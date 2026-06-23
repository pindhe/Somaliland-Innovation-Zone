from django.db import models


class Notification(models.Model):
    class NotificationType(models.TextChoices):
        APPROVAL = 'approval', 'Approval'
        REJECTION = 'rejection', 'Rejection'
        COURSE_UPDATE = 'course_update', 'Course Update'
        ANNOUNCEMENT = 'announcement', 'General Announcement'

    class RecipientType(models.TextChoices):
        ALL = 'all', 'All Applicants'
        APPROVED = 'approved', 'Approved Applicants'
        PENDING = 'pending', 'Pending Applicants'
        REJECTED = 'rejected', 'Rejected Applicants'
        SPECIFIC = 'specific', 'Specific Email'

    title = models.CharField(max_length=255)
    message = models.TextField()
    notification_type = models.CharField(
        max_length=20,
        choices=NotificationType.choices,
        default=NotificationType.ANNOUNCEMENT,
    )
    recipient_type = models.CharField(
        max_length=20,
        choices=RecipientType.choices,
        default=RecipientType.ALL,
    )
    recipient_email = models.EmailField(blank=True)
    course = models.ForeignKey(
        'courses.Course',
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name='notifications',
    )
    sent_at = models.DateTimeField(auto_now_add=True)
    sent_by = models.ForeignKey(
        'users.Admin',
        on_delete=models.SET_NULL,
        null=True,
        related_name='notifications_sent',
    )

    class Meta:
        db_table = 'notifications'
        ordering = ['-sent_at']

    def __str__(self):
        return self.title
