from django.urls import path
from . import views

app_name = "public"

urlpatterns = [
    path("", views.home, name="home"),
    path("courses/", views.course_list, name="course_list"),
    path("course/<slug:slug>/", views.course_detail, name="course_detail"),
    path("apply/<slug:slug>/", views.apply, name="apply"),
    path("apply/<slug:slug>/autosave/", views.autosave_draft, name="autosave"),
    path("apply/success/<str:number>/", views.apply_success, name="apply_success"),
    path(
        "application/<str:number>/receipt/",
        views.application_receipt_pdf,
        name="receipt_pdf",
    ),
    path("application-status/", views.application_status, name="application_status"),
    path("course/<slug:slug>/qr.png", views.course_qr, name="course_qr"),
    path("api/seats/<slug:slug>/", views.seats_api, name="seats_api"),
]
