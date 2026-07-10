from django.conf import settings
from django.core.mail import EmailMultiAlternatives
from django.template.loader import render_to_string


def send_application_email(application, template_key):
    """
    template_key: received | approved | rejected
    """
    templates = {
        "received": (
            f"Application Received – {application.application_number}",
            "emails/application_received.html",
        ),
        "approved": (
            f"Application Approved – {application.application_number}",
            "emails/application_approved.html",
        ),
        "rejected": (
            f"Application Update – {application.application_number}",
            "emails/application_rejected.html",
        ),
    }
    if template_key not in templates:
        return
    subject, template = templates[template_key]
    context = {
        "application": application,
        "course": application.course,
        "site_url": settings.SITE_URL,
    }
    html = render_to_string(template, context)
    text = (
        f"Hello {application.first_name},\n\n"
        f"Regarding your application {application.application_number} "
        f"for {application.course.title}.\n\n"
        f"Status: {application.get_status_display()}\n"
    )
    msg = EmailMultiAlternatives(
        subject=subject,
        body=text,
        from_email=settings.DEFAULT_FROM_EMAIL,
        to=[application.email],
    )
    msg.attach_alternative(html, "text/html")
    msg.send(fail_silently=True)
