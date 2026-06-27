<?php
/**
 * SIZSR Admin - Reset password using a valid token.
 */
require_once __DIR__ . '/includes/bootstrap.php';

$token = preg_replace('#[^a-f0-9]#', '', (string)input('token'));
$error = '';
$done  = false;

$admin = $token ? db_one("SELECT id FROM admins WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1", [$token]) : null;

if (is_post()) {
    csrf_check();
    if (!$admin) {
        $error = 'Invalid or expired reset link.';
    } else {
        $pwd  = (string)input('password');
        $pwd2 = (string)input('password_confirm');
        if (strlen($pwd) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($pwd !== $pwd2) {
            $error = 'Passwords do not match.';
        } else {
            db_exec("UPDATE admins SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?", [hash_password($pwd), $admin['id']]);
            log_activity('password_reset', 'Password was reset', (int)$admin['id']);
            $done = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password &middot; SIZSR</title>
  <link rel="icon" href="<?= e(asset('images/favicon.png')) ?>" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { theme: { extend: { colors: { primary: '#B11314', secondary: '#DEAE1B' }, fontFamily: { sans: ['Plus Jakarta Sans','system-ui','sans-serif'] } } } };</script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="font-sans min-h-screen grid place-items-center bg-[#F8FAFC] p-6">
  <div class="w-full max-w-md rounded-3xl bg-white border border-slate-100 shadow-xl p-8">
    <h1 class="text-2xl font-extrabold text-slate-900">Set a new password</h1>

    <?php if ($done): ?>
      <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">Your password has been updated.</div>
      <a href="<?= e(admin_url('login.php')) ?>" class="mt-6 block text-center py-3 rounded-xl bg-primary text-white font-semibold">Go to login</a>
    <?php elseif (!$admin): ?>
      <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">This reset link is invalid or has expired.</div>
      <a href="<?= e(admin_url('forgot-password.php')) ?>" class="mt-6 block text-center text-sm text-primary">Request a new link</a>
    <?php else: ?>
      <?php if ($error): ?><div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= e($error) ?></div><?php endif; ?>
      <form method="post" class="mt-6 space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div><label class="form-label">New Password</label><input type="password" name="password" required class="form-input"></div>
        <div><label class="form-label">Confirm Password</label><input type="password" name="password_confirm" required class="form-input"></div>
        <button class="w-full py-3.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold">Update Password</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
