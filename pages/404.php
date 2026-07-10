<?php
if (!defined('ROOT_PATH')) { require_once dirname(__DIR__) . '/config/config.php'; }
if (!headers_sent()) { http_response_code(404); }

$pageTitle = 'Page Not Found';
$activeNav = '';

require INCLUDES_PATH . '/header.php';
?>

<section class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-24 text-center">
  <p class="text-8xl font-extrabold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">404</p>
  <h1 class="mt-4 text-3xl font-extrabold text-slate-900 dark:text-white">Page not found</h1>
  <p class="mt-3 text-slate-600 dark:text-slate-300">The page you're looking for doesn't exist or has been moved.</p>
  <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
    <a href="<?= url('') ?>" class="px-6 py-3.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold">Back to Home</a>
    <a href="<?= url('courses') ?>" class="px-6 py-3.5 rounded-xl border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-200 font-semibold">Browse Courses</a>
  </div>
</section>

<?php require INCLUDES_PATH . '/footer.php'; ?>
