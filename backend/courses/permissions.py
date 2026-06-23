from rest_framework.permissions import BasePermission

from users.models import Admin


class IsAdminUser(BasePermission):
    def has_permission(self, request, view):
        return (
            request.user.is_authenticated
            and getattr(request.user, 'role', None) == Admin.Role.ADMIN
        )
