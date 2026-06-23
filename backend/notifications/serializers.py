from rest_framework import serializers

from .models import Notification


class NotificationSerializer(serializers.ModelSerializer):
    sent_by_name = serializers.CharField(source='sent_by.username', read_only=True)

    class Meta:
        model = Notification
        fields = (
            'id', 'title', 'message', 'notification_type',
            'recipient_type', 'recipient_email', 'course',
            'sent_at', 'sent_by', 'sent_by_name',
        )
        read_only_fields = ('sent_at', 'sent_by')
