from functools import wraps
from django.contrib.auth.decorators import login_required
from django.shortcuts import redirect
from django.contrib import messages


def portal_admin_required(view_func):
    @login_required
    @wraps(view_func)
    def _wrapped(request, *args, **kwargs):
        user = request.user
        if not (user.is_staff or getattr(user, "is_portal_admin", False)):
            messages.error(request, "You do not have access to the admin portal.")
            return redirect("portal:login")
        return view_func(request, *args, **kwargs)

    return _wrapped
