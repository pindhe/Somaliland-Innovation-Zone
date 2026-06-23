from django.conf import settings
from django.core.mail import send_mail
from rest_framework import status, viewsets
from rest_framework.response import Response

from applications.models import Application
from courses.permissions import IsAdminUser

from .models import Notification
from .serializers import NotificationSerializer


class NotificationViewSet(viewsets.ModelViewSet):
    queryset = Notification.objects.select_related('course', 'sent_by').all()
    serializer_class = NotificationSerializer
    permission_classes = [IsAdminUser]

    def perform_create(self, serializer):
        notification = serializer.save(sent_by=self.request.user)
        self._send_notification(notification)

    def _send_notification(self, notification):
        recipients = self._get_recipients(notification)
        if not recipients:
            return

        send_mail(
            subject=notification.title,
            message=notification.message,
            from_email=settings.DEFAULT_FROM_EMAIL,
            recipient_list=recipients,
            fail_silently=True,
        )

    def _get_recipients(self, notification):
        if notification.recipient_type == Notification.RecipientType.SPECIFIC:
            return [notification.recipient_email] if notification.recipient_email else []

        qs = Application.objects.all()
        if notification.course:
            qs = qs.filter(selected_course=notification.course)

        if notification.recipient_type == Notification.RecipientType.APPROVED:
            qs = qs.filter(application_status=Application.Status.APPROVED)
        elif notification.recipient_type == Notification.RecipientType.PENDING:
            qs = qs.filter(application_status=Application.Status.PENDING)
        elif notification.recipient_type == Notification.RecipientType.REJECTED:
            qs = qs.filter(application_status=Application.Status.REJECTED)

        return list(qs.values_list('email', flat=True).distinct())
