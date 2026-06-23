from django.contrib import admin

from .models import Application


@admin.register(Application)
class ApplicationAdmin(admin.ModelAdmin):
    list_display = ('full_name', 'email', 'selected_course', 'application_status', 'submitted_at')
    list_filter = ('application_status', 'education_level')
    search_fields = ('full_name', 'email', 'phone')
