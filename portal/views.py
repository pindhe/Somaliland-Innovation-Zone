import json
from datetime import timedelta

from django.contrib import messages
from django.contrib.auth import login, logout
from django.contrib.auth.views import LoginView
from django.db.models import Count, Q
from django.http import HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse, reverse_lazy
from django.utils import timezone
from django.views.decorators.http import require_POST

from applications.emails import send_application_email
from applications.exports import export_applications_csv, export_applications_excel
from applications.forms import ApplicationReviewForm
from applications.models import Application, ApplicationStatusHistory
from applications.pdf import generate_application_pdf
from core.models import ActivityLog, Notification, SiteSettings
from core.utils import log_activity, notify_admins
from courses.forms import CourseForm, CourseModuleFormSet, DepartmentForm
from courses.models import Course, Department

from .decorators import portal_admin_required
from .forms import PortalLoginForm, SiteSettingsForm


class PortalLoginView(LoginView):
    template_name = "portal/login.html"
    authentication_form = PortalLoginForm
    redirect_authenticated_user = True

    def get_success_url(self):
        return reverse_lazy("portal:dashboard")


def portal_logout(request):
    logout(request)
    messages.success(request, "Signed out successfully.")
    return redirect("portal:login")


@portal_admin_required
def dashboard(request):
    courses = Course.objects.all()
    apps = Application.objects.filter(is_draft=False)
    now = timezone.now()
    week_ago = now - timedelta(days=7)

    stats = {
        "total_courses": courses.count(),
        "published_courses": courses.filter(status=Course.Status.PUBLISHED).count(),
        "draft_courses": courses.filter(status=Course.Status.DRAFT).count(),
        "closed_courses": courses.filter(status=Course.Status.CLOSED).count(),
        "total_applications": apps.count(),
        "pending": apps.filter(status=Application.Status.PENDING).count(),
        "under_review": apps.filter(status=Application.Status.UNDER_REVIEW).count(),
        "accepted": apps.filter(status=Application.Status.ACCEPTED).count(),
        "rejected": apps.filter(status=Application.Status.REJECTED).count(),
        "waitlisted": apps.filter(status=Application.Status.WAITLISTED).count(),
    }

    recent = apps.select_related("course")[:8]
    by_status = list(
        apps.values("status").annotate(count=Count("id")).order_by("status")
    )
    by_day = []
    for i in range(6, -1, -1):
        day = (now - timedelta(days=i)).date()
        count = apps.filter(created_at__date=day).count()
        by_day.append({"date": day.strftime("%a"), "count": count})

    course_apps = list(
        apps.values("course__title")
        .annotate(count=Count("id"))
        .order_by("-count")[:6]
    )

    notifications = Notification.objects.filter(
        Q(user=request.user) | Q(user__isnull=True), is_read=False
    )[:5]

    return render(
        request,
        "portal/dashboard.html",
        {
            "stats": stats,
            "recent": recent,
            "by_status": json.dumps(by_status),
            "by_day": json.dumps(by_day),
            "course_apps": json.dumps(course_apps),
            "notifications": notifications,
            "week_apps": apps.filter(created_at__gte=week_ago).count(),
        },
    )


# ── Courses ──────────────────────────────────────────────


@portal_admin_required
def course_list(request):
    qs = Course.objects.select_related("department", "category")
    q = request.GET.get("q", "").strip()
    status = request.GET.get("status", "")
    dept = request.GET.get("department", "")
    if q:
        qs = qs.filter(
            Q(title__icontains=q)
            | Q(course_code__icontains=q)
            | Q(instructor__icontains=q)
        )
    if status:
        qs = qs.filter(status=status)
    if dept:
        qs = qs.filter(department_id=dept)
    return render(
        request,
        "portal/courses/list.html",
        {
            "courses": qs,
            "departments": Department.objects.filter(is_active=True),
            "q": q,
            "status": status,
            "dept": dept,
            "statuses": Course.Status.choices,
        },
    )


@portal_admin_required
def course_create(request):
    if request.method == "POST":
        form = CourseForm(request.POST, request.FILES)
        formset = CourseModuleFormSet(request.POST)
        if form.is_valid() and formset.is_valid():
            course = form.save(commit=False)
            course.created_by = request.user
            course.save()
            formset.instance = course
            formset.save()
            log_activity(request, "create", "Course", course)
            messages.success(request, f"Course “{course.title}” created.")
            return redirect("portal:course_detail", pk=course.pk)
    else:
        form = CourseForm()
        formset = CourseModuleFormSet()
    return render(
        request,
        "portal/courses/form.html",
        {"form": form, "formset": formset, "title": "Add Course", "course": None},
    )


