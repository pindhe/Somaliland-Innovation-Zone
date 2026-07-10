from django.core.management.base import BaseCommand
from django.utils import timezone
from datetime import timedelta

from accounts.models import User
from applications.models import Application
from core.models import SiteSettings
from courses.models import Category, Course, CourseModule, Department


class Command(BaseCommand):
    help = "Seed demo departments, courses, and admin user"

    def handle(self, *args, **options):
        SiteSettings.load()

        admin, created = User.objects.get_or_create(
            username="admin",
            defaults={
                "email": "admin@siz.so",
                "is_staff": True,
                "is_superuser": True,
                "role": User.Role.ADMIN,
                "first_name": "SIZ",
                "last_name": "Admin",
            },
        )
        if created:
            admin.set_password("admin123")
            admin.save()
            self.stdout.write(self.style.SUCCESS("Created admin / admin123"))
        else:
            self.stdout.write("Admin user already exists")

        dept, _ = Department.objects.get_or_create(
            name="Technology",
            defaults={"description": "Software, data, and digital skills"},
        )
        Department.objects.get_or_create(name="Business", defaults={"description": "Entrepreneurship"})
        cat, _ = Category.objects.get_or_create(name="Programming")

        course, created = Course.objects.get_or_create(
            course_code="PY-101",
            defaults={
                "title": "Python for Beginners",
                "subtitle": "Build a strong foundation in Python programming",
                "department": dept,
                "category": cat,
                "instructor": "Amina Hassan",
                "description": (
                    "A hands-on introduction to Python for absolute beginners.\n\n"
                    "You will write real programs, work with data, and complete a mini project."
                ),
                "learning_outcomes": "Write Python scripts\nUse variables and functions\nBuild a small project",
                "skills": "Python, Problem solving, Git basics",
                "duration": "6 weeks",
                "start_date": timezone.now().date() + timedelta(days=21),
                "end_date": timezone.now().date() + timedelta(days=63),
                "class_days": "Mon, Wed, Fri",
                "class_time": "16:00 – 18:00",
                "language": "English",
                "certificate_available": True,
                "max_seats": 40,
                "registration_deadline": timezone.now() + timedelta(days=14),
                "location": "Hargeisa Innovation Hub / Online",
                "training_mode": Course.TrainingMode.HYBRID,
                "requirements": "Laptop with internet\nBasic computer literacy",
                "who_can_apply": "Students, graduates, and career switchers",
                "benefits": "Certificate\nMentorship\nProject portfolio piece",
                "pricing_type": Course.PricingType.FREE,
                "scholarship_available": True,
                "status": Course.Status.PUBLISHED,
                "created_by": admin,
            },
        )
        if created:
            CourseModule.objects.create(
                course=course, title="Python Basics", description="Syntax, variables, types", order=1
            )
            CourseModule.objects.create(
                course=course, title="Control Flow", description="Conditions and loops", order=2
            )
            CourseModule.objects.create(
                course=course, title="Functions & Projects", description="Build your first app", order=3
            )
            self.stdout.write(self.style.SUCCESS(f"Created course: {course.title}"))
        else:
            self.stdout.write(f"Course exists: {course.title}")

        if not Application.objects.filter(email="demo.student@example.com").exists():
            Application.objects.create(
                course=course,
                first_name="Hodan",
                last_name="Ali",
                gender=Application.Gender.FEMALE,
                date_of_birth="2002-05-12",
                nationality="Somaliland",
                national_id="SL-DEMO-001",
                phone="+252634000001",
                email="demo.student@example.com",
                current_address="Sha'ab Area",
                city="Hargeisa",
                country="Somaliland",
                highest_education="Bachelor's",
                institution="University of Hargeisa",
                graduation_year=2024,
                current_status=Application.CurrentStatus.GRADUATE,
                field_of_study="Computer Science",
                motivation="I want to strengthen my programming skills for local tech opportunities.",
                career_goals="Become a software developer contributing to Somaliland's digital economy.",
                hear_about=Application.HearAbout.WEBSITE,
                declaration=True,
                status=Application.Status.PENDING,
            )
            self.stdout.write(self.style.SUCCESS("Created demo application"))

        self.stdout.write(self.style.SUCCESS("Seed complete."))
