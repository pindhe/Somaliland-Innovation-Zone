<?php
/**
 * SIZSR Admin - Forgot password (generates a reset token; emailing requires SMTP config).
 */
require_once __DIR__ . '/includes/bootstrap.php';

$message = '';
$token   = '';

if (is_post()) {
    csrf_check();
    $email = filter_var((string)input('email'), FILTER_VALIDATE_EMAIL) ?: '';
    if ($email === '') {
        $message = 'Please enter a valid email.';
    } else {
        $admin = db_one("SELECT id FROM admins WHERE email = ? AND status='active'", [$email]);
        if ($admin) {
            $token = bin2hex(random_bytes(24));
            db_exec("UPDATE admins SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?", [$token, $admin['id']]);
            log_activity('password_reset_requested', "Reset requested for {$email}", (int)$admin['id']);
        }
        // Always show same message (avoid user enumeration)
        $message = 'If an account exists for that email, a password reset link has been generated.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password &middot; SIZSR</title>
  <link rel="icon" href="<?= e(asset('images/favicon.png')) ?>" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { theme: { extend: { colors: { primary: '#B11314', secondary: '#DEAE1B' }, fontFamily: { sans: ['Plus Jakarta Sans','system-ui','sans-serif'] } } } };</script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="font-sans min-h-screen grid place-items-center bg-[#F8FAFC] p-6">
  <div class="w-full max-w-md rounded-3xl bg-white border border-slate-100 shadow-xl p-8">
    <img src="<?= asset('images/logo.png') ?>" alt="SIZSR" class="h-12 w-12 rounded-2xl object-cover shadow mb-6">
    <h1 class="text-2xl font-extrabold text-slate-900">Reset your password</h1>
    <p class="mt-2 text-slate-500">Enter your account email and we'll generate a reset link.</p>

    <?php if ($message): ?>
      <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= e($message) ?></div>
      <?php if ($token): ?>
        <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600 break-all">
          <strong>Reset link (dev):</strong><br>
          <?= e(admin_url('reset-password.php?token=' . $token)) ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <form method="post" class="mt-6 space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="form-label">Email Address</label>
        <input type="email" name="email" required class="form-input" placeholder="admin@sizsr.com">
      </div>
      <button class="w-full py-3.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold">Send Reset Link</button>
    </form>
    <a href="<?= e(admin_url('login.php')) ?>" class="mt-6 block text-center text-sm text-slate-500 hover:text-primary">&larr; Back to login</a>
  </div>
</body>
</html>
