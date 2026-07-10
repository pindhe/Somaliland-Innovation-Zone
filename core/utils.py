from .models import ActivityLog, Notification


def get_client_ip(request):
    xff = request.META.get("HTTP_X_FORWARDED_FOR")
    if xff:
        return xff.split(",")[0].strip()
    return request.META.get("REMOTE_ADDR")


def log_activity(request, action, model_name, obj=None, details=""):
    user = request.user if getattr(request, "user", None) and request.user.is_authenticated else None
    ActivityLog.objects.create(
        user=user,
        action=action,
        model_name=model_name,
        object_id=str(getattr(obj, "pk", "") or ""),
        object_repr=str(obj)[:255] if obj else "",
        details=details,
        ip_address=get_client_ip(request) if request else None,
    )


def notify_admins(title, message, link=""):
    from accounts.models import User

    admins = User.objects.filter(is_staff=True, is_active=True)
    Notification.objects.bulk_create(
        [
            Notification(user=u, title=title, message=message, link=link)
            for u in admins
        ]
    )
