/**
 * RUPE CDMX — Menú responsive Tutor/Admin
 */
(function (global) {
  'use strict';

  function isMobileNav() {
    return window.matchMedia('(max-width: 991.98px)').matches;
  }

  function ensureBackdrop() {
    var bd = document.getElementById('rupeNavBackdrop');
    if (!bd) {
      bd = document.createElement('div');
      bd.id = 'rupeNavBackdrop';
      bd.setAttribute('aria-hidden', 'true');
      document.body.appendChild(bd);
      bd.addEventListener('click', function () {
        closeAllMenus();
      });
    }
    return bd;
  }

  function closeCollapse(collapseEl, toggler) {
    if (!collapseEl) return;
    collapseEl.classList.remove('show');
    if (toggler) toggler.setAttribute('aria-expanded', 'false');
    if (global.bootstrap && bootstrap.Collapse) {
      try {
        var inst = bootstrap.Collapse.getInstance(collapseEl);
        if (inst) inst.hide();
      } catch (_) {}
    }
  }

  function closeAllMenus() {
    document.querySelectorAll('#rupeNavCollapse, #navbarAdminContent').forEach(function (c) {
      var tid = c.id === 'rupeNavCollapse' ? 'rupeNavToggler' : null;
      var toggler = tid
        ? document.getElementById(tid)
        : document.querySelector('[data-bs-target="#' + c.id + '"]');
      closeCollapse(c, toggler);
    });
    var bd = document.getElementById('rupeNavBackdrop');
    if (bd) {
      bd.classList.remove('show');
      bd.setAttribute('aria-hidden', 'true');
    }
    document.body.classList.remove('rupe-nav-open');
  }

  function syncBackdrop() {
    if (!isMobileNav()) {
      closeAllMenus();
      return;
    }
    var open = document.querySelector('#rupeNavCollapse.show, #navbarAdminContent.show');
    var bd = ensureBackdrop();
    if (open) {
      bd.classList.add('show');
      bd.setAttribute('aria-hidden', 'false');
      document.body.classList.add('rupe-nav-open');
    } else {
      bd.classList.remove('show');
      bd.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('rupe-nav-open');
    }
  }

  function bindTutor() {
    var toggler = document.getElementById('rupeNavToggler');
    var collapse = document.getElementById('rupeNavCollapse');
    if (!collapse) return;

    collapse.addEventListener('shown.bs.collapse', syncBackdrop);
    collapse.addEventListener('hidden.bs.collapse', syncBackdrop);

    if (toggler && !toggler._rupeBound) {
      toggler._rupeBound = true;
      toggler.addEventListener('click', function (e) {
        if (!isMobileNav()) return;
        // Fallback manual si Bootstrap no actúa
        setTimeout(function () {
          if (!collapse.classList.contains('show') && toggler.getAttribute('aria-expanded') === 'true') {
            collapse.classList.add('show');
          } else if (collapse.classList.contains('show') && toggler.getAttribute('aria-expanded') === 'false') {
            // ok
          } else if (!window.bootstrap) {
            var willOpen = !collapse.classList.contains('show');
            collapse.classList.toggle('show', willOpen);
            toggler.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
          }
          var expanded = collapse.classList.contains('show');
          toggler.setAttribute('aria-expanded', expanded ? 'true' : 'false');
          syncBackdrop();
        }, 40);
      });
    }

    collapse.querySelectorAll('.rupe-nav-link, .nav-modulo, a, button').forEach(function (el) {
      el.addEventListener('click', function () {
        if (!isMobileNav()) return;
        setTimeout(function () {
          closeCollapse(collapse, toggler);
          syncBackdrop();
        }, 100);
      });
    });

    // Dropdown opciones: no cerrar por backdrop accidental
    var userBtn = document.getElementById('btnUserDropdown');
    if (userBtn && !userBtn._rupeBound) {
      userBtn._rupeBound = true;
      userBtn.addEventListener('click', function (e) {
        e.stopPropagation();
      });
    }
  }

  function bindAdmin() {
    var collapse = document.getElementById('navbarAdminContent');
    if (!collapse) return;
    collapse.addEventListener('shown.bs.collapse', syncBackdrop);
    collapse.addEventListener('hidden.bs.collapse', syncBackdrop);
    collapse.querySelectorAll('a, button').forEach(function (el) {
      el.addEventListener('click', function () {
        if (!isMobileNav()) return;
        setTimeout(function () {
          var toggler = document.querySelector('[data-bs-target="#navbarAdminContent"]');
          closeCollapse(collapse, toggler);
          syncBackdrop();
        }, 100);
      });
    });
  }

  function boot() {
    ensureBackdrop();
    bindTutor();
    bindAdmin();
    window.addEventListener('resize', function () {
      if (!isMobileNav()) closeAllMenus();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeAllMenus();
    });
  }

  // API legacy
  global.RupeMobileNav = function (opts) {
    this.close = closeAllMenus;
    this.opts = opts || {};
    try { bindTutor(); bindAdmin(); } catch (_) {}
  };
  global.rupeNavCloseAll = closeAllMenus;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})(window);
