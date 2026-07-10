/* Theme + toasts + multi-step form helpers */
(function () {
  const root = document.documentElement;
  const stored = localStorage.getItem("siz-theme");
  if (stored === "dark" || (!stored && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
    root.classList.add("dark");
  }

  window.toggleTheme = function () {
    root.classList.toggle("dark");
    localStorage.setItem("siz-theme", root.classList.contains("dark") ? "dark" : "light");
  };

  window.copyText = async function (text, btn) {
    try {
      await navigator.clipboard.writeText(text);
      if (btn) {
        const old = btn.textContent;
        btn.textContent = "Copied!";
        setTimeout(() => (btn.textContent = old), 1600);
      }
    } catch (e) {
      prompt("Copy this link:", text);
    }
  };

  window.showToast = function (message, type) {
    let box = document.querySelector(".toast-container");
    if (!box) {
      box = document.createElement("div");
      box.className = "toast-container";
      document.body.appendChild(box);
    }
    const el = document.createElement("div");
    el.className =
      "glass rounded-xl px-4 py-3 text-sm font-medium shadow-lg animate-fade-up " +
      (type === "error" ? "border-red-400" : "border-teal-400");
    el.textContent = message;
    box.appendChild(el);
    setTimeout(() => el.remove(), 3500);
  };
})();
