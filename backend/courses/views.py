from django.db.models import Count
from django.db.models.functions import TruncMonth
from rest_framework import status, viewsets
from rest_framework.decorators import action
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.response import Response

from users.models import Admin

from .models import Course
from .permissions import IsAdminUser
from .serializers import (
    CourseDetailSerializer,
    CourseListSerializer,
    CourseWriteSerializer,
    get_category_choices,
)


class CourseViewSet(viewsets.ModelViewSet):
    queryset = Course.objects.all()

    def get_permissions(self):
        if self.action in ('list', 'retrieve', 'featured', 'categories'):
            return [AllowAny()]
        return [IsAuthenticated(), IsAdminUser()]

    def get_serializer_class(self):
        if self.action in ('create', 'update', 'partial_update'):
            return CourseWriteSerializer
        if self.action == 'retrieve':
            return CourseDetailSerializer
        return CourseListSerializer

    def get_queryset(self):
        qs = Course.objects.all()
        if not self.request.user.is_authenticated or \
           getattr(self.request.user, 'role', None) != Admin.Role.ADMIN:
            qs = qs.filter(status=Course.Status.OPEN)
        else:
            status_filter = self.request.query_params.get('status')
            category = self.request.query_params.get('category')
            training_type = self.request.query_params.get('training_type')
            if status_filter:
                qs = qs.filter(status=status_filter)
            if category:
                qs = qs.filter(category=category)
            if training_type:
                qs = qs.filter(training_type=training_type)
        return qs

    @action(detail=False, methods=['get'], permission_classes=[AllowAny])
    def featured(self, request):
        courses = Course.objects.filter(status=Course.Status.OPEN)[:6]
        serializer = CourseListSerializer(courses, many=True, context={'request': request})
        return Response(serializer.data)

    @action(detail=False, methods=['get'], permission_classes=[AllowAny])
    def categories(self, request):
        return Response(get_category_choices())

    @action(detail=True, methods=['post'])
    def publish(self, request, pk=None):
        course = self.get_object()
        course.status = Course.Status.OPEN
        course.save()
        return Response(CourseDetailSerializer(course, context={'request': request}).data)

    @action(detail=True, methods=['post'])
    def archive(self, request, pk=None):
        course = self.get_object()
        course.status = Course.Status.ARCHIVED
        course.save()
        return Response(CourseDetailSerializer(course, context={'request': request}).data)

    def destroy(self, request, *args, **kwargs):
        course = self.get_object()
        course.status = Course.Status.ARCHIVED
        course.save()
        return Response(status=status.HTTP_204_NO_CONTENT)
