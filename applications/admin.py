from django.contrib import admin
from .models import Application, ApplicationDocument, ApplicationStatusHistory


class DocumentInline(admin.TabularInline):
    model = ApplicationDocument
    extra = 0


class StatusHistoryInline(admin.TabularInline):
    model = ApplicationStatusHistory
    extra = 0
    readonly_fields = ("from_status", "to_status", "note", "changed_by", "created_at")


@admin.register(Application)
class ApplicationAdmin(admin.ModelAdmin):
    list_display = (
        "application_number",
        "full_name",
        "email",
        "course",
        "status",
        "created_at",
    )
    list_filter = ("status", "course", "gender", "current_status")
    search_fields = (
        "application_number",
        "first_name",
        "last_name",
        "email",
        "phone",
    )
    inlines = [DocumentInline, StatusHistoryInline]
    readonly_fields = ("application_number", "created_at", "updated_at")
