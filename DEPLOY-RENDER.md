# Deploy SIZSR on Render

This project is **PHP + MySQL**. Render does **not** include MySQL, so you use:

- **Render** → runs the PHP website (Docker)
- **Railway** (or similar) → hosts the MySQL database

---

## What you need

1. [GitHub](https://github.com) account (free)
2. [Render](https://render.com) account (free tier)
3. [Railway](https://railway.app) account (free trial / low-cost MySQL)

> **Note:** `database/sizsr.sql` must contain your schema. If the file is empty,
> export from phpMyAdmin on XAMPP or your live host before continuing.

---

## Part 1 — Push code to GitHub

1. Create a new repo on GitHub, e.g. `sizsr`.
2. In your project folder, run:

```bash
git init
git add .
git commit -m "Initial SIZSR deploy"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/sizsr.git
git push -u origin main
```

Do **not** commit `.env` (it is in `.gitignore`).

---

## Part 2 — Create MySQL on Railway

1. Go to [railway.app](https://railway.app) → **New Project**.
2. Click **Add service** → **Database** → **MySQL**.
3. Open the MySQL service → **Connect** tab → copy:
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLDATABASE`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`

4. Import your database:
   - Install [Railway CLI](https://docs.railway.app/develop/cli) or use a GUI like **TablePlus** / **DBeaver**.
   - Connect with the credentials above.
   - Import `database/sizsr.sql` (or your phpMyAdmin export).

---

## Part 3 — Deploy the website on Render

1. Go to [dashboard.render.com](https://dashboard.render.com) → **New +** → **Web Service**.
2. Connect your **GitHub** repo.
3. Settings:

| Setting | Value |
|---------|--------|
| **Name** | `sizsr` |
| **Region** | Closest to your users |
| **Branch** | `main` |
| **Runtime** | **Docker** |
| **Instance type** | Free (or paid for always-on) |

4. Under **Environment Variables**, add:

| Key | Value (from Railway) |
|-----|----------------------|
| `SIZSR_DEBUG` | `false` |
| `DB_HOST` | Railway `MYSQLHOST` |
| `DB_PORT` | Railway `MYSQLPORT` (usually `3306`) |
| `DB_NAME` | Railway `MYSQLDATABASE` |
| `DB_USER` | Railway `MYSQLUSER` |
| `DB_PASS` | Railway `MYSQLPASSWORD` |
| `WHATSAPP_ENABLED` | `true` |
| `WHATSAPP_PHONE_NUMBER_ID` | your Meta Phone Number ID |
| `WHATSAPP_TOKEN` | your Meta access token |
| `WHATSAPP_DEFAULT_COUNTRY_CODE` | `252` |

5. Click **Create Web Service**. Render builds the `Dockerfile` and deploys.

Your site will be at: `https://sizsr.onrender.com` (or the name you chose).

---

## Part 4 — After deploy

1. Open `https://YOUR-APP.onrender.com/admin/login.php`
2. Login: `admin@sizsr.com` / `Admin@123` (change password in Settings).
3. Edit each course → add **WhatsApp Group Invite Link**.
4. Test apply form + admin approval + WhatsApp.

### Custom domain (optional)

Render → your service → **Settings** → **Custom Domains** → add `sizsomaliland.com` and update DNS at your registrar.

---

## Important Render limitations

### 1. Free tier sleeps
After ~15 minutes of no traffic, the site **spins down**. First visit may take **30–60 seconds** to wake up. Use a **paid** plan for always-on.

### 2. File uploads are not permanent (free tier)
Student documents and course images saved to `assets/uploads/` may be **lost when Render redeploys** or restarts. For production, use:
- Render **persistent disk** (paid), or
- Cloud storage (S3, Cloudinary) — requires code changes.

### 3. WhatsApp Cloud API
Render **allows** outbound HTTPS, so auto-send and **Send WhatsApp to all approved** work once Meta credentials are set.

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Database connection failed | Check `DB_*` env vars match Railway exactly |
| 404 on pretty URLs | Dockerfile sets `RewriteBase /` — redeploy after pulling latest |
| Blank admin applications page | Import DB; ensure `whatsapp_group_link` column exists |
| Slow first load | Free tier cold start — normal |
| Build fails | Check Render logs; ensure `Dockerfile` is in repo root |

---

## Quick architecture

```
Student browser
      ↓
Render (Docker: PHP 8 + Apache)  ←  your GitHub repo
      ↓
Railway MySQL                      ←  database/sizsr.sql
      ↓
Meta WhatsApp Cloud API            ←  approval messages
```

---

## Alternative: Render Blueprint

If `render.yaml` is in your repo root, you can use **New +** → **Blueprint** and connect the repo. You still must create MySQL on Railway and paste `DB_*` values manually.

---

© Somaliland Innovation Zone — SIZSR
