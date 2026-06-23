from django.db import models

from courses.models import Course


class Application(models.Model):
    class Status(models.TextChoices):
        PENDING = 'pending', 'Pending'
        APPROVED = 'approved', 'Approved'
        REJECTED = 'rejected', 'Rejected'

    class Gender(models.TextChoices):
        MALE = 'male', 'Male'
        FEMALE = 'female', 'Female'
        OTHER = 'other', 'Other'
        PREFER_NOT = 'prefer_not', 'Prefer not to say'

    class EducationLevel(models.TextChoices):
        HIGH_SCHOOL = 'high_school', 'High School'
        DIPLOMA = 'diploma', 'Diploma'
        BACHELORS = 'bachelors', "Bachelor's Degree"
        MASTERS = 'masters', "Master's Degree"
        PHD = 'phd', 'PhD'
        OTHER = 'other', 'Other'

    class Schedule(models.TextChoices):
        MORNING = 'morning', 'Morning'
        AFTERNOON = 'afternoon', 'Afternoon'
        EVENING = 'evening', 'Evening'
        WEEKEND = 'weekend', 'Weekend'
        FLEXIBLE = 'flexible', 'Flexible'

    full_name = models.CharField(max_length=255)
    gender = models.CharField(max_length=20, choices=Gender.choices)
    date_of_birth = models.DateField()
    nationality = models.CharField(max_length=100)
    phone = models.CharField(max_length=20)
    email = models.EmailField()
    address = models.TextField()

    education_level = models.CharField(max_length=20, choices=EducationLevel.choices)
    institution = models.CharField(max_length=255)
    field_of_study = models.CharField(max_length=255)
    graduation_year = models.PositiveIntegerField()

    selected_course = models.ForeignKey(
        Course,
        on_delete=models.SET_NULL,
        null=True,
        related_name='applications',
    )
    preferred_schedule = models.CharField(
        max_length=20,
        choices=Schedule.choices,
        default=Schedule.FLEXIBLE,
    )

    motivation = models.TextField()
    career_goals = models.TextField()
    comments = models.TextField(blank=True)

    application_status = models.CharField(
        max_length=20,
        choices=Status.choices,
        default=Status.PENDING,
    )
    admin_notes = models.TextField(blank=True)
    submitted_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'applications'
        ordering = ['-submitted_at']

    def __str__(self):
        return f"{self.full_name} - {self.selected_course}"
