from django.contrib import admin

from .models import Course


@admin.register(Course)
class CourseAdmin(admin.ModelAdmin):
    list_display = ('title', 'category', 'training_type', 'status', 'start_date', 'seats')
    list_filter = ('category', 'training_type', 'status')
    search_fields = ('title', 'instructor')
