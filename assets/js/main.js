/* =========================================================================
   SIZSR - Public site JavaScript
   Handles: theme toggle, mobile menu, scroll reveal, counters, FAQ,
   sticky navbar, back-to-top, AJAX forms (contact + newsletter).
   ========================================================================= */
(function () {
  'use strict';

  const SIZ = window.SIZ || { base: '', csrf: '' };

  /* ---------------- Theme toggle ---------------- */
  document.querySelectorAll('#themeToggle, #fabTheme').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const isDark = document.documentElement.classList.toggle('dark');
      localStorage.setItem('siz-theme', isDark ? 'dark' : 'light');
    });
  });

  /* ---------------- Mobile menu ---------------- */
  const menuBtn = document.getElementById('mobileMenuBtn');
  const mobileMenu = document.getElementById('mobileMenu');
  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
  }

  /* ---------------- Sticky navbar shadow ---------------- */
  const navInner = document.getElementById('navInner');
  if (navInner) {
    const onScroll = () => {
      if (window.scrollY > 20) {
        navInner.classList.add('shadow-xl');
        navInner.classList.remove('shadow-lg');
      } else {
        navInner.classList.remove('shadow-xl');
        navInner.classList.add('shadow-lg');
      }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ---------------- Scroll reveal ---------------- */
  const revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('is-visible'));
  }

  /* ---------------- Animated counters ---------------- */
  const counters = document.querySelectorAll('.counter');
  if (counters.length && 'IntersectionObserver' in window) {
    const animate = (el) => {
      const target = parseInt(el.dataset.target || '0', 10);
      const duration = 1600;
      const start = performance.now();
      const tick = (now) => {
        const p = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.floor(eased * target).toLocaleString();
        if (p < 1) requestAnimationFrame(tick);
        else el.textContent = target.toLocaleString();
      };
      requestAnimationFrame(tick);
    };
    const cio = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animate(entry.target);
          cio.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach((c) => cio.observe(c));
  }

  /* ---------------- FAQ accordion ---------------- */
  document.querySelectorAll('.faq-q').forEach((btn) => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.faq-item');
      const answer = item.querySelector('.faq-a');
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach((o) => {
        o.classList.remove('open');
        o.querySelector('.faq-a').classList.add('hidden');
      });
      if (!isOpen) {
        item.classList.add('open');
        answer.classList.remove('hidden');
      }
    });
  });

  /* ---------------- Back to top ---------------- */
  const backToTop = document.getElementById('backToTop');
  if (backToTop) {
    const toggleTop = () => backToTop.classList.toggle('show', window.scrollY > 400);
    window.addEventListener('scroll', toggleTop, { passive: true });
    toggleTop();
    backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  /* ---------------- Course deadline countdown ---------------- */
  function deadlineLabel(secs) {
    if (secs <= 0) return 'Closed';
    const days = Math.floor(secs / 86400);
    const hours = Math.floor((secs % 86400) / 3600);
    const mins = Math.floor((secs % 3600) / 60);
    if (days >= 1) return days + ' day' + (days > 1 ? 's' : '') + ' left';
    if (hours >= 1) return hours + 'h left';
    return Math.max(1, mins) + 'm left';
  }

  function closeCard(card) {
    const apply = card.querySelector('.js-apply');
    if (apply) apply.remove();
    const details = card.querySelector('.js-details');
    if (details) {
      details.classList.remove('flex-1');
      details.classList.add('w-full');
      details.textContent = 'View Details';
    }
    const status = card.querySelector('.card-status');
    if (status) {
      status.className = 'card-status inline-flex items-center gap-1.5 text-xs font-semibold text-red-500';
      status.textContent = 'Registration closed';
    }
    const badge = card.querySelector('.deadline-badge');
    if (badge) {
      badge.classList.remove('bg-red-600/90');
      badge.classList.add('bg-slate-700/90');
    }
    const txt = card.querySelector('.deadline-text');
    if (txt) txt.textContent = 'Closed';
    card.removeAttribute('data-card-deadline');
  }

  function tickDeadlines() {
    const cards = document.querySelectorAll('[data-card-deadline]');
    if (!cards.length) return;
    const now = Math.floor(Date.now() / 1000);
    cards.forEach((card) => {
      const end = parseInt(card.dataset.cardDeadline, 10);
      const left = end - now;
      if (left <= 0) {
        closeCard(card);
      } else {
        const txt = card.querySelector('.deadline-text');
        if (txt) txt.textContent = deadlineLabel(left);
      }
    });
  }
  tickDeadlines();
  setInterval(tickDeadlines, 30000);

  /* ---------------- AJAX helper ---------------- */
  async function postForm(url, formData) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': SIZ.csrf },
      body: formData,
    });
    return res.json();
  }

  function setMsg(el, text, ok) {
    if (!el) return;
    el.textContent = text;
    el.className = (el.className.replace(/text-(emerald|red)-\d+/g, '')) +
      ' ' + (ok ? 'text-emerald-500' : 'text-red-500');
  }

  /* ---------------- Newsletter form ---------------- */
  const nl = document.getElementById('newsletterForm');
  if (nl) {
    const msg = document.getElementById('newsletterMsg');
    nl.addEventListener('submit', async (e) => {
      e.preventDefault();
      try {
        const data = await postForm(SIZ.base + '/api/newsletter.php', new FormData(nl));
        setMsg(msg, data.message, data.success);
        if (data.success) nl.reset();
      } catch (err) {
        setMsg(msg, 'Subscription failed. Try again.', false);
      }
    });
  }
})();
