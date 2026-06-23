# SIZSR – Somaliland Innovation Zone Student Registration System

A modern, production-ready student registration and training management platform built with **Flutter**, **Django REST Framework**, and **PostgreSQL**.

## Features

### Student Portal (Flutter)
- Modern responsive home page with hero, featured programs, and features grid
- Course browsing with search and filters
- Detailed course pages with learning outcomes and requirements
- Multi-step application form (5 steps)
- Success confirmation page

### Admin Panel (Flutter Web/Desktop)
- Secure JWT authentication with forgot password
- Analytics dashboard with statistics cards
- Full course management (CRUD, publish, archive)
- Application review (approve/reject, notes)
- Email notification system

### Technical
- Flutter 3 (Android, iOS, Web, Windows)
- Django REST API with JWT auth
- PostgreSQL database (SQLite supported for local dev)
- Dark mode support
- Responsive design (mobile, tablet, desktop)

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | Flutter 3, Dart, Provider, GoRouter, Google Fonts |
| Backend | Django 5, Django REST Framework, SimpleJWT |
| Database | PostgreSQL 16 |
| Deployment | Docker (backend), Gunicorn |

## Quick Start

### Prerequisites
- Flutter 3.16+
- Python 3.12+
- PostgreSQL 16+ (or use SQLite for local dev)

### 1. Backend Setup

```bash
cd backend
python -m venv venv

# Windows
venv\Scripts\activate

pip install -r requirements.txt
copy .env.example .env
# For quick local dev without PostgreSQL, set DB_ENGINE=sqlite in .env

python manage.py migrate
python manage.py seed_data
python manage.py runserver 8001
```

Default admin credentials: **admin** / **admin123**

### 2. Flutter App Setup

```bash
cd frontend
flutter pub get

# Web (recommended for admin panel)
flutter run -d chrome --dart-define=API_URL=http://localhost:8001/api

# Android emulator (use 10.0.2.2 instead of localhost)
flutter run --dart-define=API_URL=http://10.0.2.2:8001/api

# Windows desktop
flutter run -d windows --dart-define=API_URL=http://localhost:8001/api
```

### URLs
- **Student Home:** App opens at `/`
- **Admin Login:** Navigate to `/admin/login` or use admin route
- **API:** `http://localhost:8001/api`

## Project Structure

```
SIZSR/
├── backend/          # Django REST API
├── frontend/         # Flutter app (lib/screens/student & admin)
└── docker-compose.yml
```

## Color Palette

- **Primary Blue**: `#2563eb`
- **Accent Green**: `#22c55e`
- **White / Light Gray** backgrounds with card-based UI

## License

MIT License – Built for Somaliland Innovation Zone.
