</main>

<!-- ===================== FOOTER ===================== -->
<footer class="mt-24 bg-dark text-slate-300">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 grid gap-10 md:grid-cols-2 lg:grid-cols-4">
    <div class="lg:col-span-2">
      <div class="flex items-center gap-2.5 mb-4">
        <img src="<?= asset('images/logo.png') ?>" alt="SIZSR" class="h-10 w-10 rounded-xl object-cover">
        <span class="font-extrabold text-white text-lg">SIZSR</span>
      </div>
      <p class="text-sm leading-relaxed text-slate-400"><?= e(cms('footer_about', 'Somaliland Innovation Zone student registration platform.')) ?></p>
      <div class="flex gap-3 mt-5">
        <?php
        $socials = [
            'social_facebook'  => 'M22 12a10 10 0 10-11.5 9.9v-7H8v-2.9h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6v1.9H16l-.4 2.9h-2.1v7A10 10 0 0022 12z',
            'social_twitter'   => 'M18.9 1.2h3.7l-8 9.1L24 22.8h-7.4l-5.8-7.6-6.6 7.6H.5l8.6-9.8L0 1.2h7.6l5.2 6.9 6.1-6.9zm-1.3 19.4h2L6.5 3.3h-2.2l13.3 17.3z',
            'social_linkedin'  => 'M4.98 3.5a2.5 2.5 0 11-.02 5 2.5 2.5 0 01.02-5zM3 9h4v12H3zM10 9h3.8v1.7h.05c.53-1 1.8-2.05 3.7-2.05 4 0 4.7 2.6 4.7 6V21H18v-5.3c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9V21h-4z',
            'social_instagram' => 'M12 2.2c3.2 0 3.6 0 4.9.07 1.2.06 1.8.25 2.2.42.6.2 1 .5 1.4 1 .5.4.8.8 1 1.4.17.4.36 1 .42 2.2.06 1.3.07 1.7.07 4.9s0 3.6-.07 4.9c-.06 1.2-.25 1.8-.42 2.2-.2.6-.5 1-1 1.4-.4.5-.8.8-1.4 1-.4.17-1 .36-2.2.42-1.3.06-1.7.07-4.9.07s-3.6 0-4.9-.07c-1.2-.06-1.8-.25-2.2-.42-.6-.2-1-.5-1.4-1-.5-.4-.8-.8-1-1.4-.17-.4-.36-1-.42-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.07-4.9c.06-1.2.25-1.8.42-2.2.2-.6.5-1 1-1.4.4-.5.8-.8 1.4-1 .4-.17 1-.36 2.2-.42C8.4 2.2 8.8 2.2 12 2.2zm0 4.9a4.9 4.9 0 100 9.8 4.9 4.9 0 000-9.8zm0 8.1a3.2 3.2 0 110-6.4 3.2 3.2 0 010 6.4zm6.2-8.3a1.15 1.15 0 11-2.3 0 1.15 1.15 0 012.3 0z',
        ];
        foreach ($socials as $key => $path):
            $link = setting($key);
            if (!$link) continue;
        ?>
        <a href="<?= e($link) ?>" target="_blank" rel="noopener" class="grid place-items-center h-9 w-9 rounded-lg bg-white/5 hover:bg-primary transition">
          <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="<?= $path ?>"/></svg>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <h4 class="text-white font-bold mb-4">Quick Links</h4>
      <ul class="space-y-2.5 text-sm">
        <li><a href="<?= url('') ?>" class="hover:text-secondary transition">Home</a></li>
        <li><a href="<?= url('courses') ?>" class="hover:text-secondary transition">Courses</a></li>
        <li><a href="<?= url('about') ?>" class="hover:text-secondary transition">About Us</a></li>
        <li><a href="<?= url('admin/login.php') ?>" class="hover:text-secondary transition">Admin Login</a></li>
      </ul>
    </div>

    <div>
      <h4 class="text-white font-bold mb-4">Contact</h4>
      <ul class="space-y-3 text-sm text-slate-400">
        <li class="flex items-start gap-3">
          <svg class="h-5 w-5 text-secondary shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="2.5"/></svg>
          <span><?= e(setting('org_address')) ?></span>
        </li>
        <li class="flex items-start gap-3">
          <svg class="h-5 w-5 text-secondary shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
          <span><?= e(setting('org_phone')) ?></span>
        </li>
        <li class="flex items-start gap-3">
          <svg class="h-5 w-5 text-secondary shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75A2.25 2.25 0 015.25 4.5h13.5A2.25 2.25 0 0121 6.75v10.5a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 17.25V6.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 7l8.5 6 8.5-6"/></svg>
          <span><?= e(setting('org_email')) ?></span>
        </li>
      </ul>
    </div>
  </div>

  <div class="border-t border-white/10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
      <p>&copy; <?= date('Y') ?> <?= e(setting('org_name', ORG_NAME)) ?>. All rights reserved.</p>
      <p class="inline-flex items-center gap-1.5">pindhe,
        <a href="https://github.com/pindhe" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-slate-400 hover:text-secondary transition">
          <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .5C5.73.5.5 5.73.5 12c0 5.08 3.29 9.39 7.86 10.91.58.11.79-.25.79-.56 0-.27-.01-1-.02-1.96-3.2.7-3.88-1.54-3.88-1.54-.52-1.33-1.28-1.68-1.28-1.68-1.05-.72.08-.7.08-.7 1.16.08 1.77 1.19 1.77 1.19 1.03 1.77 2.7 1.26 3.36.96.1-.75.4-1.26.73-1.55-2.55-.29-5.24-1.28-5.24-5.69 0-1.26.45-2.29 1.19-3.1-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.18 1.18a11.07 11.07 0 015.8 0c2.2-1.49 3.17-1.18 3.17-1.18.63 1.59.23 2.76.11 3.05.74.81 1.19 1.84 1.19 3.1 0 4.42-2.69 5.39-5.25 5.68.41.36.78 1.06.78 2.14 0 1.55-.01 2.8-.01 3.18 0 .31.21.68.8.56A11.51 11.51 0 0023.5 12C23.5 5.73 18.27.5 12 .5z"/></svg>
          
        </a>
      </p>
    </div>
  </div>
</footer>

<!-- Floating action buttons -->
<div class="fixed bottom-6 right-6 z-40 flex flex-col items-center gap-3">
  <!-- Facebook -->
  <a href="<?= e(setting('social_facebook') ?: '#') ?>" target="_blank" rel="noopener" aria-label="Follow us on Facebook" title="Facebook"
     class="fab group grid place-items-center h-12 w-12 rounded-full bg-[#1877F2] text-white shadow-xl shadow-blue-500/30 hover:-translate-y-1 hover:shadow-blue-500/50 transition-all">
    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 10-11.5 9.9v-7H8v-2.9h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6v1.9H16l-.4 2.9h-2.1v7A10 10 0 0022 12z"/></svg>
  </a>

  <!-- Dark / Light mode -->
  <button id="fabTheme" type="button" aria-label="Toggle dark mode" title="Toggle theme"
     class="fab grid place-items-center h-12 w-12 rounded-full bg-white dark:bg-slate-800 text-slate-700 dark:text-amber-300 border border-slate-200 dark:border-white/10 shadow-xl hover:-translate-y-1 transition-all">
    <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
    <svg class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
  </button>

  <!-- Back to top (shows on scroll) -->
  <button id="backToTop" type="button" aria-label="Back to top" title="Back to top"
     class="fab-top grid place-items-center h-12 w-12 rounded-full bg-primary text-white shadow-xl hover:bg-secondary">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
  </button>
</div>

<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
