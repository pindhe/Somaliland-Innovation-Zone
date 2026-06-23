from django.urls import include, path
from rest_framework.routers import DefaultRouter

from .views import CourseViewSet

router = DefaultRouter()
router.register(r'', CourseViewSet, basename='course')

urlpatterns = [
    path('', include(router.urls)),
]
