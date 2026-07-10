from django.urls import path
from . import views

app_name = "portal"

urlpatterns = [
    path("login/", views.PortalLoginView.as_view(), name="login"),
    path("logout/", views.portal_logout, name="logout"),
    path("dashboard/", views.dashboard, name="dashboard"),
    # Courses
    path("courses/", views.course_list, name="course_list"),
    path("courses/add/", views.course_create, name="course_create"),
    path("courses/<int:pk>/", views.course_detail, name="course_detail"),
    path("courses/<int:pk>/edit/", views.course_edit, name="course_edit"),
    path("courses/<int:pk>/<str:action>/", views.course_action, name="course_action"),
    # Applications
    path("applications/", views.application_list, name="application_list"),
    path("applications/<int:pk>/", views.application_detail, name="application_detail"),
    path(
        "applications/<int:pk>/status/<str:status>/",
        views.application_quick_status,
        name="application_quick_status",
    ),
    path(
        "applications/<int:pk>/delete/",
        views.application_delete,
        name="application_delete",
    ),
    path(
        "applications/<int:pk>/print/",
        views.application_print,
        name="application_print",
    ),
    path(
        "applications/<int:pk>/pdf/",
        views.application_pdf,
        name="application_pdf",
    ),
    # Other
    path("students/", views.students, name="students"),
    path("reports/", views.reports, name="reports"),
    path("settings/", views.settings_view, name="settings"),
    path("activity/", views.activity_logs, name="activity"),
    path("departments/", views.department_list, name="departments"),
    path(
        "notifications/read/",
        views.mark_notifications_read,
        name="mark_notifications_read",
    ),
]
