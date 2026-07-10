# SIZSR — Somaliland Innovation Zone Student Registration System

A complete, modern, secure PHP + MySQL web application that lets students browse
innovation programs and apply online **without creating an account**, while
administrators manage everything through a powerful dashboard.

Built with **PHP 8+ (OOP + PDO)**, **MySQL/MariaDB**, **Tailwind CSS**,
**vanilla JavaScript + AJAX**, and a clean **MVC-inspired** folder structure.

---

<p align="center">
  <img src="Screenshot 2026-07-10 173458.png" width="900"/>
</p>

## ✨ Features

### Public Website
- Modern Silicon-Valley-style landing page (hero, animated stats, featured courses, upcoming trainings, success stories, testimonials carousel, partners, FAQ accordion, contact CTA)
- About page (history, mission, vision, objectives, team)
- Courses page with **live search & filters** (name, category, type) + pagination
- Course details page (banner, full description, objectives, benefits, outcomes, requirements, trainer, schedule, certificate)
- **7-step application form** (Personal → Education → Address → Professional → Motivation → Documents → Confirm) — no login required
- Secure file uploads (CV, certificates, ID, photo)
- Contact form + newsletter (AJAX)
- Dark mode, responsive, SEO-friendly URLs

### Admin Panel (`/admin`)
- Secure login with brute-force throttling, "remember me", forgot/reset password
- Analytics dashboard with Chart.js (monthly applications, gender distribution, courses by category) + recent activity feed
- **Courses**: full CRUD, publish/draft/archive, feature toggle, image upload
- **Categories**: CRUD with color coding
- **Applications**: list, search, filter, status tabs, approve/reject/waitlist, full student profile, print/PDF, admin notes
- **Messages**: inbox, read/reply, delete
- **CMS**: edit homepage, about, footer content & stats
- **Media manager**: upload/browse/delete images
- **Reports**: applications by course/status + export to **CSV / Excel**
- **Activity logs** & **JSON data backup**
- **Settings**: organization, logo/favicon, social links, SMTP, **WhatsApp Cloud API**, password change
- **WhatsApp on approval**: each course can store a WhatsApp group invite link; approving a student automatically sends them the link via the official WhatsApp Cloud API (with manual send/resend + delivery log)

### Security
- CSRF protection on every form & AJAX call
- PDO prepared statements (SQL-injection safe)
- Output escaping (XSS safe)
- `password_hash()` / `password_verify()`
- Secure session cookies (HttpOnly, SameSite)
- Validated & MIME-checked file uploads; PHP execution disabled in `assets/uploads`
- Activity logging
- Sensitive folders denied via `.htaccess`

---

## 🚀 Installation (XAMPP)

1. **Place the project** in your web root, e.g.
   `C:\xampp\htdocs\Somaliland-innovation`

2. **Start** Apache and MySQL from the XAMPP Control Panel.

3. **Create the database** — open <http://localhost/phpmyadmin>, then either:
   - Import `database/sizsr.sql`, **or**
   - Run from a terminal:
     ```bash
     C:\xampp\mysql\bin\mysql.exe -u root < database/sizsr.sql
     ```
   This creates the `sizsr_db` database with all tables and seed data.

4. **Configure** the DB connection if needed in `config/database.php`
   (defaults: host `127.0.0.1`, user `root`, no password).

5. **Visit the site:**
   - Website: <http://localhost/Somaliland-innovation/>
   - Admin:   <http://localhost/Somaliland-innovation/admin/login.php>

> The included `.htaccess` uses `RewriteBase /Somaliland-innovation/`.
> If you rename the folder, update that line (and `BASE_URL` is auto-detected).

### Default admin account
| Email | Password |
|-------|----------|
| `admin@sizsr.com` | `Admin@123` |

Change the password from **Settings** after first login.

---

## 📲 WhatsApp Cloud API Integration

When an admin approves a student, the system automatically sends a WhatsApp
message containing the **course's group invite link** using Meta's official
**WhatsApp Cloud API**. It never auto-adds users to a group — it only sends the
link as a text message.

**The message:**

```
🎉 Congratulations [Student Name]

Your application for [Course Name] has been approved.

Join your official WhatsApp class group below:
[WhatsApp Group Link]

Welcome to the course 🚀
```

### 1. Get WhatsApp Cloud API credentials
1. Go to <https://developers.facebook.com> → **My Apps** → create an app (type **Business**).
2. Add the **WhatsApp** product → **API Setup**.
3. Copy your **Phone Number ID** and a **Permanent Access Token**
   (create a System User token under **Business Settings** for production; the temporary 24-hour token is fine for testing).
4. While testing, add the recipient's number to the **allowed test recipients** list.

### 2. Add a group link to each course
Edit a course → **Schedule & Details** step → **WhatsApp Group Invite Link**
(e.g. `https://chat.whatsapp.com/xxxxxxxx`).

### 3. Configure the API credentials (two options)

**Option A — `.env` file (recommended for production):**
Copy `.env.example` to `.env` in the project root and fill in:

