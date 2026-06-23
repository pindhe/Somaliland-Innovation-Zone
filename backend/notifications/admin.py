from django.contrib import admin

from .models import Notification


@admin.register(Notification)
class NotificationAdmin(admin.ModelAdmin):
    list_display = ('title', 'notification_type', 'recipient_type', 'sent_at')
    list_filter = ('notification_type', 'recipient_type')
