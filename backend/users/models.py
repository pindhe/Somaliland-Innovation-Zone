from django.contrib.auth.models import AbstractUser
from django.db import models


class Admin(AbstractUser):
    """Admin user model with role-based access."""

    class Role(models.TextChoices):
        ADMIN = 'admin', 'Admin'
        STUDENT = 'student', 'Student'

    role = models.CharField(
        max_length=20,
        choices=Role.choices,
        default=Role.ADMIN,
    )
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'admins'
        verbose_name = 'User'
        verbose_name_plural = 'Users'

    def __str__(self):
        return self.username

    @property
    def is_admin_user(self):
        return self.role == self.Role.ADMIN
