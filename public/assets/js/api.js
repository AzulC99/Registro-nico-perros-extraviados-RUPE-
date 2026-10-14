/**
 * RUPE CDMX — Cliente API ES6
 * public/assets/js/api.js
 * Validaciones sin flash/push · manejo de excepciones · CSRF · reCAPTCHA v3
 */

'use strict';

const RUPE = (() => {
  const BASE = ''; // mismo origen
  let csrfToken = window.__CSRF__ || '';
  let recaptchaSiteKey = window.__RECAPTCHA_SITE_KEY__ || '';

  class ApiError extends Error {
    constructor(message, status = 400, errors = {}) {
      super(message);
      this.name = 'ApiError';
      this.status = status;
      this.errors = errors;
    }
  }

  async function request(method, path, body = undefined, options = {}) {
    const headers = {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    };
    if (csrfToken) {
      headers['X-CSRF-Token'] = csrfToken;
    }
    let payload = body;
    if (body && !(body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
      payload = JSON.stringify({ ...body, _csrf: csrfToken });
    } else if (body instanceof FormData) {
      body.append('_csrf', csrfToken);
      payload = body;
    }

    const ctrl = new AbortController();
    const timeout = setTimeout(() => ctrl.abort(), options.timeout || 20000);

    try {
      const res = await fetch(BASE + path, {
        method,
        headers,
        body: method === 'GET' || method === 'HEAD' ? undefined : payload,
        credentials: 'same-origin',
        signal: ctrl.signal,
      });
      clearTimeout(timeout);

      let data;
      const ct = res.headers.get('Content-Type') || '';
      if (ct.includes('application/json')) {
        data = await res.json();
      } else {
        data = { success: res.ok, message: res.statusText };
      }

      if (data && data.data && data.data.csrf) {
        csrfToken = data.data.csrf;
        window.__CSRF__ = csrfToken;
      }

      if (!res.ok || data.success === false) {
        throw new ApiError(
          data.message || 'Error de servidor',
          res.status,
          data.errors || ''
        );
      }
      return data;
    } catch (err) {
      clearTimeout(timeout);
      if (err.name === 'AbortError') {
        throw new ApiError('Tiempo de espera agotado', 408);
      }
      if (err instanceof ApiError) throw err;
      throw new ApiError(err.message || 'Error de red', 0);
    }
  }

  async function getRecaptchaToken(action = 'submit') {
    if (!recaptchaSiteKey || typeof grecaptcha === 'undefined') {
      return 'dev-bypass';
    }
    return new Promise((resolve, reject) => {
      try {
        grecaptcha.ready(() => {
          grecaptcha.execute(recaptchaSiteKey, { action })
            .then(resolve)
            .catch(() => resolve(''));
        });
      } catch (e) {
        resolve('');
      }
    });
  }

  /** Mostrar errores de validación en el DOM (sin flash) */
  function showFieldErrors(form, errors) {
    if (!form || !errors) return;
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback.rupe-err').forEach(el => el.remove());
    Object.entries(errors).forEach(([field, msgs]) => {
      const input = form.querySelector(`[name="${field}"]`);
      if (!input) return;
      input.classList.add('is-invalid');
      const div = document.createElement('div');
      div.className = 'invalid-feedback rupe-err d-block';
      div.textContent = Array.isArray(msgs) ? msgs[0] : String(msgs);
      input.parentNode.appendChild(div);
    });
  }

  function clearFieldErrors(form) {
    if (!form) return;
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback.rupe-err').forEach(el => el.remove());
  }

  function toast(message, type = 'info') {
    const el = document.getElementById('rupeToast') || document.getElementById('toastTutor');
    if (el) {
      el.textContent = message;
      el.className = `toast show align-items-center text-bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0`;
      setTimeout(() => el.classList.remove('show'), 4000);
      return;
    }
    // Fallback
    console[type === 'error' ? 'error' : 'log']('[RUPE]', message);
  }

  // API surface
  const api = {
    setCsrf(t) { csrfToken = t; window.__CSRF__ = t; },
    setRecaptchaKey(k) { recaptchaSiteKey = k; },
    get,
    post,
    put,
    patch,
    del: deleteReq,
    showFieldErrors,
    clearFieldErrors,
    toast,
    ApiError,
    getRecaptchaToken,

    // Auth
    async login(correo, password, form) {
      const recaptcha_token = await getRecaptchaToken('login');
      try {
        const r = await post('/api/auth/login', { correo, password, recaptcha_token });
        if (r && r.data && r.data.csrf) csrfToken = r.data.csrf;
        return r;
      } catch (e) {
        if (e.errors) showFieldErrors(form, e.errors);
        throw e;
      }
    },
    async register(payload, form) {
      const recaptcha_token = await getRecaptchaToken('register');
      try {
        return await post('/api/auth/register', { ...payload, recaptcha_token });
      } catch (e) {
        if (e.errors) showFieldErrors(form, e.errors);
        throw e;
      }
    },
    logout: () => post('/api/auth/logout'),
    me: () => get('/api/auth/me'),
    forgot: async (correo, form) => {
      const recaptcha_token = await getRecaptchaToken('forgot');
      try {
        return await post('/api/auth/forgot', { correo, recaptcha_token });
      } catch (e) {
        if (e.errors) showFieldErrors(form, e.errors);
        throw e;
      }
    },
    resetPassword: (token, password, password_confirmation, form) =>
      post('/api/auth/reset', { token, password, password_confirmation })
        .catch(e => { if (e.errors) showFieldErrors(form, e.errors); throw e; }),

    // Tutor
    listMascotas: () => get('/api/tutor/mascotas'),
    getMascota: (id) => get(`/api/tutor/mascotas/${id}`),
    createMascota: (data, form) =>
      post('/api/tutor/mascotas', data).catch(e => { if (e.errors) showFieldErrors(form, e.errors); throw e; }),
    updateMascota: (id, data, form) =>
      put(`/api/tutor/mascotas/${id}`, data).catch(e => { if (e.errors) showFieldErrors(form, e.errors); throw e; }),
    addSeguimiento: (id, data) => post(`/api/tutor/mascotas/${id}/seguimiento`, data),
    perfil: () => get('/api/tutor/perfil'),
    updatePerfil: (data, form) =>
      put('/api/tutor/perfil', data).catch(e => { if (e.errors) showFieldErrors(form, e.errors); throw e; }),
    updatePreferencias: (data) => put('/api/tutor/preferencias', data),
    catalogo: (tipo, qs = '') => get(`/api/catalogos/${tipo}${qs ? '?' + qs : ''}`),
    geocode: (q) => get(`/api/geo/geocode?q=${encodeURIComponent(q)}`),

    // Admin
    adminUsuarios: (rol) => get('/api/admin/usuarios' + (rol ? `?rol=${rol}` : '')),
    adminUpdateUsuario: (id, data) => patch(`/api/admin/usuarios/${id}`, data),
    adminAlcaldias: () => get('/api/admin/alcaldias'),
    adminSaveAlcaldia: (data, id) => id ? put(`/api/admin/alcaldias/${id}`, data) : post('/api/admin/alcaldias', data),
    adminRazas: () => get('/api/admin/razas'),
    adminSaveRaza: (data, id) => id ? put(`/api/admin/razas/${id}`, data) : post('/api/admin/razas', data),
    adminReportes: (qs = '') => get('/api/admin/reportes' + (qs ? '?' + qs : '')),
    adminAuditoria: () => get('/api/admin/auditoria'),
    adminConfig: () => get('/api/admin/config'),
    adminSetConfig: (clave, valor, tipo = 'string') => post('/api/admin/config', { clave, valor, tipo }),
  };

  function get(path) { return request('GET', path); }
  function post(path, body) { return request('POST', path, body); }
  function put(path, body) { return request('PUT', path, body); }
  function patch(path, body) { return request('PATCH', path, body); }
  function deleteReq(path) { return request('DELETE', path); }

  // Init CSRF
  get('/api/csrf').then(function (r) {
    if (r && r.data && r.data.csrf) {
      csrfToken = r.data.csrf;
      window.__CSRF__ = csrfToken;
    }
  }).catch(function () {});

  return api;
})();

// Export global (classic script + window)
window.RUPE = RUPE;
