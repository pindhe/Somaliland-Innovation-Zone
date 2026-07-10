# Somaliland Innovation Zone – Student Course Registration

Modern Django 5 + Tailwind CSS system for publishing courses and accepting student applications.

## Features

- **Admin Portal** (`/admin/`) – dashboard, courses, applications, students, reports, settings, activity log
- **Student Portal** – public course pages, multi-step apply form, status lookup, PDF receipt
- Course CRUD with modules, publish/close/duplicate, public links, QR codes, social share
- Application workflow: pending → under review → accepted / rejected / waitlisted
- Emails (console in development), CSV/Excel export, dark/light mode
- SQLite for local development; PostgreSQL via `DATABASE_URL`

## Quick start

```bash
python -m venv venv
# Windows
.\venv\Scripts\Activate.ps1
# macOS/Linux
source venv/bin/activate

pip install -r requirements.txt
copy .env.example .env   # or: cp .env.example .env
python manage.py migrate
python manage.py seed_demo
python manage.py runserver
```

Open:

| URL | Purpose |
|-----|---------|
| http://127.0.0.1:8000/ | Public home |
| http://127.0.0.1:8000/admin/login/ | Admin portal |
| http://127.0.0.1:8000/django-admin/ | Django admin (backup) |

**Demo login:** `admin` / `admin123`

Demo course: [Python for Beginners](http://127.0.0.1:8000/course/python-for-beginners/)

## Project structure

```
config/           # Django settings & root URLs
accounts/         # Custom User model
courses/          # Departments, categories, courses, modules
applications/     # Applications, documents, status history, PDF/export/email
portal/           # Admin portal views
public_site/      # Public student-facing views
core/             # Site settings, notifications, activity log
templates/        # HTML templates
static/           # CSS & JS
media/            # Uploaded files
```

## PostgreSQL (production)

Set in `.env`:

```
DATABASE_URL=postgres://USER:PASSWORD@HOST:5432/siz_db
DEBUG=False
SECRET_KEY=your-long-random-secret
ALLOWED_HOSTS=yourdomain.com
EMAIL_BACKEND=django.core.mail.backends.smtp.EmailBackend
```

## Security notes

- CSRF enabled on all forms
- Staff-only portal via `@portal_admin_required`
- File type/size validation on uploads
- Rate limiting on apply & status endpoints
- Change the default admin password before deploying

## Application numbers

Format: `SIZ-YYYY-000001` (configurable prefix via `APPLICATION_NUMBER_PREFIX`).
