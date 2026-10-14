/**
 * RUPE CDMX — Configuración / Cuenta: notificaciones, contraseña, preferencias
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

  function ensureState() {
    if (!global.state || typeof global.state !== 'object') {
      global.state = { preferencias: {}, registros: [], tutorActual: {} };
    }
    if (!global.state.preferencias || typeof global.state.preferencias !== 'object') {
      global.state.preferencias = {};
    }
    return global.state;
  }

  function mapPrefsFromDb(row) {
    row = row || {};
    return {
      notifEmail: !!(Number(row.notif_email != null ? row.notif_email : row.notifEmail != null ? row.notifEmail : 1)),
      notifSms: !!(Number(row.notif_sms != null ? row.notif_sms : row.notifSms || 0)),
      notifNuevoAvistamiento: !!(Number(
        row.notif_avistamiento != null
          ? row.notif_avistamiento
          : row.notifNuevoAvistamiento != null
            ? row.notifNuevoAvistamiento
            : 1
      )),
      notifCambioEstatus: !!(Number(
        row.notif_cambio_estatus != null
          ? row.notif_cambio_estatus
          : row.notifCambioEstatus != null
            ? row.notifCambioEstatus
            : 1
      )),
      notifBoletinRUPE: !!(Number(
        row.notif_boletin != null
          ? row.notif_boletin
          : row.notifBoletinRUPE != null
            ? row.notifBoletinRUPE
            : 0
      )),
      idioma: row.idioma || 'es',
      alcaldiaDefault: row.alcaldiaDefault || row.id_alcaldia_default || row.alcaldia_default || '',
      id_alcaldia_default: row.id_alcaldia_default || '',
    };
  }

  function loadPrefsFromInit() {
    var st = ensureState();
    var raw = (global.RUPE_INIT && global.RUPE_INIT.preferencias) || st.preferencias || {};
    st.preferencias = mapPrefsFromDb(raw);
    return st.preferencias;
  }

  async function cargarPreferenciasApi() {
    try {
      var res = await fetch('/api/tutor/preferencias', {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });
      var data = await res.json().catch(function () {
        return {};
      });
      var row =
        (data.data && data.data.preferencias) ||
        data.preferencias ||
        (data.data && !data.data.preferencias ? data.data : null) ||
        null;
      if (row && typeof row === 'object') {
        ensureState().preferencias = mapPrefsFromDb(row);
        if (global.RUPE_INIT) global.RUPE_INIT.preferencias = row;
      }
    } catch (err) {
      console.warn('[RUPE CFG] preferencias', err);
    }
  }

  function llenarSelectAlcaldiaCfg() {
    var sel = document.getElementById('cfgAlcaldiaDefault');
    if (!sel) return;
    var prev = sel.value;
    var alcs =
      (global.RUPE_INIT && Array.isArray(global.RUPE_INIT.alcaldias)
        ? global.RUPE_INIT.alcaldias
        : []) || [];
    if (!alcs.length) {
      alcs = [
        'Álvaro Obregón','Azcapotzalco','Benito Juárez','Coyoacán','Cuajimalpa de Morelos',
        'Cuauhtémoc','Gustavo A. Madero','Iztacalco','Iztapalapa','La Magdalena Contreras',
        'Miguel Hidalgo','Milpa Alta','Tláhuac','Tlalpan','Venustiano Carranza','Xochimilco',
      ].map(function (n) {
        return { nombre: n };
      });
    }
    sel.innerHTML = '<option value="">— Sin preferencia —</option>';
    alcs.forEach(function (a) {
      var id = a.id_alcaldia != null ? String(a.id_alcaldia) : '';
      var nombre = a.nombre || a;
      var o = document.createElement('option');
      o.value = id || String(nombre);
      o.textContent = nombre;
      sel.appendChild(o);
    });
    var p = ensureState().preferencias;
    var want = String(p.id_alcaldia_default || p.alcaldiaDefault || '');
    if (want) {
      try {
        sel.value = want;
      } catch (_) {}
    } else if (prev) {
      try {
        sel.value = prev;
      } catch (_) {}
    }
  }

  function renderizarConfiguracion() {
    var st = ensureState();
    var p = st.preferencias || loadPrefsFromInit();
    function setChk(id, val) {
      var el = document.getElementById(id);
      if (el) el.checked = !!val;
    }
    function setVal(id, val) {
      var el = document.getElementById(id);
      if (el) el.value = val != null ? String(val) : '';
    }
    setChk('cfgNotifEmail', p.notifEmail);
    setChk('cfgNotifSms', p.notifSms);
    setChk('cfgNotifAvistamiento', p.notifNuevoAvistamiento);
    setChk('cfgNotifEstatus', p.notifCambioEstatus);
    setChk('cfgNotifBoletin', p.notifBoletinRUPE);
    setVal('cfgIdioma', p.idioma || 'es');
    llenarSelectAlcaldiaCfg();

    var elPriv = document.getElementById('cfgEstatusPrivacidad');
    if (elPriv) {
      var ok =
        st.tutorActual &&
        (st.tutorActual.aceptaPrivacidad === true ||
          st.tutorActual.aceptaPrivacidad === 1);
      elPriv.textContent = ok ? 'Aceptado' : 'Pendiente de aceptar';
      elPriv.className = 'fw-semibold ' + (ok ? 'text-success' : 'text-warning');
    }
  }
  global.renderizarConfiguracion = renderizarConfiguracion;

  var saveTimer = null;
  async function guardarPreferencias(forceToast) {
    var st = ensureState();
    function chk(id) {
      var el = document.getElementById(id);
      return !!(el && el.checked);
    }
    function val(id) {
      var el = document.getElementById(id);
      return el ? el.value : '';
    }
    st.preferencias.notifEmail = chk('cfgNotifEmail');
    st.preferencias.notifSms = chk('cfgNotifSms');
    st.preferencias.notifNuevoAvistamiento = chk('cfgNotifAvistamiento');
    st.preferencias.notifCambioEstatus = chk('cfgNotifEstatus');
    st.preferencias.notifBoletinRUPE = chk('cfgNotifBoletin');
    st.preferencias.idioma = val('cfgIdioma') || 'es';
    st.preferencias.alcaldiaDefault = val('cfgAlcaldiaDefault');
    st.preferencias.id_alcaldia_default = val('cfgAlcaldiaDefault');

    // Guardado inmediato si viene del botón; debounce en cambios de switch
    var delay = forceToast ? 0 : 350;
    if (saveTimer) clearTimeout(saveTimer);
    saveTimer = setTimeout(async function () {
      var token = csrf();
      var fd = new FormData();
      fd.append('notif_email', st.preferencias.notifEmail ? '1' : '0');
      fd.append('notif_sms', st.preferencias.notifSms ? '1' : '0');
      fd.append('notif_avistamiento', st.preferencias.notifNuevoAvistamiento ? '1' : '0');
      fd.append('notif_cambio_estatus', st.preferencias.notifCambioEstatus ? '1' : '0');
      fd.append('notif_boletin', st.preferencias.notifBoletinRUPE ? '1' : '0');
      fd.append('idioma', st.preferencias.idioma || 'es');
      fd.append('id_alcaldia_default', st.preferencias.alcaldiaDefault || '');
      if (token) {
        fd.append('_csrf', token);
        fd.append('_token', token);
      }
      try {
        var res = await fetch('/api/tutor/preferencias', {
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
          toast(data.message || 'No se pudieron guardar las preferencias.', 'warning');
          return;
        }
        var row =
          (data.data && data.data.preferencias) || data.preferencias || null;
        if (row) {
          st.preferencias = mapPrefsFromDb(row);
          if (global.RUPE_INIT) global.RUPE_INIT.preferencias = row;
        }
        toast('Preferencias y notificaciones guardadas.', 'success');
      } catch (err) {
        console.warn(err);
        toast('Preferencias guardadas en esta sesión.', 'info');
      }
    }, delay);
  }
  global.guardarPreferencias = guardarPreferencias;

  async function cambiarContrasenaTutor(e) {
    if (e && e.preventDefault) e.preventDefault();
    var actual = ((document.getElementById('cfgPassActual') || {}).value || '').trim();
    var nueva = ((document.getElementById('cfgPassNueva') || {}).value || '').trim();
    var confirmar = ((document.getElementById('cfgPassConfirmar') || {}).value || '').trim();
    if (!actual || !nueva || !confirmar) {
      toast('Completa los tres campos de contraseña.', 'warning');
      return false;
    }
    if (nueva.length < 8) {
      toast('La nueva contraseña debe tener al menos 8 caracteres.', 'warning');
      return false;
    }
    if (nueva !== confirmar) {
      toast('La nueva contraseña y su confirmación no coinciden.', 'warning');
      return false;
    }
    var token = csrf();
    var fd = new FormData();
    fd.append('password_actual', actual);
    fd.append('password', nueva);
    fd.append('password_confirmation', confirmar);
    if (token) {
      fd.append('_csrf', token);
      fd.append('_token', token);
    }
    var btn = document.querySelector('#formCambiarContrasena button[type="submit"]');
    if (btn) btn.disabled = true;
    try {
      var res = await fetch('/api/tutor/password', {
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
        toast(data.message || 'No se pudo cambiar la contraseña.', 'danger');
        return false;
      }
      var form = document.getElementById('formCambiarContrasena');
      if (form) form.reset();
      toast('Contraseña actualizada correctamente.', 'success');
      return true;
    } catch (err) {
      console.error(err);
      toast('Error de red al cambiar la contraseña.', 'danger');
      return false;
    } finally {
      if (btn) btn.disabled = false;
    }
  }
  global.cambiarContrasenaTutor = cambiarContrasenaTutor;

  function bind() {
    loadPrefsFromInit();
    renderizarConfiguracion();
    var form = document.getElementById('formCambiarContrasena');
    if (form && !form._rupeCfgBound) {
      form._rupeCfgBound = true;
      form.addEventListener('submit', function (ev) {
        cambiarContrasenaTutor(ev);
      });
    }
    [
      'cfgNotifEmail',
      'cfgNotifSms',
      'cfgNotifAvistamiento',
      'cfgNotifEstatus',
      'cfgNotifBoletin',
      'cfgIdioma',
      'cfgAlcaldiaDefault',
    ].forEach(function (id) {
      var el = document.getElementById(id);
      if (el && !el._rupeCfgBound) {
        el._rupeCfgBound = true;
        el.addEventListener('change', function () {
          guardarPreferencias();
        });
      }
    });
  }

  document.addEventListener('click', function (e) {
    if (
      e.target.closest('.nav-modulo[data-modulo="configuracion"]') ||
      e.target.closest('[onclick*="mostrarModuloConfiguracion"]')
    ) {
      setTimeout(function () {
        bind();
        cargarPreferenciasApi().then(function () {
          renderizarConfiguracion();
        });
      }, 250);
    }
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      setTimeout(bind, 50);
    });
  } else {
    setTimeout(bind, 50);
  }
  global.addEventListener('load', function () {
    setTimeout(bind, 50);
  });
})(typeof window !== 'undefined' ? window : globalThis);