@portal_admin_required
def course_edit(request, pk):
    course = get_object_or_404(Course, pk=pk)
    if request.method == "POST":
        form = CourseForm(request.POST, request.FILES, instance=course)
        formset = CourseModuleFormSet(request.POST, instance=course)
        if form.is_valid() and formset.is_valid():
            form.save()
            formset.save()
            log_activity(request, "update", "Course", course)
            messages.success(request, "Course updated.")
            return redirect("portal:course_detail", pk=course.pk)
    else:
        form = CourseForm(instance=course)
        formset = CourseModuleFormSet(instance=course)
    return render(
        request,
        "portal/courses/form.html",
        {"form": form, "formset": formset, "title": "Edit Course", "course": course},
    )


@portal_admin_required
def course_detail(request, pk):
    course = get_object_or_404(
        Course.objects.select_related("department", "category").prefetch_related(
            "modules", "applications"
        ),
        pk=pk,
    )
    site_url = request.build_absolute_uri(course.public_url)
    apply_url = request.build_absolute_uri(course.apply_url)
    return render(
        request,
        "portal/courses/detail.html",
        {"course": course, "site_url": site_url, "apply_url": apply_url},
    )


@portal_admin_required
@require_POST
def course_action(request, pk, action):
    course = get_object_or_404(Course, pk=pk)
    actions = {
        "publish": Course.Status.PUBLISHED,
        "unpublish": Course.Status.DRAFT,
        "close": Course.Status.CLOSED,
        "reopen": Course.Status.PUBLISHED,
        "archive": Course.Status.ARCHIVED,
    }
    if action == "delete":
        log_activity(request, "delete", "Course", course)
        title = course.title
        course.delete()
        messages.success(request, f"Course “{title}” deleted.")
        return redirect("portal:course_list")
    if action == "duplicate":
        new_course = course.duplicate(user=request.user)
        log_activity(request, "duplicate", "Course", new_course, f"From {course.pk}")
        messages.success(request, f"Duplicated as “{new_course.title}”.")
        return redirect("portal:course_edit", pk=new_course.pk)
    if action in actions:
        course.status = actions[action]
        course.save(update_fields=["status", "updated_at"])
        log_activity(request, action, "Course", course)
        messages.success(request, f"Course {action}ed successfully.")
    return redirect("portal:course_detail", pk=pk)


# ── Applications ─────────────────────────────────────────


@portal_admin_required
def application_list(request):
    qs = Application.objects.filter(is_draft=False).select_related("course")
    q = request.GET.get("q", "").strip()
    status = request.GET.get("status", "")
    course_id = request.GET.get("course", "")
    dept = request.GET.get("department", "")
    date_from = request.GET.get("from", "")
    date_to = request.GET.get("to", "")

    if q:
        qs = qs.filter(
            Q(application_number__icontains=q)
            | Q(first_name__icontains=q)
            | Q(last_name__icontains=q)
            | Q(email__icontains=q)
            | Q(phone__icontains=q)
            | Q(course__title__icontains=q)
        )
    if status:
        qs = qs.filter(status=status)
    if course_id:
        qs = qs.filter(course_id=course_id)
    if dept:
        qs = qs.filter(course__department_id=dept)
    if date_from:
        qs = qs.filter(created_at__date__gte=date_from)
    if date_to:
        qs = qs.filter(created_at__date__lte=date_to)

    export = request.GET.get("export")
    if export == "csv":
        return export_applications_csv(qs)
    if export == "excel":
        return export_applications_excel(qs)

    return render(
        request,
        "portal/applications/list.html",
        {
            "applications": qs[:200],
            "q": q,
            "status": status,
            "course_id": course_id,
            "dept": dept,
            "date_from": date_from,
            "date_to": date_to,
            "statuses": Application.Status.choices,
            "courses": Course.objects.all(),
            "departments": Department.objects.filter(is_active=True),
        },
    )


@portal_admin_required
def application_detail(request, pk):
    application = get_object_or_404(
        Application.objects.select_related("course", "reviewed_by").prefetch_related(
            "status_history", "documents"
        ),
        pk=pk,
    )
    if request.method == "POST":
        form = ApplicationReviewForm(request.POST, instance=application)
        if form.is_valid():
            old_status = Application.objects.get(pk=pk).status
            app = form.save(commit=False)
            app.reviewed_by = request.user
            app.reviewed_at = timezone.now()
            app.save()
            if old_status != app.status:
                ApplicationStatusHistory.objects.create(
                    application=app,
                    from_status=old_status,
                    to_status=app.status,
                    note=app.admin_comments or app.admin_notes,
                    changed_by=request.user,
                )
                if app.status == Application.Status.ACCEPTED:
                    send_application_email(app, "approved")
                elif app.status == Application.Status.REJECTED:
                    send_application_email(app, "rejected")
            log_activity(request, "review", "Application", app)
            messages.success(request, "Application updated.")
            return redirect("portal:application_detail", pk=pk)
    else:
        form = ApplicationReviewForm(instance=application)
    return render(
        request,
        "portal/applications/detail.html",
        {"application": application, "form": form},
    )


