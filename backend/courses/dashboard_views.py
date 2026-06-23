from datetime import timedelta

from django.db.models import Count
from django.db.models.functions import TruncMonth
from django.utils import timezone
from rest_framework.permissions import IsAuthenticated
from rest_framework.response import Response
from rest_framework.views import APIView

from applications.models import Application
from courses.models import Course
from courses.permissions import IsAdminUser
from courses.serializers import CourseListSerializer


class DashboardStatsView(APIView):
    permission_classes = [IsAuthenticated, IsAdminUser]

    def get(self, request):
        courses = Course.objects.all()
        applications = Application.objects.all()

        total_courses = courses.count()
        active_courses = courses.filter(status=Course.Status.OPEN).count()
        free_trainings = courses.filter(training_type=Course.TrainingType.FREE).count()
        paid_courses = courses.filter(training_type=Course.TrainingType.PAID).count()

        total_applications = applications.count()
        approved = applications.filter(application_status=Application.Status.APPROVED).count()
        rejected = applications.filter(application_status=Application.Status.REJECTED).count()
        pending = applications.filter(application_status=Application.Status.PENDING).count()

        recent_applications = applications.select_related('selected_course')[:5]
        latest_courses = courses[:5]

        six_months_ago = timezone.now() - timedelta(days=180)
        monthly_growth = (
            applications.filter(submitted_at__gte=six_months_ago)
            .annotate(month=TruncMonth('submitted_at'))
            .values('month')
            .annotate(count=Count('id'))
            .order_by('month')
        )

        category_stats = (
            courses.values('category')
            .annotate(count=Count('id'))
            .order_by('-count')
        )

        status_breakdown = {
            'pending': pending,
            'approved': approved,
            'rejected': rejected,
        }

        course_registration = (
            applications.values('selected_course__title')
            .annotate(count=Count('id'))
            .order_by('-count')[:10]
        )

        return Response({
            'stats': {
                'total_courses': total_courses,
                'active_courses': active_courses,
                'free_trainings': free_trainings,
                'paid_courses': paid_courses,
                'total_applications': total_applications,
                'approved_applications': approved,
                'rejected_applications': rejected,
                'pending_applications': pending,
            },
            'recent_applications': [
                {
                    'id': app.id,
                    'full_name': app.full_name,
                    'course': app.selected_course.title if app.selected_course else 'N/A',
                    'email': app.email,
                    'status': app.application_status,
                    'submitted_at': app.submitted_at,
                }
                for app in recent_applications
            ],
            'latest_courses': CourseListSerializer(
                latest_courses, many=True, context={'request': request}
            ).data,
            'category_stats': list(category_stats),
            'application_trends': list(monthly_growth),
            'status_breakdown': status_breakdown,
            'course_registration_analytics': [
                {
                    'course': item['selected_course__title'] or 'Unknown',
                    'count': item['count'],
                }
                for item in course_registration
            ],
        })
