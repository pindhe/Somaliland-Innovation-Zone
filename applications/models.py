from django.conf import settings
from django.db import models
from django.utils import timezone


class Application(models.Model):
    class Gender(models.TextChoices):
        MALE = "male", "Male"
        FEMALE = "female", "Female"
        OTHER = "other", "Other"
        PREFER_NOT = "prefer_not", "Prefer not to say"

    class CurrentStatus(models.TextChoices):
        STUDENT = "student", "Student"
        GRADUATE = "graduate", "Graduate"
        EMPLOYED = "employed", "Employed"
        UNEMPLOYED = "unemployed", "Unemployed"

    class Status(models.TextChoices):
        PENDING = "pending", "Pending"
        UNDER_REVIEW = "under_review", "Under Review"
        ACCEPTED = "accepted", "Accepted"
        REJECTED = "rejected", "Rejected"
        WAITLISTED = "waitlisted", "Waitlisted"

    class HearAbout(models.TextChoices):
        SOCIAL = "social", "Social Media"
        WEBSITE = "website", "Website"
        FRIEND = "friend", "Friend / Family"
        UNIVERSITY = "university", "University / School"
        EVENT = "event", "Event / Workshop"
        OTHER = "other", "Other"

    application_number = models.CharField(max_length=32, unique=True, editable=False)
    course = models.ForeignKey(
        "courses.Course", on_delete=models.CASCADE, related_name="applications"
    )

    # Personal
    first_name = models.CharField(max_length=100)
    last_name = models.CharField(max_length=100)
    gender = models.CharField(max_length=20, choices=Gender.choices)
    date_of_birth = models.DateField()
    nationality = models.CharField(max_length=100)
    national_id = models.CharField(max_length=50)
    passport_number = models.CharField(max_length=50, blank=True)
    profile_photo = models.ImageField(upload_to="applications/photos/", blank=True, null=True)
    phone = models.CharField(max_length=30)
    whatsapp = models.CharField(max_length=30, blank=True)
    email = models.EmailField()
    current_address = models.TextField()
    city = models.CharField(max_length=100)
    country = models.CharField(max_length=100, default="Somaliland")

    # Emergency
    parent_name = models.CharField(max_length=150, blank=True)
    parent_phone = models.CharField(max_length=30, blank=True)

    # Academic
    highest_education = models.CharField(max_length=150)
    institution = models.CharField(max_length=200)
    graduation_year = models.PositiveIntegerField(null=True, blank=True)
    current_status = models.CharField(max_length=20, choices=CurrentStatus.choices)
    field_of_study = models.CharField(max_length=150, blank=True)
    gpa = models.CharField(max_length=20, blank=True)
    skills = models.TextField(blank=True)
    experience = models.TextField(blank=True)
    motivation = models.TextField(verbose_name="Why do you want this course?")
    career_goals = models.TextField()
    hear_about = models.CharField(max_length=30, choices=HearAbout.choices)

    # Documents
    cv = models.FileField(upload_to="applications/cv/", blank=True, null=True)
    transcript = models.FileField(upload_to="applications/transcripts/", blank=True, null=True)
    certificate = models.FileField(upload_to="applications/certificates/", blank=True, null=True)
    national_id_doc = models.FileField(
        upload_to="applications/ids/", blank=True, null=True
    )

    declaration = models.BooleanField(default=False)
    status = models.CharField(
        max_length=20, choices=Status.choices, default=Status.PENDING
    )
    admin_notes = models.TextField(blank=True)
    admin_comments = models.TextField(blank=True)
    internal_rating = models.PositiveSmallIntegerField(null=True, blank=True)
    reviewed_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name="reviewed_applications",
    )
    reviewed_at = models.DateTimeField(null=True, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    # Draft auto-save support
    is_draft = models.BooleanField(default=False)
    draft_token = models.CharField(max_length=64, blank=True, db_index=True)

    class Meta:
        ordering = ["-created_at"]
        indexes = [
            models.Index(fields=["email"]),
            models.Index(fields=["status"]),
            models.Index(fields=["application_number"]),
        ]

    def __str__(self):
        return f"{self.application_number} – {self.full_name}"

    @property
    def full_name(self):
        return f"{self.first_name} {self.last_name}"

    def save(self, *args, **kwargs):
        if not self.application_number and not self.is_draft:
            self.application_number = self._generate_number()
        super().save(*args, **kwargs)

    @classmethod
    def _generate_number(cls):
        year = timezone.now().year
        prefix = getattr(settings, "APPLICATION_NUMBER_PREFIX", "SIZ")
        last = (
            cls.objects.filter(application_number__startswith=f"{prefix}-{year}-")
            .order_by("-application_number")
            .first()
        )
        if last:
            try:
                seq = int(last.application_number.split("-")[-1]) + 1
            except ValueError:
                seq = 1
        else:
            seq = 1
        return f"{prefix}-{year}-{seq:06d}"


class ApplicationDocument(models.Model):
    """Extra / alternate document attachments."""

    class DocType(models.TextChoices):
        CV = "cv", "CV"
        TRANSCRIPT = "transcript", "Transcript"
        CERTIFICATE = "certificate", "Certificate"
        NATIONAL_ID = "national_id", "National ID"
        OTHER = "other", "Other"

    application = models.ForeignKey(
        Application, on_delete=models.CASCADE, related_name="documents"
    )
    doc_type = models.CharField(max_length=20, choices=DocType.choices)
    file = models.FileField(upload_to="applications/docs/")
    label = models.CharField(max_length=120, blank=True)
    uploaded_at = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        return f"{self.application.application_number} – {self.doc_type}"


class ApplicationStatusHistory(models.Model):
    application = models.ForeignKey(
        Application, on_delete=models.CASCADE, related_name="status_history"
    )
    from_status = models.CharField(max_length=20, blank=True)
    to_status = models.CharField(max_length=20)
    note = models.TextField(blank=True)
    changed_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
    )
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ["-created_at"]
        verbose_name_plural = "application status histories"

    def __str__(self):
        return f"{self.application.application_number}: {self.from_status} → {self.to_status}"
