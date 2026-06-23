from django.urls import include, path
from rest_framework.routers import DefaultRouter

from .views import ApplicationViewSet

router = DefaultRouter()
router.register(r'', ApplicationViewSet, basename='application')

urlpatterns = [
    path('', include(router.urls)),
]
