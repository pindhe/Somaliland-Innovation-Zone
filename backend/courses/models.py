from django.db import models


class CourseCategory(models.TextChoices):
    PROGRAMMING = 'Programming', 'Programming'
    WEB_DEVELOPMENT = 'Web Development', 'Web Development'
    MOBILE_DEVELOPMENT = 'Mobile Development', 'Mobile Development'
    GRAPHIC_DESIGN = 'Graphic Design', 'Graphic Design'
    DIGITAL_MARKETING = 'Digital Marketing', 'Digital Marketing'
    ARTIFICIAL_INTELLIGENCE = 'Artificial Intelligence', 'Artificial Intelligence'
    BUSINESS_SKILLS = 'Business Skills', 'Business Skills'
    ENTREPRENEURSHIP = 'Entrepreneurship', 'Entrepreneurship'


class Course(models.Model):
    class TrainingType(models.TextChoices):
        FREE = 'free', 'Free'
        PAID = 'paid', 'Paid'

    class Status(models.TextChoices):
        OPEN = 'open', 'Open'
        CLOSED = 'closed', 'Closed'
        ARCHIVED = 'archived', 'Archived'
        DRAFT = 'draft', 'Draft'

    title = models.CharField(max_length=255)
    category = models.CharField(max_length=100, choices=CourseCategory.choices)
    description = models.TextField()
    duration = models.CharField(max_length=100, help_text='e.g. 8 weeks, 3 months')
    training_type = models.CharField(
        max_length=10,
        choices=TrainingType.choices,
        default=TrainingType.FREE,
    )
    instructor = models.CharField(max_length=255)
    seats = models.PositiveIntegerField(default=30)
    image = models.ImageField(upload_to='courses/', blank=True, null=True)
    requirements = models.TextField(blank=True)
    outcomes = models.TextField(blank=True, help_text='Learning outcomes')
    status = models.CharField(
        max_length=20,
        choices=Status.choices,
        default=Status.DRAFT,
    )
    start_date = models.DateField()
    end_date = models.DateField()
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'courses'
        ordering = ['-created_at']

    def __str__(self):
        return self.title

    @property
    def seats_available(self):
        from applications.models import Application
        approved = Application.objects.filter(
            selected_course=self,
            application_status=Application.Status.APPROVED,
        ).count()
        return max(0, self.seats - approved)

    @property
    def is_published(self):
        return self.status == self.Status.OPEN
