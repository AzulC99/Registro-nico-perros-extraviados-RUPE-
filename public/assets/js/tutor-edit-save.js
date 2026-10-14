/**
 * RUPE CDMX — Edición CRUD: abrir modal + guardar en BD + refrescar tabla
 * Única fuente de verdad (carga al final).
 */
'use strict';

(function (global) {
  function toast(msg, type) {
    if (typeof global.rupeToast === 'function') return global.rupeToast(msg, type || 'info');
    if (typeof global.mostrarToastTutor === 'function') return global.mostrarToastTutor(msg);
    var el = document.getElementById('editAlert');
    if (el) {
      el.className = 'alert alert-' + (type === 'danger' || type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'success') + ' py-2 small';
      el.textContent = msg;
      el.classList.remove('d-none');
    } else {
      alert(msg);
    }
  }

  function regs() {
    if (global.state && Array.isArray(global.state.registros)) return global.state.registros;
    if (global.RUPE_INIT && Array.isArray(global.RUPE_INIT.mascotas)) return global.RUPE_INIT.mascotas;
    return [];
  }

  function csrf() {
    return (global.RUPE_INIT && global.RUPE_INIT.csrf) || global.__CSRF__ || '';
  }

  function fillSelect(sel, list, idKey, nameKey, selectedId) {
    if (!sel) return;
    if (list && list.length) {
      var cur = sel.value;
      if (sel.options.length <= 1) {
        sel.innerHTML = '<option value="">Selecciona…</option>';
        list.forEach(function (item) {
          var id = item[idKey] != null ? item[idKey] : item.id;
          if (!id) return;
          var opt = document.createElement('option');
          opt.value = String(id);
          opt.textContent = item[nameKey] || item.nombre || String(id);
          sel.appendChild(opt);
        });
      }
    }
    if (selectedId != null && selectedId !== '' && selectedId !== 0 && selectedId !== '0') {
      sel.value = String(selectedId);
    }
  }

  function abrirModalEditar(index) {
    var list = regs();
    var reg = list[index];
    if (!reg) {
      toast('Registro no encontrado (índice ' + index + ').', 'warning');
      return;
    }
    var alertEl = document.getElementById('editAlert');
    if (alertEl) {
      alertEl.classList.add('d-none');
      alertEl.textContent = '';
    }

    var idxEl = document.getElementById('editIndex');
    if (idxEl) idxEl.value = String(index);

    var idM = reg.id || reg.id_mascota || '';
    var idEl = document.getElementById('editIdMascota');
    if (idEl) idEl.value = String(idM);

    var folioEl = document.getElementById('modalFolioId');
    if (folioEl) folioEl.textContent = '#' + (reg.folio || '');

    var nombreEl = document.getElementById('editNombre');
    if (nombreEl) nombreEl.value = reg.nombre || '';

    var razas = (global.RUPE_INIT && global.RUPE_INIT.razas) || global.__RUPE_RAZAS__ || [];
    var alcaldias = (global.RUPE_INIT && global.RUPE_INIT.alcaldias) || global.__RUPE_ALCALDIAS__ || [];
    fillSelect(document.getElementById('editRaza'), razas, 'id_raza', 'nombre', reg.id_raza);
    fillSelect(document.getElementById('editAlcaldia'), alcaldias, 'id_alcaldia', 'nombre', reg.id_alcaldia);

    var fecha = reg.fecha || reg.fecha_extravio || '';
    var fechaEl = document.getElementById('editFecha');
    if (fechaEl) fechaEl.value = fecha ? String(fecha).substring(0, 10) : '';

    var colorEl = document.getElementById('editColor');
    if (colorEl) colorEl.value = reg.color || '';
    var colEl = document.getElementById('editColonia');
    if (colEl) colEl.value = reg.colonia || '';
    var senasEl = document.getElementById('editSenas');
    if (senasEl) senasEl.value = reg.senas || reg.senas_particulares || '';

    var modalEl = document.getElementById('modalEditarRegistro');
    if (modalEl && global.bootstrap) {
      global.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else if (modalEl) {
      modalEl.classList.add('show');
      modalEl.style.display = 'block';
    }
  }

  async function guardarEdicionRegistro(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }

    var index = parseInt((document.getElementById('editIndex') || {}).value || '-1', 10);
    var list = regs();
    var reg = list[index];
    var alertEl = document.getElementById('editAlert');

    if (!reg) {
      toast('Registro no encontrado. Recarga la página.', 'danger');
      return false;
    }

    var idMascota = reg.id || reg.id_mascota || (document.getElementById('editIdMascota') || {}).value;
    idMascota = parseInt(idMascota, 10);
    if (!idMascota) {
      toast('Este folio no tiene ID en base de datos. No se puede actualizar.', 'danger');
      return false;
    }

    var nombre = ((document.getElementById('editNombre') || {}).value || '').trim();
    var idRaza = ((document.getElementById('editRaza') || {}).value || '').trim();
    var idAlc = ((document.getElementById('editAlcaldia') || {}).value || '').trim();
    var fecha = ((document.getElementById('editFecha') || {}).value || '').trim();
    var color = ((document.getElementById('editColor') || {}).value || '').trim();
    var colonia = ((document.getElementById('editColonia') || {}).value || '').trim();
    var senas = ((document.getElementById('editSenas') || {}).value || '').trim();

    if (nombre.length < 2) {
      toast('El nombre debe tener al menos 2 caracteres.', 'warning');
      return false;
    }
    if (!idRaza || idRaza === '0') {
      toast('Selecciona una raza del catálogo.', 'warning');
      return false;
    }
    if (!idAlc || idAlc === '0') {
      toast('Selecciona una alcaldía del catálogo.', 'warning');
      return false;
    }

    var token = csrf();
    if (!token) {
      toast('Sesión CSRF vacía. Recarga la página (F5) e inicia sesión de nuevo.', 'danger');
      return false;
    }

    var fd = new FormData();
    fd.append('nombre', nombre);
    fd.append('id_raza', String(parseInt(idRaza, 10)));
    fd.append('id_alcaldia', String(parseInt(idAlc, 10)));
    if (fecha) fd.append('fecha_extravio', fecha);
    fd.append('color', color);
    fd.append('colonia', colonia);
    fd.append('senas_particulares', senas);
    fd.append('_csrf', token);
    fd.append('_method', 'PUT');
    var fileInput = document.getElementById('editFoto');
    if (fileInput && fileInput.files && fileInput.files[0]) {
      fd.append('foto', fileInput.files[0]);
    }

    var btn = document.getElementById('btnGuardarEdicion');
    if (btn) btn.disabled = true;
    if (alertEl) alertEl.classList.add('d-none');

    try {
      var url = '/api/tutor/mascotas/' + encodeURIComponent(idMascota);
      var res = await fetch(url, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': token
        },
        credentials: 'same-origin',
        body: fd
      });
      var data = {};
      try {
        data = await res.json();
      } catch (_) {
        data = {};
      }

      if (!res.ok || data.success === false) {
        var msg = data.message || ('Error HTTP ' + res.status);
        if (res.status === 419) msg = 'Token CSRF inválido. Recarga la página (F5).';
        if (res.status === 401) msg = 'Sesión expirada. Vuelve a iniciar sesión.';
        toast(msg, 'danger');
        console.error('[RUPE edit]', res.status, data);
        return false;
      }

      var m = (data.data && data.data.mascota) ? data.data.mascota : {};
      var razaTxt =
        m.raza ||
        m.raza_nombre ||
        ((document.getElementById('editRaza') || {}).selectedOptions[0] || {}).textContent ||
        '';
      var alcTxt =
        m.alcaldia ||
        m.alcaldia_nombre ||
        ((document.getElementById('editAlcaldia') || {}).selectedOptions[0] || {}).textContent ||
        '';
      razaTxt = String(razaTxt).trim();
      alcTxt = String(alcTxt).trim();

      reg.nombre = m.nombre || nombre;
      reg.id = m.id || m.id_mascota || idMascota;
      reg.id_mascota = reg.id;
      reg.id_raza = m.id_raza != null ? m.id_raza : parseInt(idRaza, 10);
      reg.id_alcaldia = m.id_alcaldia != null ? m.id_alcaldia : parseInt(idAlc, 10);
      reg.raza = razaTxt;
      reg.raza_nombre = razaTxt;
      reg.alcaldia = alcTxt;
      reg.alcaldia_nombre = alcTxt;
      reg.fecha = m.fecha || m.fecha_extravio || fecha;
      reg.fecha_extravio = reg.fecha;
      reg.color = m.color != null ? m.color : color;
      reg.colonia = m.colonia != null ? m.colonia : colonia;
      reg.senas = m.senas || m.senas_particulares || senas;
      reg.senas_particulares = reg.senas;
      if (m.foto || m.foto_url) {
        reg.foto = m.foto || m.foto_url;
        reg.foto_url = reg.foto;
      }
      reg.estatus = m.estatus || reg.estatus || 'En Búsqueda';

      // Actualizar también RUPE_INIT para que al repintar no se pierda
      if (global.RUPE_INIT && Array.isArray(global.RUPE_INIT.mascotas)) {
        var found = global.RUPE_INIT.mascotas.findIndex(function (x) {
          return (x.id || x.id_mascota) == idMascota || x.folio === reg.folio;
        });
        if (found >= 0) global.RUPE_INIT.mascotas[found] = Object.assign({}, global.RUPE_INIT.mascotas[found], reg);
      }

      var modalEl = document.getElementById('modalEditarRegistro');
      if (modalEl && global.bootstrap) {
        var inst = global.bootstrap.Modal.getInstance(modalEl);
        if (inst) inst.hide();
      }

      if (typeof global.pintarTablaCrud === 'function') global.pintarTablaCrud();
      else if (typeof global.renderizarTablaCrud === 'function') global.renderizarTablaCrud();

      toast(data.message || 'Datos guardados en la base de datos.', 'success');
      return true;
    } catch (err) {
      console.error('[RUPE edit]', err);
      toast(err.message || 'Error de red al guardar.', 'danger');
      return false;
    } finally {
      if (btn) btn.disabled = false;
    }
  }

  // Exponer globalmente (sobrescribe otras definiciones)
  global.abrirModalEditar = abrirModalEditar;
  global.guardarEdicionRegistro = guardarEdicionRegistro;

  // Enlazar form y botones data-crud
  function bind() {
    var form = document.getElementById('formEditarRegistro');
    if (form) {
      form.onsubmit = function (ev) {
        guardarEdicionRegistro(ev);
        return false;
      };
    }
    document.addEventListener(
      'click',
      function (ev) {
        var btn = ev.target.closest('[data-crud="editar"]');
        if (!btn) return;
        if (!btn.closest('#tbodyCrud') && !btn.closest('#tablaCrudTutor')) return;
        ev.preventDefault();
        var idx = parseInt(btn.getAttribute('data-index') || '-1', 10);
        abrirModalEditar(idx);
      },
      true
    );
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bind);
  } else {
    bind();
  }
  global.addEventListener('load', bind);

  // ========== ELIMINAR un solo folio ==========
  function abrirModalEliminarRegistro(index) {
    var list = regs();
    var reg = list[index];
    if (!reg) {
      toast('Registro no encontrado.', 'warning');
      return;
    }
    var alertEl = document.getElementById('delAlert');
    if (alertEl) {
      alertEl.classList.add('d-none');
      alertEl.textContent = '';
    }
    var idxEl = document.getElementById('delIndex');
    if (idxEl) idxEl.value = String(index);
    var idM = reg.id || reg.id_mascota || '';
    var idEl = document.getElementById('delIdMascota');
    if (idEl) idEl.value = String(idM);
    var folioEl = document.getElementById('delFolio');
    if (folioEl) folioEl.value = String(reg.folio || '');
    var lf = document.getElementById('delLabelFolio');
    if (lf) lf.textContent = '#' + (reg.folio || '—');
    var ln = document.getElementById('delLabelNombre');
    if (ln) ln.textContent = reg.nombre || '—';
    var lr = document.getElementById('delLabelRaza');
    if (lr) lr.textContent = reg.raza || reg.raza_nombre || '—';

    var modalEl = document.getElementById('modalEliminarRegistro');
    if (modalEl && global.bootstrap) {
      global.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else if (modalEl) {
      modalEl.classList.add('show');
      modalEl.style.display = 'block';
    }
  }

  async function confirmarEliminarRegistro(forcedIndex) {
    var index = forcedIndex != null
      ? forcedIndex
      : parseInt((document.getElementById('delIndex') || {}).value || '-1', 10);
    var list = regs();
    var reg = list[index];
    var alertEl = document.getElementById('delAlert');

    // Fallback por id oculto
    if (!reg) {
      var idHidden = parseInt((document.getElementById('delIdMascota') || {}).value || '0', 10);
      var folioHidden = (document.getElementById('delFolio') || {}).value || '';
      index = list.findIndex(function (r) {
        return (r.id || r.id_mascota) == idHidden || String(r.folio) === String(folioHidden);
      });
      reg = list[index];
    }
    if (!reg) {
      toast('No se encontró el registro a eliminar.', 'danger');
      return false;
    }

    var idMascota = parseInt(reg.id || reg.id_mascota || (document.getElementById('delIdMascota') || {}).value || '0', 10);
    var folio = reg.folio || '';
    var btn = document.getElementById('btnConfirmarEliminar');
    if (btn) btn.disabled = true;

    try {
      if (idMascota > 0) {
        var token = csrf();
        if (!token) {
          toast('Sesión CSRF vacía. Recarga la página (F5).', 'danger');
          return false;
        }
        var res = await fetch('/api/tutor/mascotas/' + encodeURIComponent(idMascota) + '/eliminar', {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': token
          },
          credentials: 'same-origin',
          body: JSON.stringify({ _csrf: token })
        });
        var data = {};
        try { data = await res.json(); } catch (_) { data = {}; }
        if (!res.ok || data.success === false) {
          var msg = data.message || ('Error HTTP ' + res.status);
          if (res.status === 419) msg = 'Token CSRF inválido. Recarga (F5).';
          if (alertEl) {
            alertEl.className = 'alert alert-danger py-2 small';
            alertEl.textContent = msg;
            alertEl.classList.remove('d-none');
          }
          toast(msg, 'danger');
          return false;
        }
      }

      // Quitar SOLO este registro de state y RUPE_INIT
      if (global.state && Array.isArray(global.state.registros)) {
        global.state.registros = global.state.registros.filter(function (r, i) {
          if (idMascota > 0) {
            return (r.id || r.id_mascota) != idMascota;
          }
          return i !== index;
        });
        if (global.state.folioSeleccionado === folio) {
          global.state.folioSeleccionado = global.state.registros[0]
            ? global.state.registros[0].folio
            : '';
        }
      }
      if (global.RUPE_INIT && Array.isArray(global.RUPE_INIT.mascotas)) {
        global.RUPE_INIT.mascotas = global.RUPE_INIT.mascotas.filter(function (r) {
          if (idMascota > 0) return (r.id || r.id_mascota) != idMascota;
          return String(r.folio) !== String(folio);
        });
      }

      var modalEl = document.getElementById('modalEliminarRegistro');
      if (modalEl && global.bootstrap) {
        var inst = global.bootstrap.Modal.getInstance(modalEl);
        if (inst) inst.hide();
      }

      if (typeof global.pintarTablaCrud === 'function') global.pintarTablaCrud();
      else if (typeof global.renderizarTablaCrud === 'function') global.renderizarTablaCrud();

      toast('Folio ' + (folio ? '#' + folio : '') + ' eliminado. Los demás registros no se modificaron.', 'success');
      return true;
    } catch (err) {
      console.error('[RUPE delete]', err);
      toast(err.message || 'Error de red al eliminar.', 'danger');
      return false;
    } finally {
      if (btn) btn.disabled = false;
    }
  }

  global.abrirModalEliminarRegistro = abrirModalEliminarRegistro;
  global.confirmarEliminarRegistro = confirmarEliminarRegistro;
  global.eliminarRegistro = function (index) {
    abrirModalEliminarRegistro(index);
  };

  // Bind botón confirmar y data-crud eliminar
  function bindDelete() {
    var btn = document.getElementById('btnConfirmarEliminar');
    if (btn && !btn._rupeDelBound) {
      btn._rupeDelBound = true;
      btn.addEventListener('click', function () {
        confirmarEliminarRegistro();
      });
    }
    if (!document._rupeDelClickBound) {
      document._rupeDelClickBound = true;
      document.addEventListener(
        'click',
        function (ev) {
          var b = ev.target.closest('[data-crud="eliminar"]');
          if (!b) return;
          if (!b.closest('#tbodyCrud') && !b.closest('#tablaCrudTutor')) return;
          ev.preventDefault();
          ev.stopPropagation();
          var idx = parseInt(b.getAttribute('data-index') || '-1', 10);
          var tr = b.closest('tr');
          var list = regs();
          if (idx < 0 || idx >= list.length) {
            var folio = tr && tr.getAttribute('data-folio');
            var id = tr && tr.getAttribute('data-id');
            idx = list.findIndex(function (r) {
              return (folio && String(r.folio) === String(folio)) ||
                (id && String(r.id || r.id_mascota) === String(id));
            });
          }
          if (idx < 0) {
            toast('No se encontró el registro a eliminar.', 'warning');
            return;
          }
          abrirModalEliminarRegistro(idx);
        },
        true
      );
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindDelete);
  } else {
    bindDelete();
  }
  global.addEventListener('load', bindDelete);

})(typeof window !== 'undefined' ? window : globalThis);