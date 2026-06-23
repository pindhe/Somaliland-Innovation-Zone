# SIZSR – Somaliland Innovation Zone Student Registration System

A modern, production-ready student registration and training management platform built with **Next.js**, **Django REST Framework**, and **PostgreSQL**.

## Features

### Student Portal
- Modern responsive home page with hero, featured programs, and categories
- Course browsing with search and filters
- Detailed course pages with learning outcomes and requirements
- Multi-step application form (5 steps)
- Success confirmation page with animations

### Admin Panel
- Secure JWT authentication with forgot password
- Analytics dashboard with charts and statistics
- Full course management (CRUD, publish, archive)
- Application review (approve/reject, notes, export CSV)
- Email notification system

### Technical
- React/Next.js 14 frontend with Tailwind CSS
- Django REST API with JWT auth
- PostgreSQL database
- Dark mode support
- Docker deployment ready
- Responsive design (mobile, tablet, desktop)

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | Next.js 14, React 18, Tailwind CSS, Framer Motion, Recharts |
| Backend | Django 5, Django REST Framework, SimpleJWT |
| Database | PostgreSQL 16 |
| Deployment | Docker, Gunicorn |

## Quick Start

### Prerequisites
- Node.js 20+
- Python 3.12+
- PostgreSQL 16+

### 1. Database Setup

```bash
# Create PostgreSQL database
createdb sizsr
```

### 2. Backend Setup

```bash
cd backend
python -m venv venv

# Windows
venv\Scripts\activate

# macOS/Linux
source venv/bin/activate

pip install -r requirements.txt
cp .env.example .env
# Edit .env with your database credentials

python manage.py migrate
python manage.py seed_data
python manage.py runserver
```

Default admin credentials: **admin** / **admin123**

### 3. Frontend Setup

```bash
cd frontend
npm install
cp .env.local.example .env.local
npm run dev
```

Open [http://localhost:3000](http://localhost:3000) for the student portal.
Open [http://localhost:3000/admin/login](http://localhost:3000/admin/login) for the admin panel.

### Docker (Alternative)

```bash
docker-compose up --build
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/login/` | Admin login |
| GET | `/api/courses/` | List courses |
| GET | `/api/courses/featured/` | Featured courses |
| POST | `/api/applications/` | Submit application |
| GET | `/api/dashboard/stats/` | Dashboard analytics |
| GET | `/api/applications/export/` | Export CSV |

## Project Structure

```
SIZSR/
├── backend/
│   ├── config/          # Django settings
│   ├── users/           # Auth & admin model
│   ├── courses/         # Course management
│   ├── applications/    # Application handling
│   └── notifications/   # Email notifications
├── frontend/
│   └── src/
│       ├── app/         # Next.js pages
│       ├── components/  # UI components
│       ├── contexts/    # Auth & theme
│       └── lib/         # API client & utils
└── docker-compose.yml
```

## Color Palette

- **Primary Blue**: `#2563eb` – buttons, links, accents
- **Accent Green**: `#22c55e` – success, free training badges
- **White / Light Gray**: backgrounds and cards
- **Glassmorphism**: frosted glass card effects

## License

MIT License – Built for Somaliland Innovation Zone.