@portal_admin_required
@require_POST
def application_quick_status(request, pk, status):
    application = get_object_or_404(Application, pk=pk)
    if status not in dict(Application.Status.choices):
        messages.error(request, "Invalid status.")
        return redirect("portal:application_list")
    old = application.status
    application.status = status
    application.reviewed_by = request.user
    application.reviewed_at = timezone.now()
    application.save()
    ApplicationStatusHistory.objects.create(
        application=application,
        from_status=old,
        to_status=status,
        changed_by=request.user,
    )
    if status == Application.Status.ACCEPTED:
        send_application_email(application, "approved")
    elif status == Application.Status.REJECTED:
        send_application_email(application, "rejected")
    log_activity(request, status, "Application", application)
    messages.success(request, f"Marked as {application.get_status_display()}.")
    return redirect("portal:application_detail", pk=pk)


@portal_admin_required
@require_POST
def application_delete(request, pk):
    application = get_object_or_404(Application, pk=pk)
    log_activity(request, "delete", "Application", application)
    application.delete()
    messages.success(request, "Application deleted.")
    return redirect("portal:application_list")


@portal_admin_required
def application_print(request, pk):
    application = get_object_or_404(Application.objects.select_related("course"), pk=pk)
    return render(
        request, "portal/applications/print.html", {"application": application}
    )


@portal_admin_required
def application_pdf(request, pk):
    application = get_object_or_404(Application, pk=pk)
    buffer = generate_application_pdf(application)
    response = HttpResponse(buffer, content_type="application/pdf")
    response["Content-Disposition"] = (
        f'attachment; filename="{application.application_number}.pdf"'
    )
    return response


# ── Students / Reports / Settings ─────────────────────────


@portal_admin_required
def students(request):
    accepted = (
        Application.objects.filter(status=Application.Status.ACCEPTED, is_draft=False)
        .select_related("course")
        .order_by("-reviewed_at")
    )
    q = request.GET.get("q", "").strip()
    if q:
        accepted = accepted.filter(
            Q(first_name__icontains=q)
            | Q(last_name__icontains=q)
            | Q(email__icontains=q)
            | Q(application_number__icontains=q)
        )
    return render(request, "portal/students.html", {"students": accepted, "q": q})


@portal_admin_required
def reports(request):
    apps = Application.objects.filter(is_draft=False)
    by_course = (
        apps.values("course__title", "course__course_code")
        .annotate(
            total=Count("id"),
            accepted=Count("id", filter=Q(status="accepted")),
            pending=Count("id", filter=Q(status="pending")),
            rejected=Count("id", filter=Q(status="rejected")),
        )
        .order_by("-total")
    )
    by_status = apps.values("status").annotate(count=Count("id"))
    return render(
        request,
        "portal/reports.html",
        {"by_course": by_course, "by_status": by_status},
    )


@portal_admin_required
def settings_view(request):
    obj = SiteSettings.load()
    if request.method == "POST":
        form = SiteSettingsForm(request.POST, request.FILES, instance=obj)
        if form.is_valid():
            form.save()
            log_activity(request, "update", "SiteSettings", obj)
            messages.success(request, "Settings saved.")
            return redirect("portal:settings")
    else:
        form = SiteSettingsForm(instance=obj)
    return render(request, "portal/settings.html", {"form": form})


@portal_admin_required
def activity_logs(request):
    logs = ActivityLog.objects.select_related("user")[:100]
    return render(request, "portal/activity.html", {"logs": logs})


@portal_admin_required
@require_POST
def mark_notifications_read(request):
    Notification.objects.filter(user=request.user, is_read=False).update(is_read=True)
    return JsonResponse({"ok": True})


@portal_admin_required
def department_list(request):
    if request.method == "POST":
        form = DepartmentForm(request.POST)
        if form.is_valid():
            form.save()
            messages.success(request, "Department added.")
            return redirect("portal:departments")
    else:
        form = DepartmentForm()
    return render(
        request,
        "portal/departments.html",
        {"departments": Department.objects.all(), "form": form},
    )
