import csv
from io import BytesIO
from openpyxl import Workbook
from django.http import HttpResponse


def export_applications_csv(queryset):
    response = HttpResponse(content_type="text/csv")
    response["Content-Disposition"] = 'attachment; filename="applications.csv"'
    writer = csv.writer(response)
    writer.writerow(
        [
            "Application Number",
            "First Name",
            "Last Name",
            "Email",
            "Phone",
            "Course",
            "Status",
            "Date",
        ]
    )
    for a in queryset:
        writer.writerow(
            [
                a.application_number,
                a.first_name,
                a.last_name,
                a.email,
                a.phone,
                a.course.title,
                a.get_status_display(),
                a.created_at.strftime("%Y-%m-%d %H:%M"),
            ]
        )
    return response


def export_applications_excel(queryset):
    wb = Workbook()
    ws = wb.active
    ws.title = "Applications"
    ws.append(
        [
            "Application Number",
            "First Name",
            "Last Name",
            "Email",
            "Phone",
            "Course",
            "Status",
            "Date",
        ]
    )
    for a in queryset:
        ws.append(
            [
                a.application_number,
                a.first_name,
                a.last_name,
                a.email,
                a.phone,
                a.course.title,
                a.get_status_display(),
                a.created_at.strftime("%Y-%m-%d %H:%M"),
            ]
        )
    buffer = BytesIO()
    wb.save(buffer)
    buffer.seek(0)
    response = HttpResponse(
        buffer.getvalue(),
        content_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    )
    response["Content-Disposition"] = 'attachment; filename="applications.xlsx"'
    return response
