<?php
/**
 * SIZSR Admin - Settings (organization, social, email, logo/favicon)
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$adminPage = 'settings';
$pageTitle = 'Settings';

$textFields = [
    'org_name'    => 'Organization Name',
    'org_email'   => 'Contact Email',
    'org_phone'   => 'Contact Phone',
    'org_address' => 'Address',
];
$socialFields = [
    'social_facebook'  => 'Facebook URL',
    'social_twitter'   => 'Twitter / X URL',
    'social_linkedin'  => 'LinkedIn URL',
    'social_instagram' => 'Instagram URL',
];
$emailFields = [
    'smtp_host' => 'SMTP Host',
    'smtp_port' => 'SMTP Port',
    'smtp_user' => 'SMTP Username',
    'smtp_from' => 'From Email',
];
$whatsappFields = [
    'whatsapp_phone_number_id'      => 'Phone Number ID',
    'whatsapp_api_version'          => 'API Version',
    'whatsapp_default_country_code' => 'Default Country Code',
];

if (is_post()) {
    csrf_check();
    $action = (string)input('action', 'general');

    if ($action === 'password') {
        $admin = current_admin();
        $row = db_one("SELECT password FROM admins WHERE id=?", [$admin['id']]);
        $current = (string)input('current_password');
        $new = (string)input('new_password');
        $confirm = (string)input('confirm_password');
        if (!$row || !verify_password($current, $row['password'])) {
            flash('error', 'Current password is incorrect.');
        } elseif (strlen($new) < 8) {
            flash('error', 'New password must be at least 8 characters.');
        } elseif ($new !== $confirm) {
            flash('error', 'New passwords do not match.');
        } else {
            db_exec("UPDATE admins SET password=? WHERE id=?", [hash_password($new), $admin['id']]);
            log_activity('password_changed', 'Admin changed their password');
            flash('success', 'Password updated.');
        }
    } else {
        foreach (array_merge(array_keys($textFields), array_keys($socialFields), array_keys($emailFields), array_keys($whatsappFields)) as $key) {
            setting_save($key, trim((string)input($key)));
        }
        if (input('smtp_pass') !== '') setting_save('smtp_pass', (string)input('smtp_pass'));

        // WhatsApp Cloud API
        setting_save('whatsapp_enabled', input('whatsapp_enabled') ? '1' : '0');
        if (input('whatsapp_token') !== '') setting_save('whatsapp_token', (string)input('whatsapp_token'));

        // Logo / favicon uploads
        foreach (['org_logo' => 'logo', 'org_favicon' => 'favicon'] as $key => $sub) {
            if (!empty($_FILES[$key]['name'])) {
                $res = handle_upload($_FILES[$key], 'media', ALLOWED_IMAGE_TYPES);
                if ($res['success']) setting_save($key, $res['filename']);
                else flash('error', ucfirst($sub) . ': ' . $res['error']);
            }
        }
        log_activity('settings_updated', 'Settings updated');
        flash('success', 'Settings saved.');
    }
    redirect('admin/settings.php');
}

require __DIR__ . '/includes/header.php';
?>

<div class="grid lg:grid-cols-3 gap-5 max-w-6xl">
  <!-- General -->
  <form method="post" enctype="multipart/form-data" class="lg:col-span-2 space-y-5">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="general">

    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4">
      <h3 class="font-bold text-slate-900 dark:text-white">Organization</h3>
      <div class="grid sm:grid-cols-2 gap-4">
        <?php foreach ($textFields as $key => $label): ?>
          <div class="<?= $key==='org_address'?'sm:col-span-2':'' ?>"><label class="form-label"><?= e($label) ?></label><input name="<?= $key ?>" value="<?= e(setting($key)) ?>" class="form-input"></div>
        <?php endforeach; ?>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="form-label">Logo</label>
          <?php if (setting('org_logo')): ?><img src="<?= e(UPLOAD_URL.'/media/'.setting('org_logo')) ?>" class="h-12 mb-2 rounded" alt=""><?php endif; ?>
          <input type="file" name="org_logo" accept=".jpg,.jpeg,.png,.webp,.svg" class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:font-semibold">
        </div>
        <div>
          <label class="form-label">Favicon</label>
          <?php if (setting('org_favicon')): ?><img src="<?= e(UPLOAD_URL.'/media/'.setting('org_favicon')) ?>" class="h-10 mb-2 rounded" alt=""><?php endif; ?>
          <input type="file" name="org_favicon" accept=".png,.svg,.ico,.jpg" class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:font-semibold">
        </div>
      </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4">
      <h3 class="font-bold text-slate-900 dark:text-white">Social Media</h3>
      <div class="grid sm:grid-cols-2 gap-4">
        <?php foreach ($socialFields as $key => $label): ?>
          <div><label class="form-label"><?= e($label) ?></label><input name="<?= $key ?>" value="<?= e(setting($key)) ?>" class="form-input" placeholder="https://"></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4">
      <h3 class="font-bold text-slate-900 dark:text-white">Email (SMTP)</h3>
      <div class="grid sm:grid-cols-2 gap-4">
        <?php foreach ($emailFields as $key => $label): ?>
          <div><label class="form-label"><?= e($label) ?></label><input name="<?= $key ?>" value="<?= e(setting($key)) ?>" class="form-input"></div>
        <?php endforeach; ?>
        <div><label class="form-label">SMTP Password</label><input type="password" name="smtp_pass" value="" class="form-input" placeholder="<?= setting('smtp_pass') ? '••••••••' : '' ?>"></div>
      </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-2">
          <span class="grid place-items-center h-8 w-8 rounded-lg bg-emerald-500/10 text-emerald-500 shrink-0">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm5.8 14.16c-.25.69-1.45 1.32-1.99 1.36-.53.05-.53.42-3.34-.7-2.81-1.12-4.6-3.99-4.74-4.17-.14-.18-1.13-1.5-1.13-2.87 0-1.36.71-2.03.97-2.31.25-.28.55-.35.74-.35.18 0 .37 0 .53.01.17.01.4-.06.62.48.25.59.83 2.04.9 2.19.07.14.11.31.02.49-.08.18-.13.3-.25.45-.13.15-.27.34-.39.45-.13.13-.26.27-.11.52.14.25.64 1.05 1.37 1.7.94.83 1.73 1.09 1.98 1.21.25.12.39.1.53-.06.14-.16.61-.71.78-.95.16-.25.32-.21.54-.13.22.08 1.41.66 1.65.78.25.12.41.18.47.28.06.1.06.59-.19 1.28Z"/></svg>
          </span>
          <h3 class="font-bold text-slate-900 dark:text-white">WhatsApp Cloud API</h3>
        </div>
        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200 cursor-pointer">
          <input type="checkbox" name="whatsapp_enabled" value="1" <?= setting('whatsapp_enabled')==='1'?'checked':'' ?> class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500"> Enabled
        </label>
      </div>
      <p class="text-xs text-slate-400 -mt-2">Used to auto-send the course group link when an application is approved. Values set in a <code>.env</code> file override these.</p>
      <div class="grid sm:grid-cols-2 gap-4">
        <?php foreach ($whatsappFields as $key => $label):
          $envOverride = getenv(strtoupper($key)) !== false && getenv(strtoupper($key)) !== ''; ?>
          <div>
            <label class="form-label"><?= e($label) ?><?php if ($envOverride): ?> <span class="text-[10px] font-bold uppercase text-emerald-500">env</span><?php endif; ?></label>
            <input name="<?= $key ?>" value="<?= e(setting($key)) ?>" class="form-input" <?= $envOverride?'disabled':'' ?> placeholder="<?= $key==='whatsapp_api_version'?'v21.0':($key==='whatsapp_default_country_code'?'252':'') ?>">
          </div>
        <?php endforeach; ?>
        <?php $tokenEnv = getenv('WHATSAPP_TOKEN') !== false && getenv('WHATSAPP_TOKEN') !== ''; ?>
        <div class="sm:col-span-2">
          <label class="form-label">Access Token<?php if ($tokenEnv): ?> <span class="text-[10px] font-bold uppercase text-emerald-500">env</span><?php endif; ?></label>
          <input type="password" name="whatsapp_token" value="" autocomplete="new-password" class="form-input" <?= $tokenEnv?'disabled':'' ?> placeholder="<?= $tokenEnv ? 'Set via .env' : (setting('whatsapp_token') ? '••••••••  (saved)' : 'Paste permanent access token') ?>">
        </div>
      </div>
    </div>

    <div class="flex justify-end"><button class="px-8 py-3 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold shadow-lg">Save Settings</button></div>
  </form>

  <!-- Password -->
  <div class="space-y-5">
    <form method="post" class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password">
      <h3 class="font-bold text-slate-900 dark:text-white">Change Password</h3>
      <div><label class="form-label">Current Password</label><input type="password" name="current_password" required class="form-input"></div>
      <div><label class="form-label">New Password</label><input type="password" name="new_password" required class="form-input"></div>
      <div><label class="form-label">Confirm Password</label><input type="password" name="confirm_password" required class="form-input"></div>
      <button class="w-full py-3 rounded-xl bg-slate-800 dark:bg-white/10 text-white font-semibold">Update Password</button>
    </form>

    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm">
      <h3 class="font-bold text-slate-900 dark:text-white mb-3">Data Backup</h3>
      <p class="text-sm text-slate-500 mb-4">Export all key tables as a JSON backup file.</p>
      <a href="<?= e(admin_url('backup.php')) ?>" class="block text-center py-3 rounded-xl border border-slate-200 dark:border-white/10 font-semibold text-slate-600 dark:text-slate-300">Download Backup</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