```env
WHATSAPP_ENABLED=true
WHATSAPP_API_VERSION=v21.0
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_TOKEN=your_permanent_access_token
WHATSAPP_DEFAULT_COUNTRY_CODE=252
```

`.env` is auto-loaded by `config/config.php` and is git-ignored. Values here
**override** anything saved in the admin panel.

**Option B — Admin → Settings → WhatsApp Cloud API:**
Toggle **Enabled**, then paste your Phone Number ID, API version, default
country code, and access token. Fields locked to `env` show an **ENV** badge.

### 4. How it sends

There are **two** ways to deliver the message:

**A) Click-to-send (wa.me) — works on any host, no API needed.**
- An **"Open WhatsApp & Send"** button (application detail page) and a WhatsApp
  icon on each approved row (applications list) open WhatsApp with the approval
  message pre-filled — you just press send from your own WhatsApp.
- Ideal for free hosts (e.g. **InfinityFree**) that block outbound API calls.

**B) Automatic Cloud API send — fully hands-off.**
- When **WhatsApp is enabled & configured**, approving a student (from the list
  or detail page) sends the message automatically on the first transition to
  *approved*. A **Auto-send via Cloud API** button is also available.
- Every attempt is recorded in `whatsapp_logs` (sent/failed + error + message id).
- Requires the PHP **cURL** extension and a host that allows outbound HTTPS to
  `graph.facebook.com` (works on XAMPP; **not** on free InfinityFree).

Phone numbers are normalised to E.164 (a leading `0` becomes the default country code).

### Database
Everything (including `courses.whatsapp_group_link` and the `whatsapp_logs`
table) lives in the single file `database/sizsr.sql`. Import it via phpMyAdmin
or:

```bash
C:\xampp\mysql\bin\mysql.exe -u root sizsr_db < database/sizsr.sql
```

> ⚠️ Re-importing `sizsr.sql` recreates the tables and **erases existing data**.
> On a live database that already has data, instead add just the two new objects
> by pasting this into the phpMyAdmin **SQL** tab:
>
> ```sql
> ALTER TABLE `courses` ADD COLUMN `whatsapp_group_link` VARCHAR(255) NULL AFTER `session_time`;
> CREATE TABLE IF NOT EXISTS `whatsapp_logs` (
>   `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `application_id` INT UNSIGNED NULL,
>   `phone` VARCHAR(40) NULL, `message` TEXT NULL,
>   `status` ENUM('sent','failed') NOT NULL DEFAULT 'sent',
>   `provider_message_id` VARCHAR(120) NULL, `error` TEXT NULL,
>   `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
>   PRIMARY KEY (`id`), KEY (`application_id`), KEY (`status`)
> ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
> ```

---

## 🗂️ Folder Structure

```
Somaliland-innovation/
├── admin/                 # Admin panel
│   ├── includes/          # bootstrap, header (sidebar), footer
│   ├── index.php          # Dashboard
│   ├── login.php / logout.php / forgot-password.php / reset-password.php
│   ├── courses.php / course-form.php
│   ├── categories.php
│   ├── applications.php / application-view.php
│   ├── contacts.php
│   ├── cms.php / media.php / settings.php
│   ├── reports.php / export.php / backup.php / logs.php
├── api/                   # JSON/AJAX endpoints
│   ├── newsletter.php
│   ├── courses.php        # GET  published courses
│   ├── students.php       # GET  applications (admin session)
│   ├── apply-student.php  # POST create application
│   └── approve-student.php# POST approve + auto WhatsApp (admin session)
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   ├── images/
│   └── uploads/           # courses, documents, media (writable)
├── config/                # config.php, database.php
├── includes/              # functions.php, header.php, footer.php
├── pages/                 # home, about, courses, course-details, apply, contact, success, 404
├── database/sizsr.sql
├── index.php              # Front controller / router
├── .htaccess
└── README.md
```

---

## 📩 API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET`  | `/api/courses.php` | public | List published courses |
| `GET`  | `/api/students.php?status=&course_id=` | admin session | List student applications |
| `POST` | `/api/apply-student.php` | public | Submit an application (`full_name, phone, email, course_id`) |
| `POST` | `/api/approve-student.php` | admin session | Approve a student (`id`) and auto-send the WhatsApp group link |

---

## 🎨 Design Tokens

| Token | Color |
|-------|-------|
| Primary | `#006C67` |
| Secondary | `#00A99D` |
| Accent | `#F4B400` |
| Background | `#F8FAFC` |
| Dark | `#0F172A` |
| Success | `#10B981` |
| Danger | `#EF4444` |
| Warning | `#F59E0B` |

---

## 🛠️ Tech Notes
- Tailwind is loaded via CDN with a custom config (see `includes/header.php`) for zero-build simplicity. For production you may compile Tailwind locally.
- Chart.js is loaded via CDN on the dashboard.
- PDF export uses the browser's print-to-PDF; CSV/Excel are generated server-side.
- Email sending hooks (SMTP settings) are stored and ready to wire into a mailer (e.g. PHPMailer) for the notification templates.

---

© Somaliland Innovation Zone — SIZSR
