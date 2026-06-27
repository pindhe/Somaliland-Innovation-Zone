    </main>
    <footer class="px-6 py-4 text-center text-xs text-slate-400 border-t border-slate-200 dark:border-white/5">
      &copy; <?= date('Y') ?> <?= e(setting('org_name', ORG_NAME)) ?> &middot; SIZSR Admin Panel
    </footer>
  </div>
</div>

<!-- Confirm dialog (blurred backdrop) -->
<div id="confirmModal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
  <div data-confirm-cancel class="fixed inset-0 bg-slate-900/40 backdrop-blur-md"></div>
  <div id="confirmModalPanel" class="relative w-full max-w-sm rounded-3xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-white/10 shadow-2xl p-6 text-center">
    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
    </div>
    <h3 class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white">Are you sure?</h3>
    <p id="confirmModalMsg" class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">This action cannot be undone.</p>
    <div class="mt-6 flex gap-3">
      <button type="button" data-confirm-cancel class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 transition">Cancel</button>
      <button type="button" id="confirmModalOk" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-sm font-semibold shadow-lg shadow-red-500/20 hover:-translate-y-0.5 transition">Delete</button>
    </div>
  </div>
</div>
<style>
  @keyframes confirmIn { from { opacity: 0; transform: translateY(14px) scale(.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
  #confirmModal:not(.hidden) #confirmModalPanel { animation: confirmIn .28s cubic-bezier(.2,.7,.2,1); }
  @media (prefers-reduced-motion: reduce) { #confirmModal #confirmModalPanel { animation: none; } }
</style>

<script>
(function () {
  // Sidebar toggle (mobile)
  const sidebar = document.getElementById('adminSidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  const toggle = document.getElementById('sidebarToggle');
  function openSidebar() { sidebar.classList.remove('-translate-x-full'); backdrop.classList.remove('hidden'); }
  function closeSidebar() { sidebar.classList.add('-translate-x-full'); backdrop.classList.add('hidden'); }
  if (toggle) toggle.addEventListener('click', openSidebar);
  if (backdrop) backdrop.addEventListener('click', closeSidebar);

  // Theme
  const themeToggle = document.getElementById('adminThemeToggle');
  if (themeToggle) themeToggle.addEventListener('click', () => {
    const dark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('siz-admin-theme', dark ? 'dark' : 'light');
  });

  // Profile dropdown
  const menuBtn = document.getElementById('adminMenuBtn');
  const menuDrop = document.getElementById('adminMenuDrop');
  if (menuBtn) {
    menuBtn.addEventListener('click', (e) => { e.stopPropagation(); menuDrop.classList.toggle('hidden'); });
    document.addEventListener('click', () => menuDrop.classList.add('hidden'));
  }

  // Confirm delete (styled modal with blurred backdrop)
  const cModal = document.getElementById('confirmModal');
  const cMsg   = document.getElementById('confirmModalMsg');
  const cOk    = document.getElementById('confirmModalOk');
  let pendingEl = null;

  function openConfirm(el) {
    pendingEl = el;
    cMsg.textContent = el.getAttribute('data-confirm') || 'This action cannot be undone.';
    cModal.classList.remove('hidden');
    cModal.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }
  function closeConfirm() {
    pendingEl = null;
    cModal.classList.add('hidden');
    cModal.classList.remove('flex');
    document.body.style.overflow = '';
  }
  function runPending() {
    const el = pendingEl;
    closeConfirm();
    if (!el) return;
    if (el.form) {
      if (typeof el.form.requestSubmit === 'function') el.form.requestSubmit(el);
      else el.form.submit();
    } else if (el.tagName === 'A' && el.getAttribute('href')) {
      window.location.href = el.href;
    }
  }

  document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); openConfirm(el); });
  });
  if (cOk) cOk.addEventListener('click', runPending);
  if (cModal) cModal.querySelectorAll('[data-confirm-cancel]').forEach((b) => b.addEventListener('click', closeConfirm));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && cModal && !cModal.classList.contains('hidden')) closeConfirm(); });
})();
</script>
<?php if (!empty($adminScripts)) echo $adminScripts; ?>
</body>
</html>
