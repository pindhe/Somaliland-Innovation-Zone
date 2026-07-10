<?php
/**
 * SIZSR Admin - CMS (homepage, about, footer content + stats)
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$adminPage = 'cms';
$pageTitle = 'Website Content';

$cmsFields = [
    'hero_title'        => ['Hero Title', 'text'],
    'hero_subtitle'     => ['Hero Subtitle', 'textarea'],
    'about_history'     => ['About: History', 'textarea'],
    'about_mission'     => ['About: Mission', 'textarea'],
    'about_vision'      => ['About: Vision', 'textarea'],
    'about_objectives'  => ['About: Objectives (one per line)', 'textarea'],
    'footer_about'      => ['Footer: About Text', 'textarea'],
];
$statFields = [
    'stat_students'  => 'Total Students',
    'stat_courses'   => 'Total Courses',
    'stat_graduates' => 'Total Graduates',
    'stat_programs'  => 'Active Programs',
];

if (is_post()) {
    csrf_check();
    foreach ($cmsFields as $key => $_) {
        setting_save('cms_' . $key, trim((string)input($key)));
    }
    foreach ($statFields as $key => $_) {
        setting_save($key, (string)(int)input($key));
    }
    log_activity('cms_updated', 'Website content updated');
    flash('success', 'Website content saved.');
    redirect('admin/cms.php');
}

require __DIR__ . '/includes/header.php';
?>

<form method="post" class="space-y-5 max-w-4xl">
  <?= csrf_field() ?>

  <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4">
    <h3 class="font-bold text-slate-900 dark:text-white">Homepage Statistics</h3>
    <div class="grid sm:grid-cols-4 gap-4">
      <?php foreach ($statFields as $key => $label): ?>
        <div><label class="form-label"><?= e($label) ?></label><input type="number" name="<?= $key ?>" value="<?= e(setting($key, '0')) ?>" class="form-input"></div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4">
    <h3 class="font-bold text-slate-900 dark:text-white">Page Content</h3>
    <?php foreach ($cmsFields as $key => [$label, $type]): ?>
      <div>
        <label class="form-label"><?= e($label) ?></label>
        <?php if ($type === 'textarea'): ?>
          <textarea name="<?= $key ?>" rows="3" class="form-textarea"><?= e(cms($key)) ?></textarea>
        <?php else: ?>
          <input name="<?= $key ?>" value="<?= e(cms($key)) ?>" class="form-input">
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="flex justify-end">
    <button class="px-8 py-3 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold shadow-lg">Save Content</button>
  </div>
</form>

<p class="mt-6 text-sm text-slate-400 max-w-4xl">Manage testimonials, partners, FAQs, success stories and team members directly from the database (phpMyAdmin) or extend these modules. Core content above powers the homepage and about page.</p>

<?php require __DIR__ . '/includes/footer.php'; ?>
