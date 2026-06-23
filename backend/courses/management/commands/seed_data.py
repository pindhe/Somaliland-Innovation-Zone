from django.core.management.base import BaseCommand
from django.contrib.auth import get_user_model
from django.utils import timezone
from datetime import timedelta

from courses.models import Course
from applications.models import Application

User = get_user_model()


class Command(BaseCommand):
    help = 'Seed database with sample data'

    def handle(self, *args, **options):
        admin, created = User.objects.get_or_create(
            username='admin',
            defaults={
                'email': 'admin@sizsr.local',
                'role': User.Role.ADMIN,
                'is_staff': True,
                'is_superuser': True,
            },
        )
        if created:
            admin.set_password('admin123')
            admin.save()
            self.stdout.write(self.style.SUCCESS('Created admin user (admin / admin123)'))
        else:
            self.stdout.write('Admin user already exists')

        courses_data = [
            {
                'title': 'Full Stack Web Development Bootcamp',
                'category': 'Web Development',
                'description': 'Master modern web development with React, Node.js, and PostgreSQL. Build real-world projects and deploy to production.',
                'duration': '12 weeks',
                'training_type': Course.TrainingType.FREE,
                'instructor': 'Ahmed Hassan',
                'seats': 30,
                'requirements': 'Basic computer literacy. Laptop required.',
                'outcomes': 'Build full-stack web applications\nDeploy to cloud platforms\nUnderstand REST APIs',
                'status': Course.Status.OPEN,
                'start_date': timezone.now().date() + timedelta(days=30),
                'end_date': timezone.now().date() + timedelta(days=120),
            },
            {
                'title': 'Mobile App Development with Flutter',
                'category': 'Mobile Development',
                'description': 'Learn to build beautiful cross-platform mobile applications using Flutter and Dart.',
                'duration': '10 weeks',
                'training_type': Course.TrainingType.FREE,
                'instructor': 'Fatima Ali',
                'seats': 25,
                'requirements': 'Programming fundamentals recommended.',
                'outcomes': 'Build iOS and Android apps\nState management\nAPI integration',
                'status': Course.Status.OPEN,
                'start_date': timezone.now().date() + timedelta(days=45),
                'end_date': timezone.now().date() + timedelta(days=115),
            },
            {
                'title': 'Introduction to Artificial Intelligence',
                'category': 'Artificial Intelligence',
                'description': 'Explore machine learning, neural networks, and AI applications in the real world.',
                'duration': '8 weeks',
                'training_type': Course.TrainingType.PAID,
                'instructor': 'Dr. Omar Yusuf',
                'seats': 20,
                'requirements': 'Python programming knowledge. Mathematics basics.',
                'outcomes': 'Understand ML fundamentals\nBuild ML models\nDeploy AI solutions',
                'status': Course.Status.OPEN,
                'start_date': timezone.now().date() + timedelta(days=60),
                'end_date': timezone.now().date() + timedelta(days=116),
            },
            {
                'title': 'Digital Marketing Masterclass',
                'category': 'Digital Marketing',
                'description': 'Learn SEO, social media marketing, content strategy, and analytics.',
                'duration': '6 weeks',
                'training_type': Course.TrainingType.FREE,
                'instructor': 'Sahra Mohamed',
                'seats': 40,
                'requirements': 'No prior experience required.',
                'outcomes': 'Create marketing campaigns\nAnalyze metrics\nGrow online presence',
                'status': Course.Status.OPEN,
                'start_date': timezone.now().date() + timedelta(days=20),
                'end_date': timezone.now().date() + timedelta(days=62),
            },
            {
                'title': 'Graphic Design Fundamentals',
                'category': 'Graphic Design',
                'description': 'Master design principles, typography, color theory, and industry-standard tools.',
                'duration': '8 weeks',
                'training_type': Course.TrainingType.FREE,
                'instructor': 'Layla Ibrahim',
                'seats': 25,
                'requirements': 'Creative mindset. Access to design software.',
                'outcomes': 'Create professional designs\nBrand identity\nPortfolio development',
                'status': Course.Status.OPEN,
                'start_date': timezone.now().date() + timedelta(days=35),
                'end_date': timezone.now().date() + timedelta(days=91),
            },
            {
                'title': 'Entrepreneurship & Business Skills',
                'category': 'Entrepreneurship',
                'description': 'Develop business acumen, startup strategies, and leadership skills.',
                'duration': '6 weeks',
                'training_type': Course.TrainingType.FREE,
                'instructor': 'Hassan Abdi',
                'seats': 35,
                'requirements': 'Interest in business and innovation.',
                'outcomes': 'Business plan creation\nPitch development\nMarket analysis',
                'status': Course.Status.OPEN,
                'start_date': timezone.now().date() + timedelta(days=25),
                'end_date': timezone.now().date() + timedelta(days=67),
            },
        ]

        for data in courses_data:
            course, created = Course.objects.get_or_create(
                title=data['title'],
                defaults=data,
            )
            if created:
                self.stdout.write(f'  Created course: {course.title}')

        self.stdout.write(self.style.SUCCESS('Seed data created successfully!'))
