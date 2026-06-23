import csv
import io

from django.http import HttpResponse
from rest_framework import serializers, status, viewsets
from rest_framework.decorators import action
from rest_framework.permissions import AllowAny
from rest_framework.response import Response

from courses.permissions import IsAdminUser

from .models import Application


class ApplicationSerializer(serializers.ModelSerializer):
    course_title = serializers.CharField(source='selected_course.title', read_only=True)

    class Meta:
        model = Application
        fields = (
            'id', 'full_name', 'gender', 'date_of_birth', 'nationality',
            'phone', 'email', 'address', 'education_level', 'institution',
            'field_of_study', 'graduation_year', 'selected_course',
            'course_title', 'preferred_schedule', 'motivation', 'career_goals',
            'comments', 'application_status', 'admin_notes', 'submitted_at',
        )
        read_only_fields = ('application_status', 'admin_notes', 'submitted_at')


class ApplicationCreateSerializer(serializers.ModelSerializer):
    class Meta:
        model = Application
        fields = (
            'full_name', 'gender', 'date_of_birth', 'nationality',
            'phone', 'email', 'address', 'education_level', 'institution',
            'field_of_study', 'graduation_year', 'selected_course',
            'preferred_schedule', 'motivation', 'career_goals', 'comments',
        )

    def validate_selected_course(self, course):
        from courses.models import Course
        if course.status != Course.Status.OPEN:
            raise serializers.ValidationError('This course is not open for applications.')
        if course.seats_available <= 0:
            raise serializers.ValidationError('No seats available for this course.')
        return course


class ApplicationStatusSerializer(serializers.Serializer):
    status = serializers.ChoiceField(choices=Application.Status.choices)
    admin_notes = serializers.CharField(required=False, allow_blank=True)
