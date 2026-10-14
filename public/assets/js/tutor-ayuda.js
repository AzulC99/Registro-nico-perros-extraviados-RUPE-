/**
 * RUPE CDMX — Centro de Ayuda: enviar mensaje a buscandoahuellitasrupe.com.mx
 */
'use strict';

(function (global) {
  function toast(msg, type) {
    if (typeof global.rupeToast === 'function') return global.rupeToast(msg, type || 'info');
    if (typeof global.mostrarToastTutor === 'function') return global.mostrarToastTutor(msg, type || 'success');
  }

  function csrf() {
    return global.__CSRF__ || (global.RUPE_INIT && global.RUPE_INIT.csrf) || '';
  }

  function userCorreo() {
    var u = (global.RUPE_INIT && global.RUPE_INIT.user) || {};
    var t = (global.state && global.state.tutorActual) || {};
    return u.correo || u.email || t.correo || '';
  }

  async function enviarMensajeAyuda(e) {
    if (e && e.preventDefault) e.preventDefault();
    var asuntoEl = document.getElementById('ayudaAsunto');
    var msgEl = document.getElementById('ayudaMensaje');
    var asunto = (asuntoEl && asuntoEl.value ? asuntoEl.value : '').trim();
    var mensaje = (msgEl && msgEl.value ? msgEl.value : '').trim();
    if (!asunto) {
      toast('Selecciona un asunto.', 'warning');
      return false;
    }
    if (mensaje.length < 10) {
      toast('Escribe un mensaje de al menos 10 caracteres.', 'warning');
      return false;
    }

    var token = csrf();
    var fd = new FormData();
    fd.append('asunto', asunto);
    fd.append('mensaje', mensaje);
    if (token) {
      fd.append('_csrf', token);
      fd.append('_token', token);
    }

    var btn = document.querySelector('#formContactoAyuda button[type="submit"]');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando…';
    }

    try {
      var res = await fetch('/api/tutor/ayuda-contacto', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': token,
        },
      });
      var data = await res.json().catch(function () {
        return {};
      });
      if (!res.ok || data.success === false) {
        toast(
          data.message ||
            'No se pudo enviar. Escribe a admin@buscandoahuellitasrupe.com.mx',
          'danger'
        );
        return false;
      }
      var form = document.getElementById('formContactoAyuda');
      if (form) form.reset();
      toast(
        'Mensaje enviado a buscandoahuellitasrupe.com.mx. Te responderemos a ' +
          (userCorreo() || 'tu correo registrado') +
          '.',
        'success'
      );
      return true;
    } catch (err) {
      console.error(err);
      toast(
        'Error de red. Intenta de nuevo o escribe a admin@buscandoahuellitasrupe.com.mx',
        'danger'
      );
      return false;
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Enviar mensaje';
      }
    }
  }
  global.enviarMensajeAyuda = enviarMensajeAyuda;

  function bind() {
    var form = document.getElementById('formContactoAyuda');
    if (form && !form._rupeAyudaBound) {
      form._rupeAyudaBound = true;
      form.addEventListener('submit', function (ev) {
        enviarMensajeAyuda(ev);
      });
    }
    var el = document.getElementById('ayudaCorreoRespuesta');
    if (el) {
      var c = userCorreo();
      if (c) el.textContent = c;
    }
  }

  document.addEventListener('click', function (e) {
    if (
      e.target.closest('.nav-modulo[data-modulo="ayuda"]') ||
      e.target.closest('[onclick*="mostrarModuloAyuda"]')
    ) {
      setTimeout(bind, 200);
    }
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bind);
  } else {
    bind();
  }
  global.addEventListener('load', function () {
    setTimeout(bind, 50);
  });
})(typeof window !== 'undefined' ? window : globalThis);
