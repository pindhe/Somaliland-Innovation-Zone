from django.conf import settings
from django.db import models
from django.utils.text import slugify


class Department(models.Model):
    name = models.CharField(max_length=120, unique=True)
    slug = models.SlugField(max_length=140, unique=True, blank=True)
    description = models.TextField(blank=True)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ["name"]

    def save(self, *args, **kwargs):
        if not self.slug:
            self.slug = slugify(self.name)
        super().save(*args, **kwargs)

    def __str__(self):
        return self.name


class Category(models.Model):
    name = models.CharField(max_length=120, unique=True)
    slug = models.SlugField(max_length=140, unique=True, blank=True)

    class Meta:
        verbose_name_plural = "categories"
        ordering = ["name"]

    def save(self, *args, **kwargs):
        if not self.slug:
            self.slug = slugify(self.name)
        super().save(*args, **kwargs)

    def __str__(self):
        return self.name


class Course(models.Model):
    class TrainingMode(models.TextChoices):
        ONLINE = "online", "Online"
        PHYSICAL = "physical", "Physical"
        HYBRID = "hybrid", "Hybrid"

    class PricingType(models.TextChoices):
        FREE = "free", "Free"
        PAID = "paid", "Paid"

    class Status(models.TextChoices):
        DRAFT = "draft", "Draft"
        PUBLISHED = "published", "Published"
        CLOSED = "closed", "Closed"
        ARCHIVED = "archived", "Archived"

    title = models.CharField(max_length=200)
    subtitle = models.CharField(max_length=255, blank=True)
    slug = models.SlugField(max_length=220, unique=True, blank=True)
    course_code = models.CharField(max_length=40, unique=True)
    department = models.ForeignKey(
        Department, on_delete=models.SET_NULL, null=True, related_name="courses"
    )
    category = models.ForeignKey(
        Category, on_delete=models.SET_NULL, null=True, blank=True, related_name="courses"
    )
    instructor = models.CharField(max_length=150)
    description = models.TextField()
    learning_outcomes = models.TextField(blank=True)
    skills = models.TextField(blank=True, help_text="Comma-separated or free text")
    duration = models.CharField(max_length=100, blank=True)
    start_date = models.DateField(null=True, blank=True)
    end_date = models.DateField(null=True, blank=True)
    class_days = models.CharField(max_length=120, blank=True)
    class_time = models.CharField(max_length=100, blank=True)
    language = models.CharField(max_length=80, default="English")
    certificate_available = models.BooleanField(default=True)
    max_seats = models.PositiveIntegerField(default=30)
    registration_deadline = models.DateTimeField(null=True, blank=True)
    location = models.CharField(max_length=200, blank=True)
    training_mode = models.CharField(
        max_length=20, choices=TrainingMode.choices, default=TrainingMode.HYBRID
    )
    thumbnail = models.ImageField(upload_to="courses/thumbnails/", blank=True, null=True)
    banner = models.ImageField(upload_to="courses/banners/", blank=True, null=True)
    requirements = models.TextField(blank=True)
    who_can_apply = models.TextField(blank=True)
    benefits = models.TextField(blank=True)
    pricing_type = models.CharField(
        max_length=10, choices=PricingType.choices, default=PricingType.FREE
    )
    price = models.DecimalField(max_digits=10, decimal_places=2, null=True, blank=True)
    scholarship_available = models.BooleanField(default=False)
    status = models.CharField(
        max_length=20, choices=Status.choices, default=Status.DRAFT
    )
    created_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        related_name="courses_created",
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        ordering = ["-created_at"]

    def save(self, *args, **kwargs):
        if not self.slug:
            base = slugify(self.title) or "course"
            slug = base
            n = 1
            while Course.objects.filter(slug=slug).exclude(pk=self.pk).exists():
                slug = f"{base}-{n}"
                n += 1
            self.slug = slug
        super().save(*args, **kwargs)

    def __str__(self):
        return f"{self.course_code} – {self.title}"

    @property
    def is_open(self):
        from django.utils import timezone

        if self.status != self.Status.PUBLISHED:
            return False
        if self.registration_deadline and timezone.now() > self.registration_deadline:
            return False
        return self.seats_remaining > 0

    @property
    def accepted_count(self):
        return self.applications.filter(status="accepted").count()

    @property
    def pending_count(self):
        return self.applications.filter(status__in=["pending", "under_review"]).count()

    @property
    def seats_remaining(self):
        taken = self.applications.filter(
            status__in=["pending", "under_review", "accepted", "waitlisted"]
        ).count()
        return max(0, self.max_seats - taken)

    @property
    def public_url(self):
        return f"/course/{self.slug}/"

    @property
    def apply_url(self):
        return f"/apply/{self.slug}/"

    def duplicate(self, user=None):
        modules = list(self.modules.all())
        self.pk = None
        self.slug = ""
        self.course_code = f"{self.course_code}-COPY"
        # Ensure unique course_code
        base_code = self.course_code
        n = 1
        while Course.objects.filter(course_code=self.course_code).exists():
            self.course_code = f"{base_code}-{n}"
            n += 1
        self.status = self.Status.DRAFT
        self.title = f"{self.title} (Copy)"
        self.created_by = user
        self.save()
        for m in modules:
            CourseModule.objects.create(
                course=self,
                title=m.title,
                description=m.description,
                order=m.order,
            )
        return self


class CourseModule(models.Model):
    course = models.ForeignKey(Course, on_delete=models.CASCADE, related_name="modules")
    title = models.CharField(max_length=200)
    description = models.TextField(blank=True)
    order = models.PositiveIntegerField(default=0)

    class Meta:
        ordering = ["order", "id"]

    def __str__(self):
        return self.title
