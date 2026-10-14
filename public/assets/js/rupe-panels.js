/**
 * RUPE CDMX — Navegación Tutor/Admin (recarga por módulo, menú parejo)
 */
'use strict';

(function (global) {
  var TUTOR_MAP = {
    analisis: '/tutor/analitico',
    analitico: '/tutor/analitico',
    registro: '/tutor/registro',
    crud: '/tutor/crud',
    'ficha-pdf': '/tutor/ficha-pdf',
    ficha: '/tutor/ficha-pdf',
    'gps-qr': '/tutor/gpsqr',
    gps: '/tutor/gpsqr',
    gpsqr: '/tutor/gpsqr',
    'gps_qr': '/tutor/gpsqr',
    seguimiento: '/tutor/seguimiento',
    estatus: '/tutor/estatus',
    albergues: '/tutor/albergues',
    perfil: '/tutor/perfil',
    miperfil: '/tutor/perfil',
    configuracion: '/tutor/cuenta',
    cuenta: '/tutor/cuenta',
    ayuda: '/tutor/ayuda'
  };

  var NAMES = {
    analisis: 'Panel Analítico',
    analitico: 'Panel Analítico',
    registro: 'Registro',
    crud: 'Tabla CRUD',
    'ficha-pdf': 'Ficha + QR',
    'gps-qr': 'GPS / QR',
    seguimiento: 'Trazabilidad',
    estatus: 'Estatus',
    albergues: 'Albergues',
    perfil: 'Mi Perfil',
    configuracion: 'Configuración',
    ayuda: 'Ayuda'
  };

  var NUMS = {
    analisis: '1',
    registro: '2',
    crud: '3',
    'ficha-pdf': '4',
    'gps-qr': '5',
    seguimiento: '6',
    estatus: '7',
    albergues: '8'
  };

  function pathModulo() {
    var p = (location.pathname || '').replace(/\/+$/, '') || '/';
    var m = p.match(/\/tutor\/([^\/\?\#]+)/i);
    if (!m) {
      if (p === '/tutor' || p === '/tutor/') return 'analisis';
      return '';
    }
    var key = String(m[1]).toLowerCase();
    var aliases = {
      analitico: 'analisis',
      analisis: 'analisis',
      registro: 'registro',
      crud: 'crud',
      'ficha-pdf': 'ficha-pdf',
      ficha: 'ficha-pdf',
      gpsqr: 'gps-qr',
      gps: 'gps-qr',
      'gps-qr': 'gps-qr',
      seguimiento: 'seguimiento',
      estatus: 'estatus',
      albergues: 'albergues',
      perfil: 'perfil',
      miperfil: 'perfil',
      cuenta: 'configuracion',
      configuracion: 'configuracion',
      ayuda: 'ayuda'
    };
    return aliases[key] || key;
  }

  function cerrarMenuMovil() {
    var collapse = document.getElementById('rupeNavCollapse');
    var toggler = document.getElementById('rupeNavToggler');
    if (collapse) {
      collapse.classList.remove('show');
      if (global.bootstrap && bootstrap.Collapse) {
        try {
          var inst = bootstrap.Collapse.getInstance(collapse);
          if (inst) inst.hide();
        } catch (_) {}
      }
    }
    if (toggler) toggler.setAttribute('aria-expanded', 'false');
    var bd = document.getElementById('rupeNavBackdrop');
    if (bd) {
      bd.classList.remove('show');
      bd.setAttribute('aria-hidden', 'true');
    }
    document.body.classList.remove('rupe-nav-open');
  }

  function activarSeccionLocal(clave) {
    document.querySelectorAll('.nav-modulo, .rupe-nav-link').forEach(function (n) {
      n.classList.remove('active');
    });
    var activeBtn = document.querySelector('.nav-modulo[data-modulo="' + clave + '"], .rupe-nav-link[data-modulo="' + clave + '"]');
    if (activeBtn) activeBtn.classList.add('active');
    // enlaces por href
    document.querySelectorAll('a.rupe-nav-link[href]').forEach(function (a) {
      var href = (a.getAttribute('href') || '').replace(/\/+$/, '');
      var dest = (TUTOR_MAP[clave] || '').replace(/\/+$/, '');
      if (dest && href === dest) a.classList.add('active');
    });

    document.querySelectorAll('.modulo-seccion').forEach(function (s) {
      s.classList.remove('activo');
    });
    var sec = document.getElementById('modulo-' + clave);
    if (!sec && clave === 'analisis') sec = document.getElementById('modulo-analisis');
    if (sec) sec.classList.add('activo');

    var numEl = document.getElementById('moduloActualNum');
    var txtEl = document.getElementById('moduloActualTexto');
    if (numEl) numEl.textContent = NUMS[clave] || '';
    if (txtEl) txtEl.textContent = NAMES[clave] || clave;
  }

  /** Siempre recarga al cambiar de módulo (menú parejo en todas las vistas) */
  function irModuloTutor(clave, btn, ev) {
    if (!clave) return false;
    if (ev) {
      try {
        ev.preventDefault();
        ev.stopPropagation();
      } catch (_) {}
    }
    clave = String(clave).toLowerCase().trim();
    var dest = TUTOR_MAP[clave] || ('/tutor/' + encodeURIComponent(clave));
    var actual = pathModulo();

    cerrarMenuMovil();

    // Misma ruta: solo activar sección y marcar activo
    if (actual === clave || (clave === 'analisis' && (location.pathname === '/tutor' || location.pathname === '/tutor/'))) {
      activarSeccionLocal(clave);
      try { window.scrollTo({ top: 0, behavior: 'smooth' }); } catch (_) { window.scrollTo(0, 0); }
      return true;
    }

    try {
      window.location.assign(dest);
    } catch (_) {
      window.location.href = dest;
    }
    return true;
  }

  global.activarModuloTutor = irModuloTutor;
  global.activarModulo = function (clave, btn, ev) {
    return irModuloTutor(clave, btn, ev);
  };
  global.mostrarModuloPerfil = function (e) { return irModuloTutor('perfil', null, e); };
  global.mostrarModuloConfiguracion = function (e) { return irModuloTutor('configuracion', null, e); };
  global.mostrarModuloAyuda = function (e) { return irModuloTutor('ayuda', null, e); };

  function bindTutorMenu() {
    // Botones data-modulo
    document.querySelectorAll('.nav-modulo[data-modulo], .rupe-nav-link[data-modulo]').forEach(function (btn) {
      btn.style.cursor = 'pointer';
      btn.style.pointerEvents = 'auto';
      if (btn._rupeNavBound) return;
      btn._rupeNavBound = true;
      var clave = btn.getAttribute('data-modulo');
      if (clave && TUTOR_MAP[clave]) {
        btn.setAttribute('data-url', TUTOR_MAP[clave]);
      }
      btn.addEventListener('click', function (e) {
        irModuloTutor(btn.getAttribute('data-modulo'), btn, e);
      });
    });

    // Enlaces <a class="rupe-nav-link" href="/tutor/...">
    document.querySelectorAll('a.rupe-nav-link[href^="/tutor"]').forEach(function (a) {
      a.style.cursor = 'pointer';
      a.style.pointerEvents = 'auto';
      if (a._rupeNavBound) return;
      a._rupeNavBound = true;
      a.addEventListener('click', function (e) {
        var href = a.getAttribute('href') || '';
        if (!href) return;
        e.preventDefault();
        cerrarMenuMovil();
        try { window.location.assign(href); } catch (_) { window.location.href = href; }
      });
    });

    document.querySelectorAll('.user-dropdown .dropdown-item[href]').forEach(function (a) {
      if (a._rupeDropBound) return;
      a._rupeDropBound = true;
      a.addEventListener('click', function (e) {
        var href = a.getAttribute('href') || '';
        if (!href || href === '#') return;
        if (a.getAttribute('data-bs-toggle') === 'modal') return;
        e.preventDefault();
        try { window.location.assign(href); } catch (_) { window.location.href = href; }
      });
    });

    var actual = pathModulo();
    if (actual) activarSeccionLocal(actual);
  }

  function bindAdminMenu() {
    document.querySelectorAll('a.nav-modulo[href], #navbarAdminContent a[href^="/admin"]').forEach(function (a) {
      a.style.cursor = 'pointer';
      a.style.pointerEvents = 'auto';
      if (a._rupeAdminBound) return;
      a._rupeAdminBound = true;
      a.addEventListener('click', function (e) {
        var href = a.getAttribute('href');
        if (!href || href === '#') return;
        e.preventDefault();
        try { window.location.assign(href); } catch (_) { window.location.href = href; }
      });
    });
  }

  function boot() {
    bindTutorMenu();
    bindAdminMenu();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})(window);
