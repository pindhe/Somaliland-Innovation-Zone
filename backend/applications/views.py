from django.conf import settings
from django.core.mail import send_mail
from django_filters import rest_framework as filters
from rest_framework import status, viewsets
from rest_framework.decorators import action
from rest_framework.permissions import AllowAny
from rest_framework.response import Response

from courses.permissions import IsAdminUser

from .models import Application
from .serializers import (
    ApplicationCreateSerializer,
    ApplicationSerializer,
    ApplicationStatusSerializer,
)


class ApplicationFilter(filters.FilterSet):
    application_status = filters.ChoiceFilter(choices=Application.Status.choices)
    education_level = filters.ChoiceFilter(choices=Application.EducationLevel.choices)
    selected_course = filters.NumberFilter(field_name='selected_course__id')
    submitted_after = filters.DateFilter(field_name='submitted_at', lookup_expr='gte')
    submitted_before = filters.DateFilter(field_name='submitted_at', lookup_expr='lte')

    class Meta:
        model = Application
        fields = ['application_status', 'education_level', 'selected_course']


class ApplicationViewSet(viewsets.ModelViewSet):
    queryset = Application.objects.select_related('selected_course').all()
    filterset_class = ApplicationFilter
    search_fields = ['full_name', 'email', 'phone', 'institution']
    ordering_fields = ['submitted_at', 'full_name', 'application_status']

    def get_permissions(self):
        if self.action == 'create':
            return [AllowAny()]
        return [IsAdminUser()]

    def get_serializer_class(self):
        if self.action == 'create':
            return ApplicationCreateSerializer
        return ApplicationSerializer

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        application = serializer.save()

        send_mail(
            subject='SIZSR Application Received',
            message=(
                f'Dear {application.full_name},\n\n'
                'Thank you for your application. Your registration has been '
                'successfully submitted and is currently under review.\n\n'
                'You will be contacted soon regarding the outcome.\n\n'
                'Somaliland Innovation Zone'
            ),
            from_email=settings.DEFAULT_FROM_EMAIL,
            recipient_list=[application.email],
            fail_silently=True,
        )

        return Response(
            ApplicationSerializer(application).data,
            status=status.HTTP_201_CREATED,
        )

    @action(detail=True, methods=['post'])
    def approve(self, request, pk=None):
        application = self.get_object()
        serializer = ApplicationStatusSerializer(data=request.data)
        serializer.is_valid(raise_exception=True)

        application.application_status = Application.Status.APPROVED
        application.admin_notes = serializer.validated_data.get('admin_notes', '')
        application.save()

        self._send_status_email(application, approved=True)
        return Response(ApplicationSerializer(application).data)

    @action(detail=True, methods=['post'])
    def reject(self, request, pk=None):
        application = self.get_object()
        serializer = ApplicationStatusSerializer(data=request.data)
        serializer.is_valid(raise_exception=True)

        application.application_status = Application.Status.REJECTED
        application.admin_notes = serializer.validated_data.get('admin_notes', '')
        application.save()

        self._send_status_email(application, approved=False)
        return Response(ApplicationSerializer(application).data)

    @action(detail=False, methods=['get'])
    def export(self, request):
        applications = self.filter_queryset(self.get_queryset())

        output = io.StringIO()
        writer = csv.writer(output)
        writer.writerow([
            'ID', 'Full Name', 'Email', 'Phone', 'Course', 'Education Level',
            'Status', 'Submitted At',
        ])
        for app in applications:
            writer.writerow([
                app.id,
                app.full_name,
                app.email,
                app.phone,
                app.selected_course.title if app.selected_course else '',
                app.get_education_level_display(),
                app.get_application_status_display(),
                app.submitted_at.strftime('%Y-%m-%d %H:%M'),
            ])

        response = HttpResponse(output.getvalue(), content_type='text/csv')
        response['Content-Disposition'] = 'attachment; filename="applications.csv"'
        return response

    def _send_status_email(self, application, approved):
        course_title = application.selected_course.title if application.selected_course else 'the course'
        if approved:
            subject = 'SIZSR Application Approved'
            message = (
                f'Dear {application.full_name},\n\n'
                f'Congratulations! Your application for {course_title} has been approved.\n\n'
            )
            if application.admin_notes:
                message += f'Notes: {application.admin_notes}\n\n'
            message += 'We look forward to seeing you in class.\n\nSomaliland Innovation Zone'
        else:
            subject = 'SIZSR Application Update'
            message = (
                f'Dear {application.full_name},\n\n'
                f'We regret to inform you that your application for {course_title} '
                'was not successful at this time.\n\n'
            )
            if application.admin_notes:
                message += f'Notes: {application.admin_notes}\n\n'
            message += 'Thank you for your interest.\n\nSomaliland Innovation Zone'

        send_mail(
            subject=subject,
            message=message,
            from_email=settings.DEFAULT_FROM_EMAIL,
            recipient_list=[application.email],
            fail_silently=True,
        )
