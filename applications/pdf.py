from io import BytesIO
from pathlib import Path

from django.conf import settings
from reportlab.lib.pagesizes import A4
from reportlab.lib.units import mm
from reportlab.pdfgen import canvas


def generate_application_pdf(application):
    buffer = BytesIO()
    c = canvas.Canvas(buffer, pagesize=A4)
    width, height = A4
    y = height - 20 * mm

    logo_path = Path(settings.BASE_DIR) / "static" / "img" / "logo.png"
    if logo_path.exists():
        c.drawImage(
            str(logo_path),
            25 * mm,
            y - 14 * mm,
            width=16 * mm,
            height=16 * mm,
            preserveAspectRatio=True,
            mask="auto",
        )
        c.setFont("Helvetica-Bold", 16)
        c.drawString(45 * mm, y - 4 * mm, "Somaliland Innovation Zone")
        c.setFont("Helvetica", 11)
        c.drawString(45 * mm, y - 10 * mm, "Application Receipt")
        y -= 24 * mm
    else:
        c.setFont("Helvetica-Bold", 16)
        c.drawString(25 * mm, y, "Somaliland Innovation Zone")
        y -= 8 * mm
        c.setFont("Helvetica", 11)
        c.drawString(25 * mm, y, "Application Receipt")
        y -= 12 * mm

    c.setFont("Helvetica-Bold", 12)
    c.drawString(25 * mm, y, f"Application No: {application.application_number}")
    y -= 8 * mm
    c.setFont("Helvetica", 10)

    lines = [
        f"Status: {application.get_status_display()}",
        f"Course: {application.course.title}",
        f"Course Code: {application.course.course_code}",
        f"Name: {application.full_name}",
        f"Email: {application.email}",
        f"Phone: {application.phone}",
        f"Gender: {application.get_gender_display()}",
        f"Nationality: {application.nationality}",
        f"City: {application.city}, {application.country}",
        f"Education: {application.highest_education}",
        f"Institution: {application.institution}",
        f"Submitted: {application.created_at.strftime('%Y-%m-%d %H:%M')}",
    ]
    for line in lines:
        c.drawString(25 * mm, y, line)
        y -= 6 * mm
        if y < 30 * mm:
            c.showPage()
            y = height - 30 * mm
            c.setFont("Helvetica", 10)

    y -= 8 * mm
    c.setFont("Helvetica-Oblique", 9)
    c.drawString(
        25 * mm,
        y,
        "Keep this receipt for your records. Use your application number to check status.",
    )
    c.showPage()
    c.save()
    buffer.seek(0)
    return buffer
