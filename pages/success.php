<?php
if (!defined('ROOT_PATH')) { require_once dirname(__DIR__) . '/config/config.php'; }

$pageTitle = 'Application Submitted';
$activeNav = '';
$reference = $_SESSION['application_ref'] ?? null;
unset($_SESSION['application_ref']);

require INCLUDES_PATH . '/header.php';
?>

<section class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-20 text-center">
  <div class="reveal is-visible">
    <div class="mx-auto h-24 w-24 rounded-full bg-emerald-100 dark:bg-emerald-500/10 grid place-items-center mb-8">
      <svg class="h-12 w-12 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white">Thank you for applying!</h1>
    <p class="mt-4 text-lg text-slate-600 dark:text-slate-300">Your application has been submitted successfully. Somaliland Innovation Zone will review it and contact you soon.</p>

    <?php if ($reference): ?>
      <div class="mt-8 inline-flex flex-col items-center rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 px-8 py-5 shadow-sm">
        <span class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Your Reference Number</span>
        <span class="mt-1 text-2xl font-extrabold text-primary dark:text-secondary tracking-wider"><?= e($reference) ?></span>
        <span class="mt-1 text-xs text-slate-400">Keep this for your records</span>
      </div>
    <?php endif; ?>

    <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
      <a href="<?= url('courses') ?>" class="px-6 py-3.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-semibold hover:-translate-y-0.5 transition">Browse More Courses</a>
      <a href="<?= url('') ?>" class="px-6 py-3.5 rounded-xl border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-200 font-semibold">Back to Home</a>
    </div>
  </div>
</section>

<?php require INCLUDES_PATH . '/footer.php'; ?>
