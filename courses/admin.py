from django.contrib import admin
from .models import Department, Category, Course, CourseModule


class CourseModuleInline(admin.TabularInline):
    model = CourseModule
    extra = 1


@admin.register(Department)
class DepartmentAdmin(admin.ModelAdmin):
    list_display = ("name", "slug", "is_active")
    prepopulated_fields = {"slug": ("name",)}


@admin.register(Category)
class CategoryAdmin(admin.ModelAdmin):
    list_display = ("name", "slug")
    prepopulated_fields = {"slug": ("name",)}


@admin.register(Course)
class CourseAdmin(admin.ModelAdmin):
    list_display = (
        "course_code",
        "title",
        "department",
        "status",
        "training_mode",
        "max_seats",
    )
    list_filter = ("status", "training_mode", "department", "pricing_type")
    search_fields = ("title", "course_code", "instructor")
    prepopulated_fields = {"slug": ("title",)}
    inlines = [CourseModuleInline]
