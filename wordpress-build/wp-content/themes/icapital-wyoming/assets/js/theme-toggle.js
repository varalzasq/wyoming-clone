// Dark mode toggle — no framework required
(function() {
  'use strict';

  var STORAGE_KEY = 'wyllc_theme';

  function applyTheme(theme) {
    if (theme === 'dark') {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
    localStorage.setItem(STORAGE_KEY, theme);
    // Update all toggle button icons
    document.querySelectorAll('[data-theme-toggle]').forEach(function(btn) {
      btn.querySelector('.icon-sun').style.display  = theme === 'dark'  ? 'inline' : 'none';
      btn.querySelector('.icon-moon').style.display = theme === 'light' ? 'inline' : 'none';
    });
  }

  // Apply saved theme immediately (prevents flash)
  var saved = localStorage.getItem(STORAGE_KEY) || 'dark';
  applyTheme(saved);

  // Wire up toggle buttons after DOM is ready
  document.addEventListener('DOMContentLoaded', function() {
    applyTheme(localStorage.getItem(STORAGE_KEY) || 'dark');

    document.querySelectorAll('[data-theme-toggle]').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        applyTheme(current === 'dark' ? 'light' : 'dark');
      });
    });

    // Mobile nav toggle
    var mobileBtn  = document.getElementById('mobile-menu-btn');
    var mobileMenu = document.getElementById('mobile-nav');
    if (mobileBtn && mobileMenu) {
      mobileBtn.addEventListener('click', function() {
        mobileMenu.classList.toggle('is-open');
        var isOpen = mobileMenu.classList.contains('is-open');
        mobileBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
    }

    // Dropdown toggles
    document.querySelectorAll('.dropdown__toggle').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var parent = btn.closest('.dropdown');
        // Close all other dropdowns
        document.querySelectorAll('.dropdown.is-open').forEach(function(d) {
          if (d !== parent) d.classList.remove('is-open');
        });
        parent.classList.toggle('is-open');
      });
    });

    // Click outside to close dropdowns
    document.addEventListener('click', function() {
      document.querySelectorAll('.dropdown.is-open').forEach(function(d) {
        d.classList.remove('is-open');
      });
    });
  });
})();
