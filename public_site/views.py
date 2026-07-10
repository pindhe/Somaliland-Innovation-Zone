import json
import secrets
from io import BytesIO

import qrcode
from django.conf import settings
from django.contrib import messages
from django.http import HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.views.decorators.http import require_GET, require_POST
from django_ratelimit.decorators import ratelimit

from applications.emails import send_application_email
from applications.forms import ApplicationForm, ApplicationStatusForm
from applications.models import Application, ApplicationStatusHistory
from applications.pdf import generate_application_pdf
from core.utils import notify_admins
from courses.models import Course


def home(request):
    courses = (
        Course.objects.filter(status=Course.Status.PUBLISHED)
        .select_related("department")
        .order_by("-created_at")[:12]
    )
    return render(request, "public/home.html", {"courses": courses})


def course_list(request):
    courses = Course.objects.filter(status=Course.Status.PUBLISHED).select_related(
        "department", "category"
    )
    q = request.GET.get("q", "").strip()
    if q:
        courses = courses.filter(title__icontains=q)
    return render(request, "public/course_list.html", {"courses": courses, "q": q})


def course_detail(request, slug):
    course = get_object_or_404(
        Course.objects.prefetch_related("modules").select_related("department"),
        slug=slug,
        status__in=[Course.Status.PUBLISHED, Course.Status.CLOSED],
    )
    apply_abs = request.build_absolute_uri(course.apply_url)
    return render(
        request,
        "public/course_detail.html",
        {
            "course": course,
            "apply_abs": apply_abs,
            "share_url": request.build_absolute_uri(course.public_url),
        },
    )


@ratelimit(key="ip", rate="20/h", method="POST", block=True)
def apply(request, slug):
    course = get_object_or_404(Course, slug=slug, status=Course.Status.PUBLISHED)
    if not course.is_open:
        messages.warning(request, "Registration is closed for this course.")
        return redirect("public:course_detail", slug=slug)

    draft_token = request.session.get(f"draft_{course.pk}") or request.GET.get("token")

    if request.method == "POST":
        form = ApplicationForm(request.POST, request.FILES)
        if form.is_valid():
            app = form.save(commit=False)
            app.course = course
            app.is_draft = False
            app.status = Application.Status.PENDING
            app.save()
            ApplicationStatusHistory.objects.create(
                application=app,
                from_status="",
                to_status=Application.Status.PENDING,
                note="Application submitted",
            )
            send_application_email(app, "received")
            notify_admins(
                title="New application",
                message=f"{app.full_name} applied for {course.title}",
                link=f"/admin/applications/{app.pk}/",
            )
            request.session.pop(f"draft_{course.pk}", None)
            return redirect("public:apply_success", number=app.application_number)
    else:
        form = ApplicationForm()

    return render(
        request,
        "public/apply.html",
        {
            "course": course,
            "form": form,
            "draft_token": draft_token or "",
            "seats_remaining": course.seats_remaining,
        },
    )


@require_POST
@ratelimit(key="ip", rate="60/h", method="POST", block=True)
def autosave_draft(request, slug):
    course = get_object_or_404(Course, slug=slug, status=Course.Status.PUBLISHED)
    token = request.POST.get("draft_token") or secrets.token_urlsafe(24)
    request.session[f"draft_{course.pk}"] = token
    # Store lightweight draft fields in session (not files)
    fields = [
        "first_name",
        "last_name",
        "gender",
        "email",
        "phone",
        "city",
        "country",
        "motivation",
        "career_goals",
    ]
    draft = {f: request.POST.get(f, "") for f in fields}
    request.session[f"draft_data_{course.pk}"] = draft
    request.session.modified = True
    return JsonResponse({"ok": True, "draft_token": token})


def apply_success(request, number):
    application = get_object_or_404(
        Application.objects.select_related("course"), application_number=number
    )
    return render(request, "public/success.html", {"application": application})


def application_receipt_pdf(request, number):
    application = get_object_or_404(Application, application_number=number)
    email = request.GET.get("email", "")
    if email and email.lower() != application.email.lower():
        messages.error(request, "Email does not match this application.")
        return redirect("public:application_status")
    buffer = generate_application_pdf(application)
    response = HttpResponse(buffer, content_type="application/pdf")
    response["Content-Disposition"] = f'attachment; filename="{number}.pdf"'
    return response


@ratelimit(key="ip", rate="30/h", method="POST", block=True)
def application_status(request):
    application = None
    form = ApplicationStatusForm(request.POST or None)
    if request.method == "POST" and form.is_valid():
        application = Application.objects.filter(
            application_number=form.cleaned_data["application_number"].strip().upper(),
            email__iexact=form.cleaned_data["email"].strip(),
            is_draft=False,
        ).select_related("course").first()
        if not application:
            # try without upper
            application = Application.objects.filter(
                application_number__iexact=form.cleaned_data["application_number"].strip(),
                email__iexact=form.cleaned_data["email"].strip(),
                is_draft=False,
            ).select_related("course").first()
        if not application:
            messages.error(request, "No application found with those details.")
    return render(
        request,
        "public/status.html",
        {"form": form, "application": application},
    )


@require_GET
def course_qr(request, slug):
    course = get_object_or_404(Course, slug=slug)
    url = request.build_absolute_uri(course.apply_url)
    img = qrcode.make(url)
    buffer = BytesIO()
    img.save(buffer, format="PNG")
    return HttpResponse(buffer.getvalue(), content_type="image/png")


@require_GET
def seats_api(request, slug):
    course = get_object_or_404(Course, slug=slug)
    return JsonResponse(
        {
            "seats_remaining": course.seats_remaining,
            "max_seats": course.max_seats,
            "is_open": course.is_open,
            "deadline": course.registration_deadline.isoformat()
            if course.registration_deadline
            else None,
        }
    )
