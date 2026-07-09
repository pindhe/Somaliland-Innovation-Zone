# 🚀 Deployment Guide — SIZSR (Custom Domain + Real Host)

This guide moves the project off the free InfinityFree (`kesug.com`) subdomain
to a proper host with your own domain. This:

- ✅ Removes Chrome's **"Dangerous site"** warning (caused by the shared free domain)
- ✅ Makes the **WhatsApp Cloud API auto-send** work (free hosts block outbound API calls)
- ✅ Looks professional to students

The steps below use **Hostinger** as the example (cheapest reliable PHP+MySQL host
with a free domain on yearly plans), but any cPanel host (Namecheap, A2, etc.) works the same way.

---

## 1. Buy hosting + a domain

1. Get a **Shared Hosting / Premium** plan that includes **PHP 8+** and **MySQL**.
2. Register a custom domain, e.g. `sizsomaliland.com` (often free for year 1).
3. Wait for the account to be provisioned (a few minutes).

> Already own a domain elsewhere? Point its **nameservers** to the host, or add the
> domain in the host panel and update DNS. Allow up to a few hours to propagate.

---

## 2. Create the database

In the hosting control panel (cPanel / hPanel):

1. Open **MySQL Databases** (or **Databases → Management**).
2. **Create a database**, e.g. `sizsr_db`.
3. **Create a database user** with a strong password.
4. **Add the user to the database** with **ALL PRIVILEGES**.
5. Write down these 4 values — you'll need them:
   - DB name (often prefixed, e.g. `u123456_sizsr`)
   - DB user (e.g. `u123456_admin`)
   - DB password
   - DB host (usually `localhost`)

---

## 3. Import the database

1. Open **phpMyAdmin** from the panel.
2. Select your new database on the left.
3. Click **Import** → choose `database/sizsr.sql` → **Go**.
4. You should see the tables created with seed data (admin, courses, etc.).

> This file already includes the `whatsapp_group_link` column and `whatsapp_logs` table.

---

## 4. Configure the database connection

Edit **`config/database.php`** and set your host's credentials:

```php
private const DB_HOST = 'localhost';        // from step 2
private const DB_PORT = '3306';
private const DB_NAME = 'u123456_sizsr';     // your DB name
private const DB_USER = 'u123456_admin';     // your DB user
private const DB_PASS = 'your_db_password';  // your DB password
```

Also, for production, open **`config/config.php`** and turn debug OFF:

```php
define('SIZSR_DEBUG', false);
```

---

## 5. Upload the project files

**Option A — File Manager (simple):**
1. Zip the whole project locally (or use `whatsapp-update.zip` for partial updates).
2. In the panel open **File Manager** → go to `public_html`.
3. Upload the zip → **Extract** → make sure files land directly in `public_html`
   (so `public_html/index.php`, `public_html/admin/...` exist).

**Option B — FTP (FileZilla):**
1. Create an FTP account in the panel.
2. Connect with FileZilla and drag the project into `public_html`.

> Do **not** upload your local `config/database.php` over the live one after you've
> set the live credentials — keep the live DB settings intact.

---

## 6. Fix the base path (.htaccess)

If the site is at the **domain root** (`https://sizsomaliland.com/`), open
**`.htaccess`** and set:

```apache
RewriteBase /
```

(The bundled file may say `RewriteBase /Somaliland-innovation/` from local XAMPP —
change it to `/` for a root domain. `BASE_URL` auto-detects the rest.)

---

## 7. Make uploads writable

Ensure these folders exist and are writable (chmod **755**, or 775 if needed):

```
assets/uploads/
assets/uploads/courses/
assets/uploads/documents/
assets/uploads/media/
```

Create any missing ones in File Manager.

---

## 8. Set up WhatsApp Cloud API (now it will actually send)

On a real host, outbound API calls work. Two ways to add credentials:

**A) `.env` file (recommended):** create `.env` in the project root:

```env
WHATSAPP_ENABLED=true
WHATSAPP_API_VERSION=v21.0
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_TOKEN=your_permanent_access_token
WHATSAPP_DEFAULT_COUNTRY_CODE=252
```

**B) Admin panel:** **Admin → Settings → WhatsApp Cloud API** → enable + paste values.

Get the Phone Number ID + token from
<https://developers.facebook.com> → your app → **WhatsApp → API Setup**.

> Use a **permanent** token (Business Settings → System Users) so it doesn't expire in 24h.

---

## 9. First login & lock down

1. Visit `https://yourdomain.com/admin/login.php`
2. Default: `admin@sizsr.com` / `Admin@123`
3. Go to **Settings → Change Password** and set a strong password immediately.

---

## 10. Enable HTTPS

In the panel open **SSL** → install the free **Let's Encrypt** certificate for your
domain. Then force HTTPS (most panels have a "Force HTTPS" toggle). The app already
sets secure cookies automatically when on HTTPS.

---

## ✅ Post-deploy checklist

- [ ] Home page loads at your domain
- [ ] Courses list + course detail pages work
- [ ] Apply from a course → submits, redirects to success
- [ ] Admin login works; password changed
- [ ] Approve a student → WhatsApp sends (or use the **Open WhatsApp & Send** button)
- [ ] `https://` padlock shows; no "Dangerous site" warning
- [ ] `SIZSR_DEBUG` is `false`

---

## About the "Dangerous site" warning

That warning was tied to the free **`kesug.com`** shared domain's bad reputation —
not your code. Using your **own custom domain** on this new host clears it. If you
ever see it on your own domain, request a review in **Google Search Console →
Security Issues**, but on a dedicated domain it normally never appears.
