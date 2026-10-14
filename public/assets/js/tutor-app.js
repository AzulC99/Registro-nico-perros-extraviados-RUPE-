/**
 * RUPE CDMX — Tutor App ES6
 * public/assets/js/tutor-app.js
 * Backend real: validaciones, toasts, API REST (sin flash)
 */
'use strict';

(function (global) {

  // Mapa módulo → URL real (cada página tutor)
  const TUTOR_MODULO_URL = {
    analisis: '/tutor/analisis',
    analitico: '/tutor/analisis',
    registro: '/tutor/registro',
    crud: '/tutor/crud',
    'ficha-pdf': '/tutor/ficha-pdf',
    ficha: '/tutor/ficha-pdf',
    'gps-qr': '/tutor/gps-qr',
    gps: '/tutor/gps-qr',
    gpsqr: '/tutor/gps-qr',
    seguimiento: '/tutor/seguimiento',
    estatus: '/tutor/estatus',
    albergues: '/tutor/albergues',
    perfil: '/tutor/perfil',
    miperfil: '/tutor/perfil',
    configuracion: '/tutor/configuracion',
    cuenta: '/tutor/configuracion',
    ayuda: '/tutor/ayuda',
  };
  // URL path → id de sección y clave módulo
  const TUTOR_PATH_MODULO = {
    '/tutor': 'analisis',
    '/tutor/analisis': 'analisis',
    '/tutor/analitico': 'analisis',
    '/tutor/registro': 'registro',
    '/tutor/crud': 'crud',
    '/tutor/ficha-pdf': 'ficha-pdf',
    '/tutor/ficha': 'ficha-pdf',
    '/tutor/gps-qr': 'gps-qr',
    '/tutor/gpsqr': 'gps-qr',
    '/tutor/gps': 'gps-qr',
    '/tutor/seguimiento': 'seguimiento',
    '/tutor/estatus': 'estatus',
    '/tutor/albergues': 'albergues',
    '/tutor/perfil': 'perfil',
    '/tutor/miperfil': 'perfil',
    '/tutor/configuracion': 'configuracion',
    '/tutor/cuenta': 'configuracion',
    '/tutor/ayuda': 'ayuda',
  };
  const TUTOR_MODULO_SECCION = {
    analisis: 'modulo-analisis',
    analitico: 'modulo-analisis',
    registro: 'modulo-registro',
    crud: 'modulo-crud',
    'ficha-pdf': 'modulo-ficha-pdf',
    ficha: 'modulo-ficha-pdf',
    'gps-qr': 'modulo-gps-qr',
    gps: 'modulo-gps-qr',
    gpsqr: 'modulo-gps-qr',
    seguimiento: 'modulo-seguimiento',
    estatus: 'modulo-estatus',
    albergues: 'modulo-albergues',
    perfil: 'modulo-perfil',
    miperfil: 'modulo-perfil',
    configuracion: 'modulo-configuracion',
    cuenta: 'modulo-configuracion',
    ayuda: 'modulo-ayuda',
  };
  global.TUTOR_MODULO_URL = TUTOR_MODULO_URL;

  function pathTutorActual() {
    return (window.location.pathname || '').replace(/\/+$/, '') || '/';
  }

  function moduloDesdePath(path) {
    path = String(path || pathTutorActual());
    if (TUTOR_PATH_MODULO[path]) return TUTOR_PATH_MODULO[path];
    // /tutor/xxx
    const m = path.match(/\/tutor\/([^\/?#]+)/);
    if (m && m[1]) {
      const k = String(m[1]).toLowerCase();
      if (TUTOR_PATH_MODULO['/tutor/' + k]) return TUTOR_PATH_MODULO['/tutor/' + k];
      if (TUTOR_MODULO_URL[k]) return k;
    }
    return 'analisis';
  }

  function activarSeccionTutor(clave) {
    clave = String(clave || '').toLowerCase();
    const secId = TUTOR_MODULO_SECCION[clave] || ('modulo-' + clave);
    document.querySelectorAll('.modulo-seccion').forEach(function (sec) {
      sec.classList.remove('activo');
    });
    const seccion = document.getElementById(secId);
    if (seccion) seccion.classList.add('activo');
    document.querySelectorAll('.nav-modulo').forEach(function (nav) {
      const m = nav.getAttribute('data-modulo');
      nav.classList.toggle('active', m === clave || (clave === 'configuracion' && m === 'cuenta'));
    });
    try {
      if (typeof global.actualizarBotonModulos === 'function') global.actualizarBotonModulos(clave);
    } catch (_) {}
    return !!seccion;
  }
  global.activarSeccionTutor = activarSeccionTutor;
  global.moduloDesdePath = moduloDesdePath;

  global.navegarModuloTutor = function (mod, folio) {
    const key = String(mod || '').toLowerCase();
    let url = TUTOR_MODULO_URL[key] || '/tutor';
    if (folio) {
      url += (url.indexOf('?') >= 0 ? '&' : '?') + 'folio=' + encodeURIComponent(folio);
    }
    const path = pathTutorActual();
    const destPath = url.split('?')[0].replace(/\/+$/, '');
    // Misma página: solo activar sección (sin recarga completa si ya está)
    if (path === destPath) {
      activarSeccionTutor(key);
      try { window.scrollTo({ top: 0, behavior: 'smooth' }); } catch (_) {}
      return;
    }
    window.location.href = url;
  };

  // Clics de menú → URL de cada módulo tutor
  document.addEventListener('click', function (ev) {
    const btn = ev.target.closest('.nav-modulo[data-modulo]');
    if (!btn) return;
    const mod = btn.getAttribute('data-modulo');
    if (!mod || !TUTOR_MODULO_URL[mod]) return;
    ev.preventDefault();
    ev.stopPropagation();
    const folio = (global.state && global.state.folioSeleccionado) || '';
    global.navegarModuloTutor(mod, folio || undefined);
  }, true);

  // Dropdown perfil / configuración / ayuda → URL real
  global.mostrarModuloPerfil = function (e) {
    if (e && e.preventDefault) e.preventDefault();
    global.navegarModuloTutor('perfil');
  };
  global.mostrarModuloConfiguracion = function (e) {
    if (e && e.preventDefault) e.preventDefault();
    global.navegarModuloTutor('configuracion');
  };
  global.mostrarModuloAyuda = function (e) {
    if (e && e.preventDefault) e.preventDefault();
    global.navegarModuloTutor('ayuda');
  };
  global.irAModuloDesdeAyuda = function (clave) {
    global.navegarModuloTutor(clave || 'ayuda');
  };

  // Al cargar / recargar: activar la sección según la URL
  function bootSeccionDesdeUrl() {
    const clave = moduloDesdePath();
    activarSeccionTutor(clave);
    // Hooks de inicialización por módulo
    try {
      if (clave === 'perfil' && typeof global.renderizarPerfilTutor === 'function') global.renderizarPerfilTutor();
      if (clave === 'configuracion') {
        if (typeof global.renderizarPreferencias === 'function') global.renderizarPreferencias();
        if (typeof global.renderizarConfiguracion === 'function') global.renderizarConfiguracion();
      }
      if (clave === 'ayuda') {
        const el = document.getElementById('ayudaCorreoRespuesta');
        const correo = (global.state && global.state.tutorActual && global.state.tutorActual.correo) ||
          (global.RUPE_INIT && global.RUPE_INIT.user && global.RUPE_INIT.user.correo) || '';
        if (el && correo) el.textContent = correo;
      }
    } catch (err) {
      console.warn('[tutor] boot seccion', err);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootSeccionDesdeUrl);
  } else {
    bootSeccionDesdeUrl();
  }
  global.addEventListener('load', function () {
    setTimeout(bootSeccionDesdeUrl, 30);
  });

  /** Estado compartido con la vista (window.state) */
  function ensureState() {
    if (!global.state || typeof global.state !== 'object') {
      global.state = {
        tutorActual: {},
        preferencias: {},
        registros: [],
        albergues: [],
        folioSeleccionado: '',
        statsBackend: {},
        charts: {},
      };
    }
    if (!Array.isArray(global.state.registros)) global.state.registros = [];
    if (!Array.isArray(global.state.albergues)) global.state.albergues = [];
    return global.state;
  }

  const api = {
    async request(method, path, body) {
      const csrf = global.__CSRF__ || '';
      const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-Token': csrf,
      };
      let payload;
      if (body && !(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
        payload = JSON.stringify({ ...body, _csrf: csrf });
      } else if (body instanceof FormData) {
        if (csrf && !body.has('_csrf')) body.append('_csrf', csrf);
        payload = body;
      } else {
        payload = body;
      }
      const res = await fetch(path, {
        method,
        headers,
        body: method === 'GET' || method === 'HEAD' ? undefined : payload,
        credentials: 'same-origin',
      });
      const data = await res.json().catch(() => ({}));
      if (data?.data?.csrf) global.__CSRF__ = data.data.csrf;
      if (!res.ok || data.success === false) {
        const err = new Error(data.message || 'Error de servidor');
        err.status = res.status;
        err.errors = data.errors || '';
        throw err;
      }
      return data;
    },
    get: (p) => api.request('GET', p),
    post: (p, b) => api.request('POST', p, b),
    put: (p, b) => api.request('PUT', p, b),
    patch: (p, b) => api.request('PATCH', p, b),
  };

  function toast(msg, type = 'success') {
    const t = type === 'error' ? 'danger' : (type || 'success');
    if (typeof global.rupeToast === 'function') {
      try { global.rupeToast(msg, t); return; } catch (_) {}
    }
    if (typeof global.mostrarToastTutor === 'function') {
      try {
        global.mostrarToastTutor(msg, t);
        return;
      } catch (_) {}
    }
    let el = document.getElementById('rupeTutorToast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'rupeTutorToast';
      el.style.cssText =
        'position:fixed;bottom:24px;right:24px;z-index:99999;min-width:260px;max-width:380px;padding:14px 18px;border-radius:12px;color:#fff;font-size:14px;box-shadow:0 8px 24px rgba(0,0,0,.18);opacity:0;transition:opacity .25s;';
      document.body.appendChild(el);
    }
    const colors = { success: '#198754', error: '#dc3545', warning: '#e8a33d', info: '#6B1D2F' };
    el.style.background = colors[type] || colors.info;
    el.textContent = msg;
    el.style.opacity = '1';
    clearTimeout(el._t);
    el._t = setTimeout(() => {
      el.style.opacity = '0';
    }, 3800);
  }

  function fieldErrors(form, errors) {
    if (!form || !errors) return;
    form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback.rupe-api').forEach((el) => el.remove());
    Object.entries(errors).forEach(([field, msgs]) => {
      const input =
        form.querySelector(`[name="${field}"]`) ||
        form.querySelector(`#${field}`) ||
        document.getElementById(field);
      if (!input) return;
      input.classList.add('is-invalid');
      const div = document.createElement('div');
      div.className = 'invalid-feedback rupe-api d-block';
      div.textContent = Array.isArray(msgs) ? msgs[0] : String(msgs);
      input.parentNode.appendChild(div);
    });
  }

  function valRequired(obj, keys) {
    const errors = {};
    keys.forEach((k) => {
      const v = obj[k];
      if (v === undefined || v === '' || String(v).trim() === '') {
        errors[k] = ['Campo obligatorio'];
      }
    });
    return errors;
  }


  async function loadUsuarioSesion() {
    try {
      const data = await api.get('/api/tutor/perfil');
      const user = data.data?.user || {};
      const nombre = user.nombre || user.correo || 'Tutor';
      const tel = user.telefono || '';
      const correo = user.correo || '';
      const priv = !!user.acepta_privacidad;

      // UI header (botón de opciones)
      if (typeof global.rupeApplyUserName === 'function') {
        global.rupeApplyUserName(nombre, user.rol);
      } else {
        const elName = document.getElementById('tutorUserName');
        if (elName) elName.textContent = nombre;
        const elHeader = document.getElementById('tutorUserHeader');
        if (elHeader) elHeader.textContent = nombre;
      }

      // state local del panel
      if (global.state) {
        global.state.tutorActual = {
          ...(global.state.tutorActual || {}),
          id: user.id_usuario,
          nombre,
          telefono: tel,
          correo,
          aceptaPrivacidad: priv,
        };
      }

      // Prefill formulario de registro (contacto tutor)
      const setVal = (id, v) => {
        const el = document.getElementById(id);
        if (el && v) el.value = v;
      };
      setVal('regTutorNombre', nombre);
      setVal('regTutorTel', tel);
      setVal('regTutorEmail', correo);
      const auto = document.getElementById('regTutorAuto');
      if (auto) {
        auto.textContent = 'Datos de contacto tomados de tu cuenta: ' + nombre;
      }

      // Perfil module fields if present
      setVal('perfilInputNombre', nombre);
      setVal('perfilInputTelefono', tel);
      setVal('perfilInputCorreo', correo);

      if (typeof global.renderizarPerfilTutor === 'function') {
        try { global.renderizarPerfilTutor(); } catch (_) {}
      }
      return user;
    } catch (e) {
      console.warn('[RUPE] loadUsuarioSesion', e);
      if (e.status === 401) {
        toast('Sesión expirada. Inicia sesión de nuevo.', 'error');
        setTimeout(() => { window.location.href = '/'; }, 1200);
      }
      return '';
    }
  }

  /** Cargar mascotas del tutor desde BD y refrescar módulos */
  async function syncMascotas() {
    const st = ensureState();
    const data = await api.get('/api/tutor/mascotas');
    // Aceptar varias formas de respuesta
    const payload = (data && data.data) ? data.data : (data || {});
    const list = Array.isArray(payload.mascotas)
      ? payload.mascotas
      : (Array.isArray(payload) ? payload : []);
    const stats = payload.stats || {};
    st.registros = list.map(mapMascotaToState);
    st.statsBackend = stats;
    if (st.registros.length) {
      const still = st.registros.find((r) => r.folio === st.folioSeleccionado);
      st.folioSeleccionado = still ? still.folio : st.registros[0].folio;
    } else {
      st.folioSeleccionado = '';
    }
    // Alias por si la vista usa var state
    global.state = st;
    await refreshAllModules();
    refreshAnaliticoFromDB();
    if (typeof global.renderizarTablaCrud === 'function') {
      try { global.renderizarTablaCrud(); } catch (_) {}
    } else {
      try { renderTablaCrudDB(); } catch (_) {}
    }
    return { list: st.registros, stats };
  }

  function mapMascotaToState(m) {
    const sexoRaw = m.sexo || 'desconocido';
    const sexoLabel =
      sexoRaw === 'macho' ? 'Macho' : sexoRaw === 'hembra' ? 'Hembra' : sexoRaw;
    return {
      id: m.id_mascota,
      folio: m.folio,
      nombre: m.nombre,
      raza: (function(){ var x = m.raza_nombre || m.raza || ''; x = String(x); return (!x || x === 'null' || x === 'undefined') ? '—' : x; })(),
      id_raza: m.id_raza,
      alcaldia: m.alcaldia_nombre || m.alcaldia || '',
      id_alcaldia: m.id_alcaldia,
      fecha: m.fecha_extravio || (m.created_at ? String(m.created_at).slice(0, 10) : ''),
      estatus: m.estatus || 'En Búsqueda',
      senas: m.senas_particulares || '',
      senas_particulares: m.senas_particulares || '',
      especie: m.especie || 'perro',
      sexo: sexoLabel,
      color: m.color || '',
      tamano: m.tamano || '',
      foto: m.foto || m.foto_url || '/assets/img/placeholder-perro.svg',
      foto_url: m.foto || m.foto_url || '/assets/img/placeholder-perro.svg',
      edad_aprox: m.edad_aprox || m.edad || '',
      collar: m.collar || '',
      color: m.color || '',
      senas: m.senas || m.senas_particulares || '',
      senas_particulares: m.senas_particulares || m.senas || '',
      colonia: (m.colonia && String(m.colonia) !== 'null') ? String(m.colonia) : '',
      direccion_ref: (m.direccion_ref && String(m.direccion_ref) !== 'null') ? String(m.direccion_ref) : '',
      calle: (m.calle && String(m.calle) !== 'null') ? String(m.calle) : '',
      referencia: (m.referencia && String(m.referencia) !== 'null') ? String(m.referencia) : '',
      latitud: (m.latitud != null && m.latitud !== '' && String(m.latitud) !== 'null') ? m.latitud : '',
      longitud: (m.longitud != null && m.longitud !== '' && String(m.longitud) !== 'null') ? m.longitud : '',
      qr_token: m.qr_token || '',
      bitacora: (m.bitacora || m.notas || 'Registro en RUPE CDMX'),
      tutorNombre: (global.state && global.state.tutorActual && global.state.tutorActual.nombre) || '',
      tutorTel: (global.state && global.state.tutorActual && global.state.tutorActual.telefono) || '',
      tutorEmail: (global.state && global.state.tutorActual && global.state.tutorActual.correo) || '',
      avistamientos: Array.isArray(m.avistamientos) ? m.avistamientos : [],
      id_albergue: m.id_albergue || '',
      albergue_nombre: m.albergue_nombre || '',
    };
  }

  async function loadAlberguesDB() {
    try {
      // Módulo Albergues: usar el listado completo del tutor (tipo, activo, ocupación, id_alcaldia)
      // para no pisar el directorio con el catálogo público (que perdía tipo/estado y rompía los filtros).
      if (global.RUPE_ALBERGUES_FULL) {
        const resFull = await api.get('/api/tutor/albergues');
        const full = Array.isArray(resFull) ? resFull : (resFull.data || []);
        if (global.state) {
          global.state.albergues = full.map((a) => {
            const id = parseInt(a.id_albergue != null ? a.id_albergue : a.id, 10) || 0;
            return {
              id: id,
              id_albergue: id,
              nombre: a.nombre || '',
              tipo: a.tipo || 'Albergue',
              alcaldia: a.alcaldia_nombre || a.alcaldia || '',
              alcaldia_nombre: a.alcaldia_nombre || a.alcaldia || '',
              id_alcaldia: parseInt(a.id_alcaldia, 10) || 0,
              direccion: a.direccion || '',
              telefono: a.telefono || '',
              correo: a.correo || a.email || '',
              email: a.correo || a.email || '',
              capacidad: parseInt(a.capacidad, 10) || 0,
              ocupacion: parseInt(a.ocupacion, 10) || 0,
              activo: parseInt(a.activo, 10) === 0 ? 0 : 1,
              latitud: a.latitud,
              longitud: a.longitud,
            };
          }).filter((a) => a.id > 0);
        }
        return full;
      }
      const res = await api.get('/api/catalogos/albergues');
      const list = res.data || [];
      if (global.state) {
        global.state.albergues = list.map((a) => ({
          id: a.id_albergue,
          nombre: a.nombre,
          tipo: 'Albergue',
          alcaldia: a.alcaldia_nombre || '',
          direccion: a.direccion || '',
          telefono: a.telefono || '',
          capacidad: a.capacidad || 0,
          latitud: a.latitud,
          longitud: a.longitud,
        }));
      }
      return list;
    } catch (e) {
      console.warn('[RUPE] albergues', e);
      return [];
    }
  }

  async function loadSeguimientosFor(idMascota) {
    if (!idMascota) return [];
    try {
      const res = await api.get(`/api/tutor/mascotas/${idMascota}`);
      const segs = res.data?.seguimientos || [];
      const mascota = res.data?.mascota;
      // Map seguimientos -> avistamientos UI
      const avis = segs.map((s) => ({
        id: s.id_seguimiento,
        fecha: s.created_at,
        descripcion: s.descripcion,
        tipo: s.tipo,
        latitud: s.latitud,
        longitud: s.longitud,
        usuario: s.usuario_nombre || '',
        foto: s.foto_url || '',
      }));
      if (global.state && mascota) {
        const reg = global.state.registros.find((r) => r.id === idMascota || r.folio === mascota.folio);
        if (reg) {
          reg.avistamientos = avis;
          reg.bitacora = segs[0]?.descripcion || reg.bitacora;
        }
      }
      return avis;
    } catch (e) {
      console.warn('[RUPE] seguimientos', e);
      return [];
    }
  }


  async function loadAyudaPublica() {
    try {
      const res = await api.get('/api/catalogos/ayuda');
      const list = res.data || [];
      global.__RUPE_FAQS__ = list;
      const cont =
        document.getElementById('tutorListaFaqs') ||
        document.querySelector('#modulo-ayuda .accordion') ||
        document.querySelector('#modulo-ayuda [data-faqs-container]');
      // If dedicated list exists, fill it
      const listEl = document.getElementById('tutorFaqsList');
      if (listEl) {
        listEl.innerHTML = list
          .map(
            (f, i) => `
          <div class="accordion-item" data-ayuda-item>
            <h2 class="accordion-header">
              <button class="accordion-button ${i ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#faqDb${f.id_faq}">
                ${String(f.pregunta || '').replace(/</g, '&lt;')}
              </button>
            </h2>
            <div id="faqDb${f.id_faq}" class="accordion-collapse collapse ${i ? '' : 'show'}" data-bs-parent="#tutorFaqsList">
              <div class="accordion-body">${String(f.respuesta || '').replace(/</g, '&lt;')}</div>
            </div>
          </div>`
          )
          .join('');
      }
      return list;
    } catch (e) {
      console.warn('[RUPE] faqs', e);
      return [];
    }
  }

  async function loadPreferenciasDB() {
    try {
      const res = await api.get('/api/tutor/preferencias');
      const p = res.data || {};
      const setChk = (id, v) => {
        const el = document.getElementById(id);
        if (el && el.type === 'checkbox') el.checked = !!Number(v);
      };
      const setVal = (id, v) => {
        const el = document.getElementById(id);
        if (el && v !== undefined && v !== '') el.value = v;
      };
      setChk('cfgNotifEmail', p.notif_email);
      setChk('cfgNotifSms', p.notif_sms);
      setChk('cfgNotifAvistamiento', p.notif_avistamiento);
      setChk('cfgNotifEstatus', p.notif_cambio_estatus);
      setChk('cfgNotifBoletin', p.notif_boletin);
      setVal('cfgIdioma', p.idioma || 'es');
      setVal('cfgAlcaldiaDefault', p.id_alcaldia_default || '');
      if (global.state) {
        global.state.preferencias = {
          ...(global.state.preferencias || {}),
          notifEmail: !!Number(p.notif_email),
          notifSms: !!Number(p.notif_sms),
          notifNuevoAvistamiento: !!Number(p.notif_avistamiento),
          notifCambioEstatus: !!Number(p.notif_cambio_estatus),
          notifBoletinRUPE: !!Number(p.notif_boletin),
          idioma: p.idioma || 'es',
          alcaldiaDefault: p.id_alcaldia_default || '',
        };
      }
      return p;
    } catch (e) {
      console.warn('[RUPE] preferencias', e);
      return '';
    }
  }

  function refreshAnaliticoFromDB() {
    if (!global.state) return;
    const regs = global.state.registros || [];
    const stats = global.state.statsBackend || {};
    const setT = (id, v) => {
      const el = document.getElementById(id);
      if (el) el.textContent = String(v ?? 0);
    };
    setT('kpiTotalRegistros', (typeof stats.total === 'number' ? stats.total : regs.length));
    setT('kpiEnBusqueda', (typeof stats['En Búsqueda'] === 'number' ? stats['En Búsqueda'] : regs.filter((r) => r.estatus === 'En Búsqueda').length));
    setT('kpiResguardo', (typeof stats['En Resguardo'] === 'number' ? stats['En Resguardo'] : regs.filter((r) => r.estatus === 'En Resguardo').length));
    setT('kpiLocalizado', (typeof stats['Localizado'] === 'number' ? stats['Localizado'] : regs.filter((r) => r.estatus === 'Localizado').length));
    if (typeof global.actualizarAnalisisMetricas === 'function') {
      try { global.actualizarAnalisisMetricas(); } catch (_) {}
    }
    if (typeof global.actualizarGraficasPanel === 'function') {
      try { global.actualizarGraficasPanel(); } catch (_) {}
    }
  }


  function renderTablaCrudDB(filtro) {
    const tbody = document.getElementById('tbodyCrud');
    if (!tbody) return;
    const st = global.state || {};
    const regs = Array.isArray(st.registros) ? st.registros : [];
    const q = String(filtro != null ? filtro : ((document.getElementById('inputBuscadorCrud') || {}).value || ''))
      .toLowerCase()
      .trim();
    const filtrados = !q
      ? regs
      : regs.filter(function (r) {
          return (
            String(r.folio || '').toLowerCase().indexOf(q) !== -1 ||
            String(r.nombre || '').toLowerCase().indexOf(q) !== -1
          );
        });

    if (!filtrados.length) {
      tbody.innerHTML =
        '<tr><td colspan="8" class="text-center text-muted py-5">' +
        '<i class="bi bi-inbox fs-3 d-block mb-2"></i>' +
        (regs.length
          ? 'Sin resultados para el filtro.'
          : 'No hay registros todavía. Usa el módulo <strong>Registro</strong> para crear el primero.') +
        '</td></tr>';
      return;
    }

    tbody.innerHTML = filtrados
      .map(function (reg) {
        const indexOriginal = regs.findIndex(function (r) {
          return r.folio === reg.folio || r.id === reg.id || r.id_mascota === reg.id_mascota;
        });
        const est = reg.estatus || 'En Búsqueda';
        let badgeColor = 'bg-secondary';
        if (est === 'En Búsqueda') badgeColor = 'bg-danger';
        else if (est === 'En Resguardo') badgeColor = 'bg-warning text-dark';
        else if (est === 'Localizado') badgeColor = 'bg-success';
        const folio = String(reg.folio || '—').replace(/</g, '&lt;').replace(/'/g, '&#39;');
        const nombre = String(reg.nombre || '—').replace(/</g, '&lt;');
        const raza = String(reg.raza || reg.raza_nombre || '—').replace(/</g, '&lt;');
        const alc = String(reg.alcaldia || reg.alcaldia_nombre || '—').replace(/</g, '&lt;');
        const fecha = String(reg.fecha || reg.fecha_extravio || '—').replace(/</g, '&lt;');
        const id = reg.id || reg.id_mascota || '';
        var fotoSrc =
          reg.foto && String(reg.foto) !== 'null' && String(reg.foto).trim() !== ''
            ? String(reg.foto)
            : reg.foto_url && String(reg.foto_url) !== 'null' && String(reg.foto_url).trim() !== ''
              ? String(reg.foto_url)
              : String(reg.especie || '').toLowerCase().indexOf('gato') >= 0
                ? '/assets/img/placeholder-gato.svg'
                : '/assets/img/placeholder-perro.svg';
        return (
          '<tr data-id="' +
          id +
          '" data-folio="' +
          folio +
          '">' +
          '<td class="ps-4 fw-bold text-danger">#' +
          folio +
          '</td>' +
          '<td><img src="' +
          String(fotoSrc).replace(/"/g, '&quot;') +
          '" alt="' +
          nombre +
          '" class="rounded border" style="width:48px;height:48px;object-fit:cover;background:#f0f0f0;" onerror="this.onerror=null;this.src=\'/assets/img/placeholder-mascota.svg\'"></td>' +
          '<td><strong>' +
          nombre +
          '</strong></td>' +
          '<td>' +
          raza +
          '</td>' +
          '<td>' +
          alc +
          '</td>' +
          '<td>' +
          fecha +
          '</td>' +
          '<td><span class="badge ' +
          badgeColor +
          '">' +
          String(est).replace(/</g, '&lt;') +
          '</span></td>' +
          '<td class="text-end pe-4">' +
          '<button type="button" class="btn btn-sm btn-outline-primary me-1" title="Ver Ficha" data-crud="ver" data-index="' +
          indexOriginal +
          '"><i class="bi bi-eye"></i></button>' +
          '<button type="button" class="btn btn-sm btn-outline-secondary me-1" title="Editar" data-crud="editar" data-index="' +
          indexOriginal +
          '"><i class="bi bi-pencil"></i></button>' +
          '<button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" data-crud="eliminar" data-index="' +
          indexOriginal +
          '"><i class="bi bi-trash"></i></button>' +
          '</td></tr>'
        );
      })
      .join('');
  }

  function refreshAllModules() {
    try {
      // Pintar CRUD con 8 columnas (Folio|Foto|Mascota|Raza|Alcaldía|Fecha|Estatus|Acciones)
      if (global.RUPECrud && typeof global.RUPECrud.renderTablaCrud === 'function') {
        global.RUPECrud.renderTablaCrud();
      } else {
        renderTablaCrudDB();
      }
      global.renderizarTablaCrud = function (filtro) {
        if (global.RUPECrud && typeof global.RUPECrud.renderTablaCrud === 'function') {
          return global.RUPECrud.renderTablaCrud(filtro);
        }
        return renderTablaCrudDB(filtro);
      };
      if (typeof global.renderizarTodo === 'function') {
        try { global.renderizarTodo(); } catch (e) { console.warn(e); }
        // Volver a pintar CRUD por si renderizarTodo falló a medias
        renderTablaCrudDB();
      }
      else {
        if (typeof global.renderizarTablaCrud === 'function') global.renderizarTablaCrud();
        if (typeof global.actualizarSelectoresFolioTutor === 'function') global.actualizarSelectoresFolioTutor();
        if (typeof global.renderizarModuloEstatus5 === 'function') global.renderizarModuloEstatus5();
        if (typeof global.renderizarAlbergues === 'function') global.renderizarAlbergues();
        if (typeof global.actualizarAnalisisMetricas === 'function') global.actualizarAnalisisMetricas();
      }
      // Ficha del folio seleccionado
      if (global.state && global.state.folioSeleccionado) {
        const reg = (global.state.registros || []).find((r) => r.folio === global.state.folioSeleccionado);
        if (reg) {
          if (typeof global.renderizarFichaTutor === 'function') global.renderizarFichaTutor(reg);
          if (typeof global.renderizarSeguimientoTutor === 'function') global.renderizarSeguimientoTutor(reg);
          if (typeof global.rupeGenerarQR === 'function' && reg.qr_token) {
            const base = location.origin || '';
            global.rupeGenerarQR(`${base}/mascota/${encodeURIComponent(reg.qr_token)}`, reg.folio);
          }
        }
      }
      // Placeholders FOL mock en UI
      document.querySelectorAll('[id*="Folio"], [id*="folio"]').forEach((el) => {
        if (el.tagName === 'INPUT' || el.tagName === 'SELECT') return;
        const txt = el.textContent || '';
        if (txt.includes('FOL-2026') && global.state?.folioSeleccionado) {
          el.textContent = txt.replace(/FOL-2026-\d+/g, global.state.folioSeleccionado);
        }
      });
    } catch (err) {
      console.warn('[RUPE] refreshAllModules', err);
    }
  }

  async function loadCatalogos() {
    try {
      const [alc, razas] = await Promise.all([
        api.get('/api/catalogos/alcaldias'),
        api.get('/api/catalogos/razas'),
      ]);
      global.__RUPE_ALCALDIAS__ = alc.data || [];
      global.__RUPE_RAZAS__ = razas.data || [];
      fillSelects();
    } catch (e) {
      console.warn('[RUPE] catálogos', e);
    }
  }

  function fillSelects() {
    const alcs = global.__RUPE_ALCALDIAS__ || [];
    const razas = global.__RUPE_RAZAS__ || [];
    document.querySelectorAll('select[id*="lcaldia"], select[id*="Alcaldia"], #cfgAlcaldiaDefault').forEach((sel) => {
      if (!sel || sel.dataset.rupeFilled) return;
      const keepFirst = sel.options[0] ? sel.options[0].outerHTML : '<option value="">Seleccione</option>';
      sel.innerHTML = keepFirst;
      alcs.forEach((a) => {
        const o = document.createElement('option');
        o.value = a.id_alcaldia;
        o.textContent = a.nombre;
        sel.appendChild(o);
      });
      sel.dataset.rupeFilled = '1';
    });
    document.querySelectorAll('select[id*="aza"], select[id*="Raza"]').forEach((sel) => {
      if (!sel || sel.dataset.rupeFilled) return;
      const keepFirst = sel.options[0] ? sel.options[0].outerHTML : '<option value="">Seleccione</option>';
      sel.innerHTML = keepFirst;
      razas.forEach((r) => {
        const o = document.createElement('option');
        o.value = r.id_raza;
        o.textContent = r.nombre + (r.especie ? ` (${r.especie})` : '');
        sel.appendChild(o);
      });
      sel.dataset.rupeFilled = '1';
    });
  }

  /** Registro de mascota */
  async function procesarRegistroTutor(e) {
    if (e) e.preventDefault();
    const form = document.getElementById('formCapturaPerro') || e?.target;
    const get = (id) => document.getElementById(id);

    const payload = {
      nombre: (get('regNombre') || get('nombreMascota') || form?.querySelector('[name="nombre"]'))?.value?.trim() || '',
      especie: (get('regEspecie') || form?.querySelector('[name="especie"]'))?.value || 'perro',
      sexo: (get('regSexo') || form?.querySelector('[name="sexo"]'))?.value || 'desconocido',
      id_raza: (get('regRaza') || form?.querySelector('[name="id_raza"]'))?.value || '',
      id_alcaldia: (get('regAlcaldia') || form?.querySelector('[name="id_alcaldia"]'))?.value || '',
      color: (get('regColor') || form?.querySelector('[name="color"]'))?.value?.trim() || '',
      edad_aprox: (get('regEdad') || form?.querySelector('[name="edad_aprox"]'))?.value?.trim() || '',
      senas_particulares: (get('regSenas') || form?.querySelector('[name="senas"]'))?.value?.trim() || '',
      colonia: (get('regColonia') || form?.querySelector('[name="colonia"]'))?.value?.trim() || '',
      direccion_ref: (get('regDireccion') || form?.querySelector('[name="direccion"]'))?.value?.trim() || '',
      fecha_extravio: (get('regFecha') || form?.querySelector('[name="fecha"]'))?.value || '',
      latitud: (get('regLat') || form?.querySelector('[name="latitud"]'))?.value || '',
      longitud: (get('regLng') || form?.querySelector('[name="longitud"]'))?.value || '',
      estatus: 'En Búsqueda',
    };

    // Fallback: collect by placeholders/labels from form fields order
    if (!payload.nombre && form) {
      const inputs = form.querySelectorAll('input, select, textarea');
      inputs.forEach((el) => {
        const id = (el.id || '').toLowerCase();
        const name = (el.name || '').toLowerCase();
        const val = el.value;
        if (!val) return;
        if (id.includes('nombre') || name.includes('nombre')) payload.nombre = val.trim();
        if (id.includes('raza') || name.includes('raza')) payload.id_raza = val;
        if (id.includes('alcaldia') || name.includes('alcaldia')) payload.id_alcaldia = val;
        if (id.includes('fecha') || name.includes('fecha')) payload.fecha_extravio = val;
        if (id.includes('sena') || name.includes('sena')) payload.senas_particulares = val.trim();
        if (id.includes('color')) payload.color = val.trim();
        if (id.includes('colonia')) payload.colonia = val.trim();
      });
    }

    const localErr = valRequired(payload, ['nombre']);
    if (Object.keys(localErr).length) {
      fieldErrors(form, localErr);
      toast('Completa los campos obligatorios (nombre).', 'warning');
      return;
    }

    // Normalizar especie/sexo por si el select tiene etiquetas en español
    const mapEsp = (v) => {
      const s = String(v || '').toLowerCase();
      if (s.includes('gato')) return 'gato';
      if (s.includes('otro')) return 'otro';
      if (s.includes('perro') || s === 'canino') return 'perro';
      return ['perro', 'gato', 'otro'].includes(s) ? s : 'perro';
    };
    const mapSex = (v) => {
      const s = String(v || '').toLowerCase();
      if (s.includes('hembra')) return 'hembra';
      if (s.includes('macho')) return 'macho';
      return ['macho', 'hembra', 'desconocido'].includes(s) ? s : 'desconocido';
    };
    payload.especie = mapEsp(payload.especie);
    payload.sexo = mapSex(payload.sexo);
    if (payload.id_raza === '' || payload.id_raza === '0' || payload.id_raza === 0) {
      payload.id_raza = 0;
    } else {
      payload.id_raza = parseInt(payload.id_raza, 10) || '';
    }
    if (payload.id_alcaldia === '' || payload.id_alcaldia === '0' || payload.id_alcaldia === 0) {
      payload.id_alcaldia = 0;
    } else {
      payload.id_alcaldia = parseInt(payload.id_alcaldia, 10) || '';
    }
    // Validar que se eligió raza y alcaldía del catálogo (IDs)
    if (!payload.id_raza) {
      toast('Selecciona una raza del catálogo.', 'warning');
      return;
    }
    if (!payload.id_alcaldia) {
      toast('Selecciona una alcaldía del catálogo.', 'warning');
      return;
    }

    // Foto base64 si el usuario la previsualizó
    if (global.fotoRegistroCapturada && String(global.fotoRegistroCapturada).indexOf('data:image/') === 0) {
      payload.foto_base64 = global.fotoRegistroCapturada;
    }
    const fileInput = document.getElementById('regFoto');
    const file = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : '';

    try {
      let res;
      if (file) {
        const fd = new FormData();
        Object.keys(payload).forEach((k) => {
          if (payload[k] !== undefined && payload[k] !== '') fd.append(k, String(payload[k]));
        });
        fd.append('foto', file);
        res = await api.post('/api/tutor/mascotas', fd);
      } else {
        res = await api.post('/api/tutor/mascotas', payload);
      }
      const m = res.data?.mascota || {};
      const folioOk = res.data?.folio || m.folio || '';
      const qrOk = res.data?.qr_token || m.qr_token || '';
      if (m && !m.qr_token && qrOk) m.qr_token = qrOk;
      if (m && !m.folio && folioOk) m.folio = folioOk;
      toast(res.message || `Registro guardado. Folio: ${folioOk}`, 'success');
      if (typeof global.renderizarFichaTutor === 'function' && (m.folio || folioOk)) {
        global.renderizarFichaTutor({
          ...m,
          folio: folioOk,
          qr_token: qrOk,
          raza: m.raza_nombre || m.raza || '',
          alcaldia: m.alcaldia_nombre || m.alcaldia || '',
          fecha: m.fecha_extravio || '',
          senas: m.senas_particulares || '',
        });
      }
      await syncMascotas();
      if (typeof global.renderizarTodo === 'function') global.renderizarTodo();
      if (typeof global.renderizarTablaCrud === 'function') global.renderizarTablaCrud();
      if (form) form.reset();
      const crudBtn = document.querySelector('[data-modulo="crud"]');
      if (crudBtn) crudBtn.click();
    } catch (err) {
      if (err.errors) fieldErrors(form, err.errors);
      toast(err.message || 'No se pudo guardar el registro.', 'error');
    }
  }

  async function guardarEdicionRegistro(e) {
    if (e) e.preventDefault();
    const index = parseInt(document.getElementById('editIndex')?.value ?? '-1', 10);
    const reg = global.state?.registros?.[index];
    if (!reg?.id && !reg?.id_mascota) {
      toast('Registro no encontrado.', 'error');
      return;
    }
    const payload = {
      nombre: document.getElementById('editNombre')?.value?.trim(),
      senas_particulares: document.getElementById('editSenas')?.value?.trim(),
      fecha_extravio: document.getElementById('editFecha')?.value || '',
      color: document.getElementById('editColor')?.value?.trim() || '',
      colonia: document.getElementById('editColonia')?.value?.trim() || '',
    };
    const idAlc = document.getElementById('editAlcaldia')?.value;
    if (idAlc) payload.id_alcaldia = parseInt(idAlc, 10);
    if (!payload.nombre || payload.nombre.length < 2) {
      toast('El nombre es obligatorio (mín. 2 caracteres).', 'warning');
      return;
    }
    const id = reg.id || reg.id_mascota;
    const fileInput = document.getElementById('editFoto');
    const file = fileInput?.files?.[0] || '';
    try {
      if (file) {
        const fd = new FormData();
        Object.keys(payload).forEach((k) => {
          if (payload[k] !== undefined && payload[k] !== '') fd.append(k, String(payload[k]));
        });
        fd.append('foto', file);
        await api.post(`/api/tutor/mascotas/${id}`, fd);
      } else if (id) {
        await api.put(`/api/tutor/mascotas/${id}`, payload);
      }
      toast('Cambios guardados correctamente.', 'success');
      await syncMascotas();
      if (typeof global.renderizarTablaCrud === 'function') global.renderizarTablaCrud();
      if (typeof global.renderizarTodo === 'function') global.renderizarTodo();
      if (global.modalEditarBs) global.modalEditarBs.hide();
    } catch (err) {
      toast(err.message || 'No se pudo guardar.', 'error');
    }
  }

  async function cambiarEstatusTutor(e) {
    if (e && e.preventDefault) e.preventDefault();
    const folio =
      document.getElementById('tutorSelectFolioEstatus')?.value ||
      document.getElementById('selectFolioEstatus')?.value ||
      global.state?.folioSeleccionado;
    const nuevo =
      document.getElementById('tutorNuevoEstatus')?.value ||
      document.getElementById('nuevoEstatus')?.value;
    if (!folio || !nuevo) {
      toast('Selecciona folio y estatus.', 'warning');
      return;
    }
    const reg = (global.state?.registros || []).find((r) => r.folio === folio);
    if (!reg?.id) {
      toast('No se encontró el registro en el servidor.', 'error');
      return;
    }
    try {
      await api.put(`/api/tutor/mascotas/${reg.id}`, { estatus: nuevo });
      toast(`Estatus actualizado a: ${nuevo}`, 'success');
      await syncMascotas();
      if (typeof global.renderizarTodo === 'function') global.renderizarTodo();
      if (typeof global.renderizarModuloEstatus5 === 'function') global.renderizarModuloEstatus5();
    } catch (err) {
      toast(err.message || 'No se pudo actualizar el estatus.', 'error');
    }
  }

  async function addSeguimientoFromUI(descripcion, tipo = 'avistamiento') {
    const folio =
      document.getElementById('tutorSelectFolioSeg')?.value ||
      global.state?.folioSeleccionado;
    const reg = (global.state?.registros || []).find((r) => r.folio === folio);
    if (!reg?.id) {
      toast('Selecciona un folio válido.', 'warning');
      return;
    }
    if (!descripcion || descripcion.trim().length < 3) {
      toast('Describe el avistamiento (mín. 3 caracteres).', 'warning');
      return;
    }
    try {
      await api.post(`/api/tutor/mascotas/${reg.id}/seguimiento`, {
        descripcion: descripcion.trim(),
        tipo,
      });
      toast('Seguimiento guardado.', 'success');
    } catch (err) {
      toast(err.message || 'No se pudo guardar el seguimiento.', 'error');
    }
  }

  async function guardarPerfilTutor(e) {
    if (e) e.preventDefault();
    const form = document.getElementById('formEditarPerfil') || e?.target;
    if (typeof global.rupeValidarPerfilTutor === 'function' && !global.rupeValidarPerfilTutor(true)) {
      toast('Completa nombre con apellidos, teléfono de 10 dígitos y correo válido.', 'warning');
      return;
    }
    const payload = {
      nombre: (document.getElementById('perfilInputNombre')?.value || '').trim().replace(/\s+/g, ' '),
      telefono: String(document.getElementById('perfilInputTelefono')?.value || '').replace(/\D/g, ''),
      correo: (document.getElementById('perfilInputCorreo')?.value || '').trim(),
      acepta_privacidad: document.getElementById('perfilInputPrivacidad')?.checked ? 1 : 0,
    };
    const reNom = /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+(?:\s+[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+){1,4}$/;
    if (!reNom.test(payload.nombre || '')) {
      toast('Nombre completo: incluye nombre y apellidos (solo letras).', 'warning');
      return;
    }
    if (!/^\d{10}$/.test(payload.telefono || '')) {
      toast('Teléfono: exactamente 10 dígitos.', 'warning');
      return;
    }
    if (!payload.correo || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(payload.correo)) {
      toast('Correo no válido.', 'warning');
      return;
    }
    try {
      const res = await api.put('/api/tutor/perfil', payload);
      toast(res.message || 'Perfil actualizado.', 'success');
      if (global.state && res.data?.user) {
        global.state.tutorActual = {
          ...(global.state.tutorActual || {}),
          nombre: res.data.user.nombre,
          telefono: res.data.user.telefono,
          correo: res.data.user.correo,
          aceptaPrivacidad: !!res.data.user.acepta_privacidad,
        };
      }
      if (typeof global.renderizarPerfilTutor === 'function') global.renderizarPerfilTutor();
      if (global.modalEditarPerfilBs) global.modalEditarPerfilBs.hide();
    } catch (err) {
      if (err.errors) fieldErrors(form, err.errors);
      toast(err.message || 'No se pudo actualizar el perfil.', 'error');
    }
  }

  async function guardarPreferencias() {
    const payload = {
      notif_email: document.getElementById('cfgNotifEmail')?.checked ? 1 : 0,
      notif_sms: document.getElementById('cfgNotifSms')?.checked ? 1 : 0,
      notif_avistamiento: document.getElementById('cfgNotifAvistamiento')?.checked ? 1 : 0,
      notif_cambio_estatus: document.getElementById('cfgNotifEstatus')?.checked ? 1 : 0,
      notif_boletin: document.getElementById('cfgNotifBoletin')?.checked ? 1 : 0,
      idioma: document.getElementById('cfgIdioma')?.value || 'es',
      id_alcaldia_default: document.getElementById('cfgAlcaldiaDefault')?.value || '',
    };
    try {
      await api.put('/api/tutor/preferencias', payload);
      toast('Preferencias guardadas.', 'success');
    } catch (err) {
      toast(err.message || 'No se pudieron guardar las preferencias.', 'error');
    }
  }

  async function logoutTutor() {
    try {
      await api.post('/api/auth/logout', {});
    } catch (_) {}
    window.location.href = '/auth/logout';
  }

  async function init() {
    // CSRF
    try {
      const r = await fetch('/api/csrf', { credentials: 'same-origin' });
      const d = await r.json();
      if (d.data?.csrf) global.__CSRF__ = d.data.csrf;
    } catch (_) {}

    // Override funciones globales del panel (menús tutor)
    global.procesarRegistroTutor = procesarRegistroTutor;
    global.mapMascotaToState = mapMascotaToState;
    global.guardarEdicionRegistro = guardarEdicionRegistro;
    global.guardarPerfilTutor = guardarPerfilTutor;
    global.guardarPreferencias = guardarPreferencias;
    global.cambiarEstatusTutor = cambiarEstatusTutor;
    global.cambiarContrasenaTutor = async function (e) {
      if (e) e.preventDefault();
      const actual = document.getElementById('cfgPassActual')?.value || '';
      const nueva = document.getElementById('cfgPassNueva')?.value || '';
      const confirmar = document.getElementById('cfgPassConfirmar')?.value || '';
      const _vp = (typeof global.validarPasswordFuerte === 'function')
          ? global.validarPasswordFuerte(nueva)
          : (function (p) {
              const fails = [];
              if (String(p).length < 8) fails.push('Mínimo 8 caracteres.');
              if (!/[A-Z]/.test(p)) fails.push('Una mayúscula.');
              if (!/[a-z]/.test(p)) fails.push('Una minúscula.');
              if (!/\d/.test(p)) fails.push('Un número.');
              if (!/[^A-Za-z0-9]/.test(p)) fails.push('Un carácter especial.');
              return { ok: !fails.length, message: fails.join(' ') };
            })(nueva);
        if (!_vp.ok) { toast(_vp.message || 'Contraseña no válida.', 'warning'); return; }
        if (confirmar != null && String(confirmar) !== String(nueva)) { toast('La confirmación no coincide.', 'warning'); return; }
      if (nueva !== confirmar) { toast('La confirmación no coincide.', 'warning'); return; }
      try {
        await api.put('/api/tutor/password', {
          password_actual: actual,
          password: nueva,
          password_confirmation: confirmar
        });
        toast('Contraseña actualizada correctamente.', 'success');
        document.getElementById('formCambiarContrasena')?.reset();
      } catch (err) {
        toast(err.message || 'No se pudo cambiar la contraseña.', 'error');
      }
    };
    global.rupeAddSeguimiento = addSeguimientoFromUI;
    global.rupeSyncMascotas = syncMascotas;
    global.renderizarTablaCrud = function (filtro) {
      if (typeof global.pintarTablaCrud === 'function') return global.pintarTablaCrud(filtro);
      return renderTablaCrudDB(filtro);
    };
    global.filtrarTablaCrud = function () {
      const q = document.getElementById('inputBuscadorCrud')?.value || '';
      renderTablaCrudDB(q);
    };
    // No sobrescribir rupeToast del sistema de alertas (evita recursión)
    if (typeof global.rupeToast !== 'function') global.rupeToast = toast;

    // Formulario de registro → API real
    const formReg = document.getElementById('formCapturaPerro');
    if (formReg && !formReg._rupeAppBound) {
      formReg._rupeAppBound = true;
      formReg.addEventListener('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (typeof global.procesarRegistroTutor === 'function') {
          global.procesarRegistroTutor(e);
        }
      });
    }

    // Cerrar sesión
    const btnConfirmar = document.getElementById('btnConfirmarCierre');
    if (btnConfirmar) {
      btnConfirmar.addEventListener('click', () => {
        setTimeout(() => logoutTutor(), 400);
      });
    }

    // Segumiento: si hay botón registrar avistamiento
    document.querySelectorAll('[data-action="guardar-avistamiento"]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const txt =
          document.getElementById('inputAvistamiento')?.value ||
          document.getElementById('tutorAvistamientoDesc')?.value ||
          '';
        addSeguimientoFromUI(txt, 'avistamiento');
      });
    });

    try {
      await loadUsuarioSesion();
      await loadPreferenciasDB();
      await loadCatalogos();
      await loadAlberguesDB();
      await syncMascotas();
      await loadAyudaPublica();
      refreshAnaliticoFromDB();
      if (typeof global.renderizarTodo === 'function') global.renderizarTodo();
      if (typeof global.renderizarTablaCrud === 'function') global.renderizarTablaCrud();
      if (typeof global.actualizarAnalisisMetricas === 'function') global.actualizarAnalisisMetricas();
      toast('Datos sincronizados con el servidor.', 'info');
    } catch (e) {
      console.warn('[RUPE] sync inicial', e);
      if (e.status === 401) {
        toast('Sesión expirada. Inicia sesión de nuevo.', 'error');
        setTimeout(() => {
          window.location.href = '/';
        }, 1500);
      }
    }
  }


  // Al cambiar de módulo, recargar datos del folio activo
  document.addEventListener('click', async (ev) => {
    const btn = ev.target.closest('.nav-modulo');
    if (!btn) return;
    const mod = btn.getAttribute('data-modulo');
    if (!global.state) return;
    const reg = (global.state.registros || []).find((r) => r.folio === global.state.folioSeleccionado) || global.state.registros[0];
    if (!reg) return;
    if (mod === 'seguimiento' || mod === 'estatus' || mod === 'ficha-pdf' || mod === 'gps-qr') {
      if (reg.id) await loadSeguimientosFor(reg.id);
      if (mod === 'ficha-pdf') {
      await syncMascotas();
      const reg2 = (global.state.registros || []).find((r) => r.folio === global.state.folioSeleccionado) || global.state.registros[0];
      if (reg2 && typeof global.renderizarFichaTutor === 'function') global.renderizarFichaTutor(reg2);
    }
      if (mod === 'seguimiento' && typeof global.renderizarSeguimientoTutor === 'function') global.renderizarSeguimientoTutor(reg);
      if (mod === 'estatus' && typeof global.renderizarModuloEstatus5 === 'function') global.renderizarModuloEstatus5();
      if (mod === 'gps-qr' && typeof global.renderizarContextoFolioGPS === 'function') {
        try { global.renderizarContextoFolioGPS(); } catch (_) {}
      }
    }
    if (mod === 'albergues' && typeof global.renderizarAlbergues === 'function') {
      await loadAlberguesDB();
      global.renderizarAlbergues();
    }
    if (mod === 'crud') {
      if (typeof renderTablaCrudDB === 'function') renderTablaCrudDB();
      else if (typeof global.renderizarTablaCrud === 'function') global.renderizarTablaCrud();
    }
    if (mod === 'perfil' || mod === 'configuracion') {
      await loadUsuarioSesion();
      await loadPreferenciasDB();
      if (typeof global.renderizarPerfilTutor === 'function') global.renderizarPerfilTutor();
    }
    if (mod === 'ayuda') {
      await loadAyudaPublica();
    }
    if (mod === 'analisis') {
      refreshAnaliticoFromDB();
    }
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => setTimeout(init, 0));
  } else {
    setTimeout(init, 0);
  }

  if (typeof global.pintarTablaCrud === 'function') { try { global.pintarTablaCrud(); } catch (_) {} }
  global.RUPETutor = { api, toast, syncMascotas, loadUsuarioSesion, loadAlberguesDB, loadSeguimientosFor, loadPreferenciasDB, loadAyudaPublica, refreshAnaliticoFromDB, refreshAllModules, renderTablaCrudDB, init };
})(window);


/* RUPE_NAV_SAFEGUARD: no bloquear navegación real del menú */
(function () {
  document.addEventListener('click', function (ev) {
    var btn = ev.target && ev.target.closest && ev.target.closest('.nav-modulo[data-modulo]');
    if (!btn) return;
    // Dejar que rupe-panels maneje; si no está, navegar
    if (typeof window.activarModuloTutor !== 'function') {
      var map = {
        analisis: '/tutor/analitico', registro: '/tutor/registro', crud: '/tutor/crud',
        'ficha-pdf': '/tutor/ficha-pdf', 'gps-qr': '/tutor/gpsqr', seguimiento: '/tutor/seguimiento',
        estatus: '/tutor/estatus', albergues: '/tutor/albergues'
      };
      var k = btn.getAttribute('data-modulo');
      if (map[k]) window.location.assign(map[k]);
    }
  }, true);
})();
