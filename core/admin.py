from django.contrib import admin
from .models import SiteSettings, Notification, Announcement, ActivityLog


@admin.register(SiteSettings)
class SiteSettingsAdmin(admin.ModelAdmin):
    def has_add_permission(self, request):
        return not SiteSettings.objects.exists()


@admin.register(Notification)
class NotificationAdmin(admin.ModelAdmin):
    list_display = ("title", "user", "is_read", "created_at")
    list_filter = ("is_read",)


@admin.register(Announcement)
class AnnouncementAdmin(admin.ModelAdmin):
    list_display = ("title", "is_active", "created_at")


@admin.register(ActivityLog)
class ActivityLogAdmin(admin.ModelAdmin):
    list_display = ("action", "model_name", "object_repr", "user", "created_at")
    list_filter = ("action", "model_name")
    readonly_fields = (
        "user",
        "action",
        "model_name",
        "object_id",
        "object_repr",
        "details",
        "ip_address",
        "created_at",
    )
