<?php
/**
 * SIZSR Admin - Login
 */
require_once __DIR__ . '/includes/bootstrap.php';

if (is_admin_logged_in()) {
    redirect('admin/index.php');
}

$error = '';

if (is_post()) {
    csrf_check();

    $email    = filter_var((string)input('email'), FILTER_VALIDATE_EMAIL) ?: '';
    $password = (string)input('password');
    $remember = (bool)input('remember');

    // Simple brute-force throttle
    $attempts = $_SESSION['_login_attempts'] ?? 0;
    $lockedUntil = $_SESSION['_login_locked'] ?? 0;

    if ($lockedUntil > time()) {
        $error = 'Too many attempts. Please try again in ' . ($lockedUntil - time()) . 's.';
    } elseif ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $admin = db_one("SELECT * FROM admins WHERE email = ? AND status = 'active' LIMIT 1", [$email]);
        if ($admin && verify_password($password, $admin['password'])) {
            unset($_SESSION['_login_attempts'], $_SESSION['_login_locked']);
            admin_login($admin);
            db_exec("UPDATE admins SET last_login = NOW() WHERE id = ?", [$admin['id']]);

            if ($remember) {
                // Extend session cookie to 30 days
                $params = session_get_cookie_params();
                setcookie(session_name(), session_id(), time() + 60 * 60 * 24 * 30, $params['path'], $params['domain'], $params['secure'], true);
            }

            log_activity('admin_login', 'Admin logged in', (int)$admin['id']);
            flash('success', 'Welcome back, ' . $admin['name'] . '!');
            redirect('admin/index.php');
        } else {
            $_SESSION['_login_attempts'] = ++$attempts;
            if ($attempts >= 5) {
                $_SESSION['_login_locked'] = time() + 60;
                $_SESSION['_login_attempts'] = 0;
            }
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login &middot; SIZSR</title>
  <link rel="icon" href="<?= e(setting('org_favicon') ? UPLOAD_URL . '/media/' . setting('org_favicon') : asset('images/favicon.png')) ?>" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: { primary: '#B11314', secondary: '#DEAE1B', accent: '#F9CC21', dark: '#1F2937' },
      fontFamily: { sans: ['Plus Jakarta Sans','system-ui','sans-serif'] } } } };
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <style>
    html, body { margin: 0; }
    /* Slow Ken Burns zoom for the background photo */
    @keyframes kenburns { 0% { transform: scale(1.08) translate(0,0); } 50% { transform: scale(1.16) translate(-1.5%, -1.5%); } 100% { transform: scale(1.08) translate(0,0); } }
    .kenburns { animation: kenburns 22s ease-in-out infinite; }
    /* Animated aurora blobs for the brand panel */
    @keyframes float-slow { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(20px,-26px) scale(1.08); } }
    @keyframes float-slower { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-24px,18px) scale(1.12); } }
    .blob-a { animation: float-slow 13s ease-in-out infinite; }
    .blob-b { animation: float-slower 17s ease-in-out infinite; }
    /* 3D tilt card */
    .scene { perspective: 1200px; }
    .tilt { transform-style: preserve-3d; transition: transform .25s cubic-bezier(.2,.7,.2,1); will-change: transform; }
    .tilt .pop { transform: translateZ(40px); }
    /* Gentle floating logo */
    @keyframes bob { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
    .bob { animation: bob 5s ease-in-out infinite; }
    /* Feature rows hover lift */
    .feat { transition: transform .25s ease, background-color .25s ease; }
    .feat:hover { transform: translateX(6px); background-color: rgba(255,255,255,.08); }
    /* Subtle grid overlay */
    .grid-overlay {
      background-image: linear-gradient(rgba(255,255,255,.07) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(255,255,255,.07) 1px, transparent 1px);
      background-size: 44px 44px;
      mask-image: radial-gradient(ellipse 80% 70% at 50% 40%, #000 40%, transparent 100%);
    }
    /* Staggered entrance */
    .stagger > * { opacity: 0; animation: fadeUpIn .65s cubic-bezier(.2,.7,.2,1) forwards; }
    .stagger > *:nth-child(1) { animation-delay: .05s; }
    .stagger > *:nth-child(2) { animation-delay: .14s; }
    .stagger > *:nth-child(3) { animation-delay: .23s; }
    .stagger > *:nth-child(4) { animation-delay: .32s; }
    .stagger > *:nth-child(5) { animation-delay: .41s; }
    .stagger > *:nth-child(6) { animation-delay: .50s; }
    .stagger > *:nth-child(7) { animation-delay: .59s; }
    /* Icon-aware inputs */
    .field { position: relative; }
    .field .field-icon { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; transition: color .15s; }
    .field input.form-input { padding-left: 2.7rem; }
    .field:focus-within .field-icon { color: var(--secondary); }
    @media (prefers-reduced-motion: reduce) {
      .blob-a, .blob-b, .kenburns, .bob { animation: none; }
      .stagger > * { opacity: 1; animation: none; }
      .tilt { transition: none; }
    }
  </style>
</head>
<body class="font-sans min-h-screen grid lg:grid-cols-2 bg-[#F8FAFC] text-slate-800">

  <!-- Brand panel -->
  <div class="hidden lg:flex relative overflow-hidden p-12 text-white flex-col justify-between min-h-screen">
    <!-- Background photo -->
    <div class="kenburns absolute inset-0 bg-cover bg-center" style="background-image:url('<?= asset('images/hero-bg.png') ?>')"></div>
    <!-- Brand gradient overlay for readability -->
    <div class="absolute inset-0 bg-gradient-to-br from-[#00403d]/95 via-primary/85 to-secondary/75"></div>
    <div class="grid-overlay absolute inset-0"></div>
    <div class="blob-a absolute -top-24 -right-24 h-96 w-96 rounded-full bg-accent/20 blur-3xl"></div>
    <div class="blob-b absolute -bottom-28 -left-20 h-96 w-96 rounded-full bg-secondary/40 blur-3xl"></div>

    <div class="relative flex items-center gap-3">
      <img src="<?= asset('images/logo.png') ?>" alt="SIZSR" class="bob h-12 w-12 rounded-2xl object-cover ring-1 ring-white/20 shadow-lg">
      <div class="leading-tight">
        <span class="block text-lg font-extrabold tracking-tight">SIZSR</span>
        <span class="block text-xs text-white/60">Admin Console</span>
      </div>
    </div>

    <div class="relative max-w-md">
      <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white/80 ring-1 ring-white/15 backdrop-blur">
        <span class="h-1.5 w-1.5 rounded-full bg-accent"></span> Secure Admin Access
      </span>
      <h2 class="mt-6 text-4xl font-extrabold leading-tight">Somaliland Innovation Zone</h2>
      <p class="mt-4 text-white/75 text-lg">Student Registration System &mdash; manage courses, applications and content from one powerful dashboard.</p>

      <ul class="mt-8 space-y-2.5 text-sm text-white/90">
        <li class="feat flex items-center gap-3 rounded-2xl px-3 py-2.5 ring-1 ring-white/5 backdrop-blur-sm">
          <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white/10 ring-1 ring-white/15 shadow-lg shadow-black/20">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
          </span>
          Real-time dashboard &amp; analytics
        </li>
        <li class="feat flex items-center gap-3 rounded-2xl px-3 py-2.5 ring-1 ring-white/5 backdrop-blur-sm">
          <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white/10 ring-1 ring-white/15 shadow-lg shadow-black/20">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8M16 17H8M10 9H8"/></svg>
          </span>
          Manage applications &amp; courses
        </li>
        <li class="feat flex items-center gap-3 rounded-2xl px-3 py-2.5 ring-1 ring-white/5 backdrop-blur-sm">
          <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white/10 ring-1 ring-white/15 shadow-lg shadow-black/20">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </span>
          Encrypted &amp; role-based security
        </li>
      </ul>
    </div>

    <p class="relative text-white/55 text-sm">&copy; <?= date('Y') ?> Somaliland Innovation Zone. All rights reserved.</p>
  </div>

  <!-- Form panel -->
  <div class="scene relative flex items-center justify-center p-6 sm:p-10 min-h-screen bg-gradient-to-br from-white via-[#F8FAFC] to-[#eef6f5]">
    <div id="authCard" class="tilt w-full max-w-md rounded-3xl bg-white/80 backdrop-blur ring-1 ring-slate-200/70 shadow-2xl shadow-slate-400/20 p-8 sm:p-10 stagger">
      <div class="lg:hidden flex items-center gap-3 mb-8">
        <img src="<?= asset('images/logo.png') ?>" alt="SIZSR" class="h-11 w-11 rounded-2xl object-cover shadow">
        <span class="text-xl font-extrabold text-slate-900">SIZSR</span>
      </div>

      <div>
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Welcome back</h1>
        <p class="mt-2 text-slate-500">Sign in to your admin account to continue.</p>
      </div>

      <?php if ($error): ?>
        <div class="mt-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          <svg class="mt-0.5 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
          <span><?= e($error) ?></span>
        </div>
      <?php endif; ?>
      <?php foreach (get_flashes() as $f): if ($f['type']==='success') continue; ?>
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?= e($f['message']) ?></div>
      <?php endforeach; ?>

      <form method="post" id="loginForm" class="mt-8 space-y-5">
        <?= csrf_field() ?>
        <div>
          <label class="form-label" for="email">Email Address</label>
          <div class="field">
            <span class="field-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>
            </span>
            <input type="email" id="email" name="email" required autofocus value="<?= e((string)input('email','')) ?>" class="form-input" placeholder="Enter admin email" autocomplete="username">
          </div>
        </div>
        <div>
          <label class="form-label" for="pwd">Password</label>
          <div class="field">
            <span class="field-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input type="password" name="password" id="pwd" required class="form-input pr-12" placeholder="Enter your password" autocomplete="current-password">
            <button type="button" id="togglePwd" aria-label="Show password" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-secondary transition">
              <svg id="eyeOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg id="eyeOff" class="hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c6.5 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.5 13.5 0 0 0 2 11s3.5 7 10 7a9.7 9.7 0 0 0 5.39-1.61"/><path d="m2 2 20 20M9.5 9.5a3 3 0 0 0 4.24 4.24"/></svg>
            </button>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer select-none">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary"> Remember me
          </label>
          <a href="<?= e(admin_url('forgot-password.php')) ?>" class="text-sm font-semibold text-primary hover:underline">Forgot password?</a>
        </div>
        <button type="submit" id="submitBtn" class="w-full inline-flex items-center justify-center gap-2 py-3.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold shadow-lg shadow-primary/25 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-primary/30 active:translate-y-0 transition disabled:opacity-70 disabled:cursor-not-allowed">
          <span id="btnText">Sign In</span>
          <svg id="btnArrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          <span id="btnSpinner" class="spinner hidden"></span>
        </button>
      </form>

      <a href="<?= url('') ?>" class="mt-8 inline-flex items-center justify-center gap-1.5 w-full text-sm text-slate-500 hover:text-primary transition">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        Back to website
      </a>
    </div>
  </div>

  <script>
    // Password visibility toggle
    const toggle = document.getElementById('togglePwd');
    const pwd = document.getElementById('pwd');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeOff = document.getElementById('eyeOff');
    toggle.addEventListener('click', () => {
      const show = pwd.type === 'password';
      pwd.type = show ? 'text' : 'password';
      eyeOpen.classList.toggle('hidden', show);
      eyeOff.classList.toggle('hidden', !show);
      toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });

    // Submit loading state
    document.getElementById('loginForm').addEventListener('submit', () => {
      const btn = document.getElementById('submitBtn');
      btn.disabled = true;
      document.getElementById('btnText').textContent = 'Signing in...';
      document.getElementById('btnArrow').classList.add('hidden');
      document.getElementById('btnSpinner').classList.remove('hidden');
    });

    // Subtle 3D tilt on the auth card (pointer-driven)
    (function () {
      const card = document.getElementById('authCard');
      const scene = card.closest('.scene');
      const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (!card || !scene || reduce || window.matchMedia('(hover: none)').matches) return;
      const MAX = 6; // degrees
      scene.addEventListener('mousemove', (e) => {
        const r = card.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width - 0.5;
        const py = (e.clientY - r.top) / r.height - 0.5;
        card.style.transform = `rotateY(${px * MAX}deg) rotateX(${-py * MAX}deg) translateZ(0)`;
      });
      scene.addEventListener('mouseleave', () => { card.style.transform = 'rotateY(0) rotateX(0)'; });
    })();
  </script>
</body>
</html>
