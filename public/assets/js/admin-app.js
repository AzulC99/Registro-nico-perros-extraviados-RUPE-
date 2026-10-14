/**
 * RUPE CDMX — Admin App ES6
 * public/assets/js/admin-app.js
 * Validaciones · toasts · API REST admin (sin flash)
 */
'use strict';

(function (global) {
  const api = {
    async request(method, path, body) {
      const csrf = global.__CSRF__ || '';
      const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-Token': csrf,
      };
      let payload;
      if (body !== undefined && body !== '' && !(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
        payload = JSON.stringify({ ...body, _csrf: csrf });
      }
      const res = await fetch(path, {
        method,
        headers,
        body: method === 'GET' || method === 'HEAD' ? undefined : payload || '{}',
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

  function toast(mensaje, tipo = 'success') {
    const t = tipo === 'error' ? 'danger' : (tipo || 'success');
    if (typeof global.rupeToast === 'function') {
      try { global.rupeToast(mensaje, t); return; } catch (_) {}
    }
    if (typeof global.mostrarToast === 'function') {
      try {
        global.mostrarToast(mensaje, t);
        return;
      } catch (_) {}
    }
    let el = document.getElementById('rupeAdminToast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'rupeAdminToast';
      el.style.cssText =
        'position:fixed;bottom:24px;right:24px;z-index:99999;min-width:260px;max-width:400px;padding:14px 18px;border-radius:12px;color:#fff;font-size:14px;box-shadow:0 8px 24px rgba(0,0,0,.2);opacity:0;transition:opacity .25s;';
      document.body.appendChild(el);
    }
    const map = { success: '#198754', danger: '#dc3545', error: '#dc3545', info: '#0dcaf0', warning: '#e8a33d' };
    el.style.background = map[tipo] || map.success;
    el.style.color = tipo === 'info' || tipo === 'warning' ? '#111' : '#fff';
    el.textContent = mensaje;
    el.style.opacity = '1';
    clearTimeout(el._t);
    el._t = setTimeout(() => (el.style.opacity = '0'), 3800);
  }

  function mapEspecie(val) {
    const v = String(val || '').toLowerCase();
    if (v.includes('fel') || v.includes('gato')) return 'gato';
    if (v.includes('otro')) return 'otro';
    return 'perro';
  }

  // ---------- RAZAS ----------
  function mapEspecieLabel(e) {
    return 'perro';
  }

  function formatFechaRaza(v) {
    if (!v) return '—';
    try {
      const d = new Date(v);
      if (!isNaN(d.getTime())) {
        return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
      }
    } catch (_) {}
    return String(v).slice(0, 10);
  }

  async function loadRazas() {
    let list = [];
    try {
      const res = await api.get('/api/admin/razas');
      const raw = res && res.data !== undefined ? res.data : res;
      if (Array.isArray(raw)) {
        list = raw;
      } else if (raw && Array.isArray(raw.razas)) {
        list = raw.razas;
      } else if (raw && Array.isArray(raw.items)) {
        list = raw.items;
      } else if (raw && typeof raw === 'object') {
        const keys = Object.keys(raw).filter(function (k) { return /^\d+$/.test(k); });
        if (keys.length) {
          list = keys.sort(function (a, b) { return Number(a) - Number(b); }).map(function (k) { return raw[k]; });
        }
      }
      console.info('[RUPE Admin] loadRazas count=', list.length);
    } catch (err) {
      console.warn('[RUPE Admin] loadRazas', err);
      if (global.RUPE_ADMIN_INIT && Array.isArray(global.RUPE_ADMIN_INIT.razas)) {
        list = global.RUPE_ADMIN_INIT.razas;
      }
      toast(err.message || 'No se pudieron cargar las razas.', 'warning');
    }
    if (!list.length && global.RUPE_ADMIN_INIT && Array.isArray(global.RUPE_ADMIN_INIT.razas)) {
      list = global.RUPE_ADMIN_INIT.razas;
    }
    list = (list || []).filter(function (r) {
      if (!r) return false;
      var idNum = parseInt(r.id_raza != null ? r.id_raza : r.id, 10);
      return isFinite(idNum) && idNum > 0;
    }).map(function (r) {
      var idNum = parseInt(r.id_raza != null ? r.id_raza : r.id, 10) || 0;
      var esp = 'perro';
      // Tamaño: razas.tamano → mascotas (SELECT) → —
      var tam = String(r.tamano || r.tamano_mascota || r.tamano_raza || r.tamaño || '').trim();
      if (!tam || tam === 'null' || tam === 'undefined' || tam === 'NaN' || tam === '—') {
        tam = String(r.tamano_mascota || '').trim();
      }
      if (!tam || tam === 'null' || tam === 'undefined') tam = '—';
      var regs = parseInt(r.registros != null ? r.registros : 0, 10);
      if (!isFinite(regs) || regs < 0) regs = 0;
      return {
        id: idNum,
        id_raza: idNum,
        codigo: 'RZ-' + String(idNum).padStart(2, '0'),
        nombre: String(r.nombre || '').trim(),
        especie: esp,
        especie_label: 'perro',
        tamano: tam,
        tamano_mascota: String(r.tamano_mascota || '').trim(),
        activo: r.activo == 1 || r.activo === true || r.activo === '1' ? 1 : 0,
        created_at: String(r.created_at || r.fecha_alta || ''),
        registros: regs,
      };
    });
    // Quitar duplicados por id y por nombre
    var seenId = {};
    var seenNom = {};
    list = list.filter(function (r) {
      if (!r.id_raza || seenId[r.id_raza]) return false;
      var nk = (r.nombre || '').toLowerCase();
      if (nk && seenNom[nk]) return false;
      seenId[r.id_raza] = true;
      if (nk) seenNom[nk] = true;
      return true;
    });
    global.__RUPE_RAZAS__ = list;
    const cnt = document.getElementById('contadorRazas');
    if (cnt) cnt.textContent = String(list.length);
    const tbody = document.getElementById('tbodyRazas') || document.querySelector('#tablaRazas tbody');
    if (!tbody) return list;
    if (!list.length) {
      tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No hay razas en la base de datos.</td></tr>';
      return list;
    }
    tbody.innerHTML = list.map(function (r) {
      const id = parseInt(r.id_raza || r.id, 10) || 0;
      if (id <= 0) return '';
      const codigo = 'RZ-' + String(id).padStart(2, '0');
      const nombre = esc(r.nombre || '');
      const especieLbl = 'perro';
      var tamRaw = String(r.tamano || r.tamano_mascota || '').trim();
      if (!tamRaw || tamRaw === 'null' || tamRaw === 'undefined') tamRaw = '—';
      const tamano = esc(tamRaw);
      const regs = parseInt(r.registros, 10) || 0;
      const activo = r.activo === 1 || r.activo === true || r.activo === '1';
      const estado = activo ? 'Activo' : 'Inactivo';
      const fecha = formatFechaRaza(r.created_at) || '—';
      return (
        '<tr data-id="' + id + '" data-estado="' + estado + '" data-tamano="' + tamano + '" data-especie="perro" data-nombre="' + nombre.replace(/"/g, '&quot;') + '">' +
        '<td class="ps-4 fw-bold">' + codigo + '</td>' +
        '<td>' + nombre + '</td>' +
        '<td>' + especieLbl + '</td>' +
        '<td>' + tamano + '</td>' +
        '<td class="text-center">' + regs + '</td>' +
        '<td><span class="badge ' + (activo ? 'bg-success' : 'bg-secondary') + '">' + estado + '</span></td>' +
        '<td>' + esc(fecha) + '</td>' +
        '<td class="text-end pe-4 text-nowrap">' +
        '<button type="button" class="btn btn-sm btn-outline-primary me-1" data-action="edit-raza" data-id="' + id + '" title="Editar"><i class="bi bi-pencil"></i></button>' +
        '<button type="button" class="btn btn-sm btn-outline-secondary" data-action="toggle-raza" data-id="' + id + '" data-activo="' + (activo ? 1 : 0) + '" title="Activar/Desactivar"><i class="bi bi-toggle-on"></i></button>' +
        '</td></tr>'
      );
    }).filter(Boolean).join('');
    return list;
  }

  async function loadAlcaldias() {
    let list = [];
    try {
      const res = await api.get('/api/admin/alcaldias');
      list = Array.isArray(res.data) ? res.data : [];
    } catch (err) {
      console.warn('[RUPE Admin] loadAlcaldias', err);
      list = Array.isArray(global.__RUPE_ALCALDIAS__) ? global.__RUPE_ALCALDIAS__ : [];
      if (!list.length && global.RUPE_ADMIN_INIT && Array.isArray(global.RUPE_ADMIN_INIT.alcaldias)) {
        list = global.RUPE_ADMIN_INIT.alcaldias;
      }
      toast(err.message || 'No se pudieron cargar las alcaldías.', 'warning');
    }
    global.__RUPE_ALCALDIAS__ = list;
    const tbody = document.querySelector('#tablaAlcaldias tbody');
    if (tbody) {
      if (!list.length) {
        tbody.innerHTML =
          '<tr><td colspan="8" class="text-center text-muted py-4">No hay alcaldías. Usa <strong>Agregar Alcaldía</strong>.</td></tr>';
      } else {
        tbody.innerHTML = list
          .map(function (a) {
            const id = a.id_alcaldia != null ? a.id_alcaldia : a.id;
            const clave = a.clave || ('CDMX-' + String(id).padStart(2, '0'));
            const nombre = a.nombre || '';
            const zona = a.zona || '—';
            const cp = a.cp_base || a.codigo_postal || '—';
            const casos = a.casos != null ? a.casos : a.registros != null ? a.registros : 0;
            const activo = a.activo == 1 || a.activo === true || a.activo === '1';
            const estado = activo ? 'Activo' : 'Inactivo';
            let fecha = '—';
            if (a.created_at) {
              try {
                const d = new Date(a.created_at);
                if (!isNaN(d.getTime())) {
                  fecha = d.toLocaleDateString('es-MX', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                  });
                } else fecha = String(a.created_at).slice(0, 10);
              } catch (_) {
                fecha = String(a.created_at).slice(0, 10);
              }
            }
            return (
              '<tr data-id="' +
              id +
              '" data-estado="' +
              estado +
              '" data-zona="' +
              esc(zona) +
              '" data-nombre="' +
              esc(nombre) +
              '">' +
              '<td class="ps-4 fw-bold">' +
              esc(clave) +
              '</td>' +
              '<td>' +
              esc(nombre) +
              '</td>' +
              '<td>' +
              esc(zona) +
              '</td>' +
              '<td>' +
              esc(cp) +
              '</td>' +
              '<td class="text-center">' +
              casos +
              '</td>' +
              '<td><span class="badge ' +
              (activo ? 'bg-success' : 'bg-secondary') +
              '">' +
              estado +
              '</span></td>' +
              '<td>' +
              fecha +
              '</td>' +
              '<td class="text-end pe-4 text-nowrap">' +
              '<button class="btn btn-sm btn-outline-primary me-1" type="button" data-action="edit-alcaldia" data-id="' +
              id +
              '" title="Editar"><i class="bi bi-pencil"></i></button>' +
              '<button class="btn btn-sm btn-outline-secondary" type="button" data-action="toggle-alcaldia" data-id="' +
              id +
              '" data-activo="' +
              (activo ? 1 : 0) +
              '" title="Activar/Desactivar"><i class="bi bi-toggle-on"></i></button>' +
              '</td></tr>'
            );
          })
          .join('');
      }
    }
    const cont = document.getElementById('contadorAlcaldias');
    if (cont) cont.textContent = String(list.length);
    if (typeof global.aplicarFiltrosAlcaldias === 'function') {
      try {
        global.aplicarFiltrosAlcaldias(false);
      } catch (_) {}
    }
    return list;
  }

  function siguienteIdCdmx() {
    var max = 0;
    (global.__RUPE_ALCALDIAS__ || []).forEach(function (a) {
      var id = parseInt(a.id_alcaldia != null ? a.id_alcaldia : a.id, 10) || 0;
      if (id > max) max = id;
      var c = String(a.clave || '');
      var m = c.match(/(\d+)/);
      if (m) {
        var n = parseInt(m[1], 10) || 0;
        if (n > max) max = n;
      }
    });
    document.querySelectorAll('#tablaAlcaldias tbody tr[data-id]').forEach(function (tr) {
      var id = parseInt(tr.getAttribute('data-id'), 10) || 0;
      if (id > max) max = id;
    });
    return 'CDMX-' + String(max + 1).padStart(2, '0');
  }
  global.siguienteIdCdmx = siguienteIdCdmx;

  async function guardarAlcaldia(e) {
    if (e) e.preventDefault();
    const idHidden = document.getElementById('inputAlcaldiaDbId')?.value;
    const nombre = document.getElementById('inputAlcaldiaNombre')?.value?.trim() || '';
    let clave =
      document.getElementById('inputAlcaldiaId')?.value?.trim() ||
      document.getElementById('inputAlcaldiaClave')?.value?.trim() ||
      '';
    if (!clave || clave === '(automático)') {
      clave = siguienteIdCdmx();
    }
    const zona = document.getElementById('inputAlcaldiaZona')?.value?.trim() || '';
    const cp =
      document.getElementById('inputAlcaldiaCP')?.value?.trim() ||
      document.getElementById('inputAlcaldiaCodigoPostal')?.value?.trim() ||
      '';
    if (typeof global.rupeValidarAlcaldia === 'function' && !global.rupeValidarAlcaldia(true)) {
      toast('Completa nombre, zona y CP de 5 dígitos.', 'warning');
      return;
    }
    if (nombre.length < 2) {
      toast('El nombre de la alcaldía es obligatorio.', 'warning');
      return;
    }
    if (!zona) {
      toast('Selecciona la zona.', 'warning');
      return;
    }
    if (!/^\d{5}$/.test(String(cp).replace(/\D/g, ''))) {
      toast('Código postal: exactamente 5 dígitos.', 'warning');
      return;
    }
    try {
      const payload = { nombre, clave, zona, cp_base: cp, activo: 1 };
      if (idHidden) {
        await api.put(`/api/admin/alcaldias/${idHidden}`, payload);
        toast(`Alcaldía "${nombre}" actualizada · ${clave}`, 'success');
      } else {
        await api.post('/api/admin/alcaldias', payload);
        toast(`Alcaldía "${nombre}" registrada · ${clave}`, 'success');
      }
      document.getElementById('formAlcaldia')?.reset();
      const modal = document.getElementById('modalAlcaldia');
      if (modal && global.bootstrap) {
        const inst = bootstrap.Modal.getInstance(modal);
        if (inst) inst.hide();
      }
      await loadAlcaldias();
    } catch (err) {
      toast(err.message || 'No se pudo guardar la alcaldía.', 'error');
    }
  }

  function aplicarFiltrosAlcaldias(showToast) {
    const tabla = document.getElementById('tablaAlcaldias');
    if (!tabla) return;
    const q = (document.getElementById('filtroAlcBuscar')?.value || '').trim().toLowerCase();
    const estado = document.getElementById('filtroAlcEstado')?.value || '';
    const zona = document.getElementById('filtroAlcZona')?.value || '';
    let visible = 0;
    tabla.querySelectorAll('tbody tr').forEach(function (tr) {
      if (tr.querySelector('td[colspan]')) {
        tr.style.display = '';
        return;
      }
      const txt = (tr.getAttribute('data-nombre') || tr.textContent || '').toLowerCase();
      const est = tr.getAttribute('data-estado') || '';
      const z = tr.getAttribute('data-zona') || '';
      let ok = true;
      if (q && txt.indexOf(q) < 0) ok = false;
      if (estado && est !== estado) ok = false;
      if (zona && z !== zona) ok = false;
      tr.style.display = ok ? '' : 'none';
      if (ok) visible++;
    });
    const cont = document.getElementById('contadorAlcaldias');
    if (cont) cont.textContent = String(visible);
    if (showToast !== false) toast('Filtro: ' + visible + ' alcaldía(s).', 'info');
  }
  global.aplicarFiltrosAlcaldias = aplicarFiltrosAlcaldias;
  global.filtrarCatalogoPorZona = function (tablaId, val) {
    const el = document.getElementById('filtroAlcZona');
    if (el) el.value = val || '';
    aplicarFiltrosAlcaldias(true);
  };

  // ---------- USUARIOS ----------
  async function loadUsuarios() {
    let list = [];
    try {
      const res = await api.get('/api/admin/usuarios');
      list = Array.isArray(res.data)
        ? res.data
        : Array.isArray(res.data?.usuarios)
          ? res.data.usuarios
          : [];
    } catch (e) {
      console.warn('[RUPE Admin] loadUsuarios', e);
      list = Array.isArray(global.__RUPE_USUARIOS__) ? global.__RUPE_USUARIOS__ : [];
      if (!list.length && Array.isArray(global.RUPE_ADMIN_INIT?.usuarios)) {
        list = global.RUPE_ADMIN_INIT.usuarios;
      }
    }
    global.__RUPE_USUARIOS__ = list;
    const tbody =
      document.querySelector('#tablaUsuarios tbody') ||
      document.querySelector('#modulo-usuarios table tbody');
    if (tbody) {
      if (!list.length) {
        tbody.innerHTML =
          '<tr><td colspan="7" class="text-center text-muted py-4">No hay usuarios. Agrega un administrador.</td></tr>';
      } else {
        tbody.innerHTML = list
          .map(function (u) {
            const id = u.id_usuario != null ? u.id_usuario : u.id;
            const activo = u.activo == 1 || u.activo === true || u.activo === '1';
            const rol = String(u.rol || '');
            const estado = activo ? 'Activo' : 'Inactivo';
            return (
              '<tr data-id="' +
              id +
              '" data-rol="' +
              esc(rol) +
              '" data-estado="' +
              estado +
              '" data-nombre="' +
              esc(u.nombre || '') +
              '">' +
              '<td class="ps-4">' +
              id +
              '</td>' +
              '<td>' +
              esc(u.nombre || '') +
              '</td>' +
              '<td class="small">' +
              esc(u.correo || '') +
              '</td>' +
              '<td class="small">' +
              esc(u.telefono || '—') +
              '</td>' +
              '<td><span class="badge bg-dark">' +
              esc(rol) +
              '</span></td>' +
              '<td><span class="badge ' +
              (activo ? 'bg-success' : 'bg-secondary') +
              '">' +
              estado +
              '</span></td>' +
              '<td class="text-end pe-4">' +
              '<button class="btn btn-sm btn-outline-secondary" type="button" data-action="toggle-usuario" data-id="' +
              id +
              '" data-activo="' +
              (activo ? 1 : 0) +
              '">' +
              (activo ? 'Desactivar' : 'Activar') +
              '</button></td></tr>'
            );
          })
          .join('');
      }
    }
    const cont = document.getElementById('contadorUsuarios');
    if (cont) cont.textContent = String(list.length);
    if (typeof global.aplicarFiltrosUsuarios === 'function') {
      try {
        global.aplicarFiltrosUsuarios(false);
      } catch (_) {}
    }
    return list;
  }

  function generarPasswordAdmin() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$';
    let p = '';
    for (let i = 0; i < 10; i++) {
      p += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    p += 'A1!';
    return p;
  }
  global.generarPasswordAdmin = generarPasswordAdmin;

  function abrirModalAdmin() {
    const form = document.getElementById('formNuevoAdmin');
    if (form) form.reset();
    const modal = document.getElementById('modalNuevoAdmin');
    if (modal && global.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(modal).show();
    } else if (modal) {
      modal.classList.add('show');
      modal.style.display = 'block';
    }
  }
  global.abrirModalAdmin = abrirModalAdmin;
  global.abrirModalCrear = function () {
    abrirModalAdmin();
  };

  async function guardarNuevoAdmin(e) {
    if (e && e.preventDefault) e.preventDefault();
    if (typeof global.rupeValidarNuevoAdmin === 'function' && !global.rupeValidarNuevoAdmin()) {
      toast('Completa nombre (1 o 2 nombres), correo y teléfono de 10 dígitos.', 'warning');
      return false;
    }
    const nombre = (document.getElementById('inputAdminNombre')?.value || '').trim().replace(/\s+/g, ' ');
    const correo = (document.getElementById('inputAdminCorreo')?.value || '').trim();
    const telefono = String(document.getElementById('inputAdminTelefono')?.value || '').replace(/\D/g, '');
    const reNom = /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+(?:\s+[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+)?$/;
    if (!reNom.test(nombre)) {
      toast('Nombre: uno o dos nombres, solo letras (sin números).', 'warning');
      document.getElementById('inputAdminNombre')?.focus();
      return false;
    }
    if (!/[^\s@]+@[^\s@]+\.[^\s@]{2,}/.test(correo)) {
      toast('Correo no válido.', 'warning');
      document.getElementById('inputAdminCorreo')?.focus();
      return false;
    }
    if (!/^\d{10}$/.test(telefono)) {
      toast('Teléfono: exactamente 10 dígitos.', 'warning');
      document.getElementById('inputAdminTelefono')?.focus();
      return false;
    }
    const btn = document.getElementById('btnCrearAdminSubmit');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando…';
    }
    try {
      const res = await api.post('/api/admin/usuarios', {
        nombre,
        correo,
        telefono,
        rol: 'administrador',
      });
      const payload = res.data || {};
      const mailOk = payload.mail_enviado === true;
      toast(
        mailOk
          ? 'Administrador creado. Contraseña enviada a ' + (payload.correo || correo)
          : res.message || 'Administrador creado (revisa el correo SMTP).',
        mailOk ? 'success' : 'warning'
      );
      // Recargar para ver el registro en la tabla
      setTimeout(function () {
        window.location.reload();
      }, 900);
    } catch (err) {
      toast(err.message || 'No se pudo crear el administrador.', 'error');
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Crear y enviar correo';
      }
    }
    return false;
  }
  global.guardarNuevoAdmin = guardarNuevoAdmin;

  function aplicarFiltrosUsuarios(showToast) {
    const tabla = document.getElementById('tablaUsuarios');
    if (!tabla) return;
    const q = (document.getElementById('filtroUserBuscar')?.value || '').trim().toLowerCase();
    const rol = document.getElementById('filtroUserRol')?.value || '';
    const estado = document.getElementById('filtroUserEstado')?.value || '';
    let visible = 0;
    tabla.querySelectorAll('tbody tr').forEach(function (tr) {
      if (tr.querySelector('td[colspan]')) {
        tr.style.display = '';
        return;
      }
      const txt = (tr.getAttribute('data-nombre') || tr.textContent || '').toLowerCase();
      const r = tr.getAttribute('data-rol') || '';
      const e = tr.getAttribute('data-estado') || '';
      let ok = true;
      if (q && txt.indexOf(q) < 0) ok = false;
      if (rol && r !== rol) ok = false;
      if (estado && e !== estado) ok = false;
      tr.style.display = ok ? '' : 'none';
      if (ok) visible++;
    });
    const cont = document.getElementById('contadorUsuarios');
    if (cont) cont.textContent = String(visible);
    if (showToast !== false) toast('Filtro: ' + visible + ' usuario(s).', 'info');
  }
  global.aplicarFiltrosUsuarios = aplicarFiltrosUsuarios;


  // ---------- REPORTES ----------
  async function loadReportes() {
    let mascotas = [];
    let stats = {};
    let porAlc = [];
    try {
      const res = await api.get('/api/admin/reportes');
      const raw = res.data || {};
      mascotas = Array.isArray(raw.mascotas) ? raw.mascotas : (Array.isArray(raw.items) ? raw.items : (Array.isArray(raw) ? raw : []));
      stats = raw.stats || {};
      porAlc = raw.por_alcaldia || [];
      if (raw.database) global.__RUPE_DB__ = raw.database;
    } catch (err) {
      console.warn('[RUPE Admin] loadReportes', err);
      // Fallback: datos inyectados por PHP (solo BD)
      if (Array.isArray(global.__RUPE_REPORTES__) && global.__RUPE_REPORTES__.length) {
        mascotas = global.__RUPE_REPORTES__;
      } else {
        toast(err.message || 'No se pudieron cargar expedientes desde la BD.', 'warning');
      }
    }
    // Nunca inventar filas: solo lo que viene de SELECT
    global.__RUPE_REPORTES__ = mascotas;
    global.__RUPE_STATS__ = stats;
    global.__RUPE_POR_ALCALDIA__ = porAlc;
    if (typeof global.cargarReportesDesdeBD === 'function') {
      try { global.cargarReportesDesdeBD(mascotas); } catch (_) {}
    }
    const setText = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = String(val ?? 0);
    };
    setText('kpiTotalReportes', stats.total != null ? stats.total : mascotas.length);
    setText('kpiEnBusqueda', stats['En Búsqueda']);
    setText('kpiResguardo', stats['En Resguardo']);
    setText('kpiLocalizado', stats['Localizado']);
    if (typeof refreshCalculosFromDB === 'function') {
      try { refreshCalculosFromDB(); } catch (_) {}
    }
    return { mascotas, stats };
  }

  // ---------- AUDITORÍA ----------
  async function loadAuditoria() {
    // Preferir datos inyectados por PHP (BD)
    let list = Array.isArray(global.__RUPE_AUDITORIA__) ? global.__RUPE_AUDITORIA__ : [];
    try {
      const res = await api.get('/api/admin/auditoria');
      if (Array.isArray(res.data) && res.data.length) {
        list = res.data;
      } else if (res.data && Array.isArray(res.data.list)) {
        list = res.data.list;
      }
    } catch (e) {
      console.warn('[RUPE Admin] auditoria API', e);
    }
    global.__RUPE_AUDITORIA__ = list;
    const tbody = document.getElementById('tbodyAuditoria')
      || document.querySelector('#tablaAuditoria tbody')
      || document.querySelector('#modulo-auditoria table tbody');
    if (!tbody) return list;

    if (!list.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-5">No hay registros de auditoría.</td></tr>';
      return list;
    }

    function moduloDesdeAccion(accion) {
      const a = String(accion || '').toLowerCase();
      const map = {
        login_ok: 'Autenticación', login_fallido: 'Autenticación', login_cuenta_inactiva: 'Autenticación',
        logout: 'Autenticación', registro_tutor: 'Registro', password_reset_solicitado: 'Seguridad',
        password_reset_ok: 'Seguridad', password_change: 'Seguridad', perfil_actualizar: 'Perfil tutor',
        mascota_crear: 'Mascotas', mascota_actualizar: 'Mascotas', mascota_eliminar: 'Mascotas',
        seguimiento_crear: 'Seguimiento', ayuda_contacto: 'Ayuda', usuario_crear_admin: 'Usuarios',
        usuario_actualizar: 'Usuarios', alcaldia_crear: 'Alcaldías', alcaldia_actualizar: 'Alcaldías',
        config_actualizar: 'Configuración', raza_crear: 'Razas', raza_actualizar: 'Razas'
      };
      if (map[a]) return map[a];
      if (a.indexOf('login') !== -1 || a.indexOf('logout') !== -1) return 'Autenticación';
      if (a.indexOf('mascota') !== -1) return 'Mascotas';
      if (a.indexOf('usuario') !== -1) return 'Usuarios';
      return 'Sistema';
    }

    function detalleDesdeAccion(accion, detalleRaw) {
      if (detalleRaw && typeof detalleRaw === 'string' && detalleRaw.trim() !== '') {
        try {
          const j = JSON.parse(detalleRaw);
          if (j && typeof j === 'object') {
            if (j.evento) return String(j.evento);
            return Object.keys(j).map(function (k) { return k + ': ' + j[k]; }).join(' · ');
          }
        } catch (_) {
          return detalleRaw;
        }
      }
      if (detalleRaw && typeof detalleRaw === 'object') {
        if (detalleRaw.evento) return String(detalleRaw.evento);
        try {
          return Object.keys(detalleRaw).map(function (k) { return k + ': ' + detalleRaw[k]; }).join(' · ');
        } catch (_) {}
      }
      const labels = {
        login_ok: 'Inicio de sesión exitoso',
        login_fallido: 'Intento de acceso con credenciales incorrectas',
        login_cuenta_inactiva: 'Intento de acceso con cuenta inactiva',
        logout: 'Cierre de sesión',
        registro_tutor: 'Alta de cuenta tutor',
        password_reset_solicitado: 'Solicitud de restablecimiento de contraseña',
        password_reset_ok: 'Contraseña restablecida correctamente',
        password_change: 'Cambio de contraseña desde el panel',
        perfil_actualizar: 'Actualización de datos de perfil',
        mascota_crear: 'Registro de mascota extraviada',
        mascota_actualizar: 'Actualización de ficha de mascota',
        mascota_eliminar: 'Eliminación de registro de mascota',
        seguimiento_crear: 'Nuevo avistamiento / seguimiento GPS',
        ayuda_contacto: 'Mensaje de ayuda enviado',
        usuario_crear_admin: 'Alta de usuario administrador',
        usuario_actualizar: 'Actualización de usuario',
        alcaldia_crear: 'Alta de alcaldía en catálogo',
        alcaldia_actualizar: 'Actualización de alcaldía',
        config_actualizar: 'Cambio de configuración del sistema'
      };
      const a = String(accion || '').toLowerCase();
      return labels[a] || ('Evento: ' + (accion || 'sin acción'));
    }

    function badgeClass(accion) {
      const a = String(accion || '').toLowerCase();
      if (a.indexOf('login') !== -1 || a.indexOf('auth') !== -1) return 'bg-primary';
      if (a.indexOf('logout') !== -1) return 'bg-secondary';
      if (a.indexOf('crear') !== -1 || a.indexOf('insert') !== -1 || a.indexOf('create') !== -1) return 'bg-success';
      if (a.indexOf('eliminar') !== -1 || a.indexOf('delete') !== -1) return 'bg-danger';
      if (a.indexOf('actualizar') !== -1 || a.indexOf('update') !== -1 || a.indexOf('estatus') !== -1) return 'bg-warning text-dark';
      return 'bg-secondary';
    }

    tbody.innerHTML = list
      .map(function (a) {
        const id = a.id_auditoria || a.id || '—';
        const fecha = a.created_at || '';
        let usuario = a.usuario_nombre || a.usuario_correo || '';
        if (!usuario && a.id_usuario) usuario = 'Usuario #' + a.id_usuario;
        if (!usuario) usuario = 'Sistema';
        const accion = a.accion || '';
        let modulo = a.modulo || a.tabla || '';
        if (!modulo || modulo === '—' || modulo === 'sesiones') {
          modulo = moduloDesdeAccion(accion);
        }
        const ip = a.ip || '—';
        const detalle = a.detalle_texto || detalleDesdeAccion(accion, a.detalle);
        return (
          '<tr data-accion="' + esc(accion) + '" data-usuario="' + esc(usuario) + '" data-ip="' + esc(ip) + '">' +
          '<td class="ps-4 fw-bold">#' + esc(String(id)) + '</td>' +
          '<td class="small">' + esc(fecha) + '</td>' +
          '<td>' + esc(usuario) + '</td>' +
          '<td><span class="badge ' + badgeClass(accion) + '">' + esc(accion) + '</span></td>' +
          '<td>' + esc(modulo) + '</td>' +
          '<td><code class="small">' + esc(ip) + '</code></td>' +
          '<td class="pe-4 text-muted small">' + esc(detalle) + '</td>' +
          '</tr>'
        );
      })
      .join('');
    return list;
  }

  // ---------- CONFIG ----------
  async function loadConfig() {
    const res = await api.get('/api/admin/config');
    const map = res.data || {};
    global.__RUPE_CONFIG__ = map;
    Object.entries(map).forEach(([k, v]) => {
      const el = document.querySelector(`[data-config="${k}"], #cfg_${k}`);
      if (!el) return;
      if (el.type === 'checkbox') el.checked = !!v;
      else el.value = v;
    });
    return map;
  }

  async function guardarConfigClave(clave, valor, tipo = 'string') {
    if (!clave) {
      toast('Clave de configuración requerida.', 'warning');
      return;
    }
    try {
      await api.post('/api/admin/config', { clave, valor, tipo });
      toast('Configuración guardada.', 'success');
      await loadConfig();
    } catch (err) {
      toast(err.message || 'No se pudo guardar.', 'error');
    }
  }

  function guardarPreferenciasAdmin() {
    // Si hay campos data-config, guardar cada uno
    const nodes = document.querySelectorAll('[data-config]');
    if (!nodes.length) {
      toast('Preferencias actualizadas (local).', 'info');
      return;
    }
    Promise.all(
      Array.from(nodes).map((el) => {
        const clave = el.getAttribute('data-config');
        const valor = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
        const tipo = el.type === 'checkbox' ? 'bool' : el.type === 'number' ? 'int' : 'string';
        return api.post('/api/admin/config', { clave, valor, tipo });
      })
    )
      .then(() => toast('Preferencias guardadas.', 'success'))
      .catch((err) => toast(err.message || 'Error al guardar preferencias.', 'error'));
  }

  // ---------- AYUDA FAQ ----------
  async function loadAyuda() {
    const res = await api.get('/api/admin/ayuda');
    const list = res.data || [];
    global.__RUPE_AYUDA__ = list;
    return list;
  }

  async function guardarAyuda(e) {
    if (e) e.preventDefault();
    const id = document.getElementById('inputFaqId')?.value;
    const pregunta = document.getElementById('inputFaqPregunta')?.value?.trim() || '';
    const respuesta = document.getElementById('inputFaqRespuesta')?.value?.trim() || '';
    const categoria = document.getElementById('inputFaqCategoria')?.value?.trim() || 'general';
    if (pregunta.length < 3 || respuesta.length < 3) {
      toast('Pregunta y respuesta son obligatorias.', 'warning');
      return;
    }
    try {
      if (id) await api.put(`/api/admin/ayuda/${id}`, { pregunta, respuesta, categoria });
      else await api.post('/api/admin/ayuda', { pregunta, respuesta, categoria });
      toast('FAQ guardada.', 'success');
      await loadAyuda();
    } catch (err) {
      toast(err.message || 'No se pudo guardar la FAQ.', 'error');
    }
  }

  function esc(s) {
    return String(s ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // Delegación de eventos tabla
  document.addEventListener('click', async (ev) => {
    const btn = ev.target.closest('[data-action]');
    if (!btn) return;
    const action = btn.getAttribute('data-action');
    const id = parseInt(btn.getAttribute('data-id'), 10) || 0;
    try {
      if (action === 'toggle-usuario') {
        const id = parseInt(btn.getAttribute('data-id'), 10) || 0;
        const activo = btn.getAttribute('data-activo') === '1' ? 0 : 1;
        try {
          const res = await api.patch('/api/admin/usuarios/' + id, { activo });
          const mailOk = res.data && res.data.mail_enviado === true;
          toast(
            res.message ||
              (activo
                ? 'Usuario activado' + (mailOk ? ' · correo enviado' : '')
                : 'Usuario desactivado' + (mailOk ? ' · correo enviado' : '')),
            mailOk ? 'success' : 'info'
          );
          await loadUsuarios();
        } catch (err) {
          toast(err.message || 'No se pudo actualizar el usuario.', 'error');
        }
        return;
      }
      if (action === 'toggle-raza') {
        const activo = btn.getAttribute('data-activo') === '1' ? 0 : 1;
        await api.put(`/api/admin/razas/${id}`, { activo });
        toast('Estatus de raza actualizado', 'info');
        await loadRazas();
      }
      if (action === 'toggle-alcaldia') {
        const activo = btn.getAttribute('data-activo') === '1' ? 0 : 1;
        await api.put(`/api/admin/alcaldias/${id}`, { activo });
        toast('Estatus de alcaldía actualizado', 'info');
        await loadAlcaldias();
      }
      if (action === 'edit-raza') {
        const r = (global.__RUPE_RAZAS__ || []).find((x) => String(x.id_raza) === String(id));
        if (!r) {
          toast('No se encontró la raza en la lista cargada.', 'warning');
          return;
        }
        let hid = document.getElementById('inputRazaId');
        if (!hid) {
          hid = document.createElement('input');
          hid.type = 'hidden';
          hid.id = 'inputRazaId';
          const form = document.getElementById('formRaza');
          if (form) form.appendChild(hid);
        }
        hid.value = r.id_raza;
        const cod = document.getElementById('inputRazaCodigo');
        if (cod) cod.value = 'RZ-' + String(r.id_raza).padStart(2, '0');
        const n = document.getElementById('inputRazaNombre');
        if (n) n.value = r.nombre || '';
        const e = document.getElementById('inputRazaEspecie');
        if (e) {
          let esp = String(r.especie || 'perro').toLowerCase();
          if (esp === 'canino') esp = 'perro';
          if (esp === 'felino') esp = 'gato';
          e.value = esp;
        }
        const t = document.getElementById('inputRazaTamano');
        if (t) {
          var tv = String(r.tamano || r.tamano_mascota || r.tamano_raza || '').trim();
          if (tv === '—' || tv === '-' || tv === 'null') tv = '';
          // asegurar opción existe
          if (tv && !Array.from(t.options).some(function(o){ return o.value === tv; })) {
            var o = document.createElement('option'); o.value = tv; o.textContent = tv; t.appendChild(o);
          }
          t.value = tv;
        }
        const titulo = document.getElementById('modalRazaTitulo');
        if (titulo) titulo.textContent = 'Editar Raza';
        const modal = document.getElementById('modalRaza');
        if (modal && global.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
      }
      if (action === 'edit-alcaldia') {
        const a = (global.__RUPE_ALCALDIAS__ || []).find((x) => String(x.id_alcaldia) === String(id));
        if (!a) return;
        let hid = document.getElementById('inputAlcaldiaDbId');
        if (!hid) {
          hid = document.createElement('input');
          hid.type = 'hidden';
          hid.id = 'inputAlcaldiaDbId';
          document.getElementById('formAlcaldia')?.appendChild(hid);
        }
        hid.value = a.id_alcaldia;
        const n = document.getElementById('inputAlcaldiaNombre');
        if (n) n.value = a.nombre;
        const c = document.getElementById('inputAlcaldiaId');
        if (c) {
          var idNum = parseInt(a.id_alcaldia != null ? a.id_alcaldia : a.id, 10) || 0;
          c.value = a.clave || ('CDMX-' + String(idNum).padStart(2, '0'));
          c.readOnly = true;
        }
        const titA = document.getElementById('modalAlcaldiaTitulo');
        if (titA) titA.textContent = 'Editar Alcaldía';
        const titulo = document.getElementById('modalAlcaldiaTitulo');
        if (titulo) titulo.textContent = 'Editar Alcaldía';
        const modal = document.getElementById('modalAlcaldia');
        if (modal && global.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
      }
    } catch (err) {
      toast(err.message || 'Acción fallida', 'error');
    }
  });


  async function loadAdminSesion() {
    try {
      const res = await api.get('/api/auth/me');
      const user = res.data?.user || {};
      const nombre = user.nombre || user.correo || 'Administrador';
      if (typeof global.rupeApplyUserName === 'function') {
        global.rupeApplyUserName(nombre, user.rol);
      }
      const el = document.getElementById('adminUserName');
      if (el) el.textContent = nombre;
      // Guardar en global
      global.__RUPE_ADMIN_USER__ = user;
      // Perfil module if fields exist
      const setVal = (id, v) => {
        const n = document.getElementById(id);
        if (n && v) n.value = v;
      };
      setVal('adminPerfilNombre', nombre);
      setVal('adminPerfilCorreo', user.correo || '');
      setVal('adminPerfilTelefono', user.telefono || '');
      // texto visible en perfil
      document.querySelectorAll('[data-admin-nombre]').forEach((n) => {
        n.textContent = nombre;
      });
      return user;
    } catch (e) {
      console.warn('[RUPE Admin] sesion', e);
      if (e.status === 401 || e.status === 403) {
        toast('Sesión no autorizada.', 'error');
        setTimeout(() => (location.href = '/'), 1200);
      }
      return '';
    }
  }


  function refreshCalculosFromDB() {
    const stats = global.__RUPE_STATS__ || {};
    const mascotas = Array.isArray(global.__RUPE_REPORTES__) ? global.__RUPE_REPORTES__ : [];
    const porAlc = Array.isArray(global.__RUPE_POR_ALCALDIA__) ? global.__RUPE_POR_ALCALDIA__ : [];

    // Contar desde lista real si stats vacío
    let busq = 0, resg = 0, loc = 0;
    if (mascotas.length) {
      mascotas.forEach(function (m) {
        const e = String(m.estatus || '').toLowerCase();
        if (/localiz|encontr|resuelt|reunif/.test(e)) loc++;
        else if (/resguardo|albergue/.test(e)) resg++;
        else busq++;
      });
    } else {
      busq = Number(stats['En Búsqueda'] != null ? stats['En Búsqueda'] : stats.en_busqueda || 0) || 0;
      resg = Number(stats['En Resguardo'] != null ? stats['En Resguardo'] : stats.en_resguardo || 0) || 0;
      loc = Number(stats['Localizado'] != null ? stats['Localizado'] : stats.localizado || 0) || 0;
    }
    const total = mascotas.length || Number(stats.total != null ? stats.total : (busq + resg + loc)) || 0;

    const pct = function (n) {
      return total > 0 ? Math.round((n / total) * 1000) / 10 : 0;
    };
    const pctBusq = pct(busq);
    const pctLoc = pct(loc);
    const pctResg = pct(resg);

    // Barras de distribución por estatus
    const setBar = function (labelId, barId, pctVal, count, label) {
      const lab = document.getElementById(labelId);
      if (lab) {
        lab.textContent = pctVal.toFixed(1) + '% (' + count + ' casos)';
      }
      const bar = document.getElementById(barId);
      if (bar) {
        bar.style.width = Math.min(100, pctVal) + '%';
        bar.setAttribute('aria-valuenow', String(pctVal));
      }
    };
    setBar('calcLabelBusqueda', 'calcBarBusqueda', pctBusq, busq);
    setBar('calcLabelLocalizado', 'calcBarLocalizado', pctLoc, loc);
    setBar('calcLabelResguardo', 'calcBarResguardo', pctResg, resg);

    const totalEl = document.getElementById('calcTotalAnalizados');
    if (totalEl) totalEl.textContent = total + ' Registros';

    // Calculadora: precargar con datos reales
    const inpEnc = document.getElementById('calcEncontrados');
    const inpTot = document.getElementById('calcTotales');
    if (inpEnc && !inpEnc.dataset.userEdited) inpEnc.value = String(loc);
    if (inpTot && !inpTot.dataset.userEdited) inpTot.value = String(total);
    if (typeof global.ejecutarCalculo === 'function') {
      try {
        global.ejecutarCalculo();
      } catch (_) {}
    } else if (typeof ejecutarCalculo === 'function') {
      try {
        ejecutarCalculo();
      } catch (_) {}
    } else {
      // inline formula
      const encontrados = loc;
      const totales = total;
      const resEl = document.getElementById('resultadoEfectividad');
      const msgEl = document.getElementById('mensajeCalificacion');
      if (resEl) {
        const p = totales > 0 ? ((encontrados / totales) * 100).toFixed(2) : '0.00';
        resEl.textContent = p + '%';
      }
      if (msgEl && totales > 0) {
        const p = (encontrados / totales) * 100;
        if (p >= 70) {
          msgEl.textContent = 'Efectividad Alta en Proceso de Localización';
          msgEl.className = 'text-success fw-semibold';
        } else if (p >= 40) {
          msgEl.textContent = 'Efectividad Moderada';
          msgEl.className = 'text-warning fw-semibold';
        } else {
          msgEl.textContent = 'Efectividad Baja / Alerta Requerida';
          msgEl.className = 'text-danger fw-semibold';
        }
      }
    }

    // KPIs genéricos (otras páginas)
    const setT = function (id, v) {
      const el = document.getElementById(id);
      if (el) el.textContent = String(v ?? 0);
    };
    setT('kpiTotalReportes', total);
    setT('kpiEnBusqueda', busq);
    setT('kpiResguardo', resg);
    setT('kpiLocalizado', loc);
    setT('calcTasaRecuperacion', (total ? Math.round((loc / total) * 1000) / 10 : 0) + '%');

    // Tabla por alcaldía
    let rows = porAlc;
    if (!rows.length && mascotas.length) {
      const map = {};
      mascotas.forEach(function (m) {
        const k = m.alcaldia_nombre || m.alcaldia || 'Sin alcaldía';
        if (!map[k]) map[k] = { alcaldia: k, total: 0, busqueda: 0, localizado: 0, resguardo: 0 };
        map[k].total++;
        if (m.estatus === 'En Búsqueda') map[k].busqueda++;
        else if (m.estatus === 'Localizado') map[k].localizado++;
        else if (m.estatus === 'En Resguardo') map[k].resguardo++;
      });
      rows = Object.values(map);
    }

    const tbody =
      document.getElementById('tbodyCalculosAlcaldias') ||
      document.querySelector('#tablaCalculosAlcaldias tbody') ||
      document.querySelector('#modulo-calculos .table tbody');
    if (tbody) {
      if (!rows.length) {
        tbody.innerHTML =
          '<tr><td colspan="5" class="text-center text-muted py-4">Sin casos registrados aún.</td></tr>';
      } else {
        const grand = rows.reduce(function (s, r) {
          return s + Number(r.total || 0);
        }, 0) || total || 1;
        tbody.innerHTML = rows
          .map(function (r) {
            const t = Number(r.total || 0);
            const b = Number(r.busqueda || 0);
            const l = Number(r.localizado || 0);
            const pctRow = grand > 0 ? Math.round((t / grand) * 1000) / 10 : 0;
            const nombre = String(r.alcaldia || r.nombre || '—').replace(/</g, '&lt;');
            return (
              '<tr>' +
              '<td class="ps-4 fw-bold">' +
              nombre +
              '</td>' +
              '<td>' +
              b +
              '</td>' +
              '<td>' +
              l +
              '</td>' +
              '<td>' +
              t +
              '</td>' +
              '<td class="pe-4"><span class="fw-bold text-primary">' +
              pctRow +
              '%</span></td>' +
              '</tr>'
            );
          })
          .join('');
      }
    }
  }

  async function calcularProyeccion() {
    try {
      await loadReportes();
      refreshCalculosFromDB();
      toast('Indicadores recalculados con datos reales de la BD.', 'success');
    } catch (err) {
      console.warn(err);
      refreshCalculosFromDB();
      toast(err.message || 'No se pudieron recalcular los indicadores.', 'warning');
    }
  }
  global.calcularProyeccion = calcularProyeccion;
  global.refreshCalculosFromDB = refreshCalculosFromDB;


  global.rupeAdminRefreshCalculos = refreshCalculosFromDB;

  async function init() {
    // Prioridad: datos reales inyectados por PHP desde la BD
    const initData = global.RUPE_ADMIN_INIT || {};
    if (initData.csrf) global.__CSRF__ = initData.csrf;
    if (initData.user) global.__RUPE_ADMIN_USER__ = initData.user;
    if (Array.isArray(initData.usuarios)) global.__RUPE_USUARIOS__ = initData.usuarios;
    if (Array.isArray(initData.alcaldias)) global.__RUPE_ALCALDIAS__ = initData.alcaldias;
    if (Array.isArray(initData.razas)) global.__RUPE_RAZAS__ = initData.razas;
    if (Array.isArray(initData.mascotas)) global.__RUPE_REPORTES__ = initData.mascotas;
    if (initData.stats) global.__RUPE_STATS__ = initData.stats;
    if (Array.isArray(initData.auditoria)) global.__RUPE_AUDITORIA__ = initData.auditoria;
    if (initData.config) global.__RUPE_CONFIG__ = initData.config;
    if (Array.isArray(initData.faqs)) global.__RUPE_AYUDA__ = initData.faqs;

    try {
      const r = await fetch('/api/csrf', { credentials: 'same-origin' });
      const d = await r.json();
      if (d.data && d.data.csrf) global.__CSRF__ = d.data.csrf;
    } catch (_) {}

    // Overrides globales de las funciones de cada menú admin

  async function guardarRaza(e) {
    if (e && e.preventDefault) e.preventDefault();
    if (e && e.stopPropagation) e.stopPropagation();
    const nombreEl = document.getElementById('inputRazaNombre');
    const tamanoEl = document.getElementById('inputRazaTamano');
    const hid = document.getElementById('inputRazaId');
    const btn = document.querySelector('#formRaza button[type="submit"]');
    const nombre = (nombreEl && nombreEl.value ? nombreEl.value : '').trim();
    const especie = 'perro';
    const tamano = (tamanoEl && tamanoEl.value ? tamanoEl.value : '').trim();
    const idEdit = hid && hid.value ? String(hid.value).trim() : '';

    if (!global.__CSRF__ && global.RUPE_ADMIN_INIT && global.RUPE_ADMIN_INIT.csrf) {
      global.__CSRF__ = global.RUPE_ADMIN_INIT.csrf;
    }
    // Refrescar CSRF
    try {
      const cr = await fetch('/api/csrf', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
      const cj = await cr.json();
      const tok = (cj.data && cj.data.csrf) || cj.csrf || '';
      if (tok) global.__CSRF__ = tok;
    } catch (_) {}

    if (typeof global.rupeValidarRaza === 'function' && !global.rupeValidarRaza(true)) {
      toast('Completa nombre (mín. 2) y tamaño de la raza.', 'warning');
      return false;
    }
    if (nombre.length < 2) {
      toast('El nombre de la raza es obligatorio (mín. 2 caracteres).', 'warning');
      if (nombreEl) nombreEl.focus();
      return false;
    }
    if (!tamano) {
      toast('Selecciona el tamaño de la raza.', 'warning');
      if (tamanoEl) tamanoEl.focus();
      return false;
    }

    const payload = { nombre: nombre, especie: especie, tamano: tamano, activo: 1, _csrf: global.__CSRF__ || '' };
    if (idEdit) payload.id = Number(idEdit) || idEdit;

    if (btn) {
      btn.disabled = true;
      btn.dataset._old = btn.innerHTML;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando…';
    }

    async function postJson(url, body) {
      const res = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': global.__CSRF__ || '',
        },
        body: JSON.stringify(body),
      });
      const text = await res.text();
      let data = {};
      try { data = JSON.parse(text); } catch (_) {
        throw new Error('Respuesta no JSON (' + res.status + '): ' + text.slice(0, 150));
      }
      if (!res.ok || data.success === false) {
        const err = new Error(data.message || ('HTTP ' + res.status));
        err.status = res.status;
        throw err;
      }
      return data;
    }

    async function postForm(url, body) {
      const fd = new FormData();
      Object.keys(body).forEach(function (k) {
        if (body[k] != null) fd.append(k, String(body[k]));
      });
      const res = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': global.__CSRF__ || '',
        },
        body: fd,
      });
      const text = await res.text();
      let data = {};
      try { data = JSON.parse(text); } catch (_) {
        throw new Error('Respuesta no JSON form (' + res.status + '): ' + text.slice(0, 150));
      }
      if (!res.ok || data.success === false) {
        const err = new Error(data.message || ('HTTP ' + res.status));
        err.status = res.status;
        throw err;
      }
      return data;
    }

    try {
      let res;
      // 1) Endpoint directo (más confiable en Hostinger)
      // 2) API router como respaldo
      const urlDirect = '/guardar-raza.php';
      const urlDirect2 = '/admin-save-raza.php';
      const urlApi = idEdit
        ? '/api/admin/razas/' + encodeURIComponent(idEdit)
        : '/api/admin/razas';
      try {
        res = await postJson(urlDirect, payload);
      } catch (e1) {
        try {
          res = await postForm(urlDirect, payload);
        } catch (e2) {
          try {
            res = await postJson(urlDirect2, payload);
          } catch (e2b) {
            try {
              res = await postForm(urlDirect2, payload);
            } catch (e2c) {
              try {
                res = await postJson(urlApi, payload);
              } catch (e3) {
                try {
                  res = await postForm(urlApi, payload);
                } catch (e4) {
                  throw e1;
                }
              }
            }
          }
        }
      }
      const row = (res && res.data) ? res.data : {};
      const newId = row.id_raza || row.id || idEdit || '';
      const dbMsg = row._database || global.__RUPE_DB__ || '';
      const cod = newId ? ('RZ-' + String(newId).padStart(2, '0')) : 'RZ—';
      toast('✓ Guardado en tabla razas' + (dbMsg ? ' (' + dbMsg + ')' : '') + ': ' + nombre + ' · ' + cod + ' · ' + (row.tamano || tamano), 'success');
      const badge = document.getElementById('badgeDbRazas');
      if (badge && dbMsg) badge.textContent = 'BD: ' + dbMsg;
      const modal = document.getElementById('modalRaza');
      if (modal && global.bootstrap) {
        const inst = bootstrap.Modal.getInstance(modal);
        if (inst) inst.hide();
      }
      try { await loadRazas(); } catch (_) {}
      // Recarga dura para garantizar datos de BD
      setTimeout(function () { location.reload(); }, 900);
    } catch (err) {
      console.warn('[RUPE Admin] guardarRaza', err);
      let msg = (err && err.message) ? err.message : 'No se pudo guardar la raza en la base de datos.';
      if (err && err.status === 419) {
        msg = 'Sesión CSRF expirada. Recarga la página (Ctrl+Shift+R) e intenta de nuevo.';
      }
      if (err && err.status === 403) {
        msg = 'Sesión de administrador requerida. Vuelve a iniciar sesión.';
      }
      toast(msg, 'error');
      try { console.error('[RUPE] guardarRaza error', err); } catch(_){}
      // Mostrar error visible
      try { alert('Error al guardar raza: ' + msg); } catch(_){}
    } finally {
      if (btn) {
        btn.disabled = false;
        if (btn.dataset._old) btn.innerHTML = btn.dataset._old;
      }
    }
    return false;
  }
  global.guardarRaza = guardarRaza;
  window.guardarRaza = guardarRaza;



    global.guardarRaza = guardarRaza;
    global.guardarAlcaldia = guardarAlcaldia;
    global.guardarPreferenciasAdmin = guardarPreferenciasAdmin;
    global.guardarAyuda = guardarAyuda;
    global.rupeAdminToast = toast;
    global.rupeLoadRazas = loadRazas;
    global.rupeLoadAlcaldias = loadAlcaldias;
    global.rupeLoadUsuarios = loadUsuarios;

    const path = location.pathname.replace(/\/$/, '');
    try {
      await loadAdminSesion();
    } catch (e) {
      console.warn('[RUPE Admin] sesion', e);
      if (e && (e.status === 401 || e.status === 403)) {
        toast('Sesión no autorizada. Inicia sesión como administrador.', 'error');
        setTimeout(() => (location.href = '/'), 1500);
        return;
      }
    }
    try {
      if (path.includes('razas')) await loadRazas();
      else if (path.includes('alcaldias')) await loadAlcaldias();
      else if (path.includes('usuarios')) await loadUsuarios();
      else if (path.includes('auditoria')) await loadAuditoria();
      else if (path.includes('configuracion')) await loadConfig();
      else if (path.includes('ayuda')) await loadAyuda();
      else if (path.includes('calculos')) {
        await loadReportes();
        try { refreshCalculosFromDB(); } catch (_) {}
      }
      else if (path.includes('reportes') || path === '/admin' || path.endsWith('/admin')) {
        await loadReportes();
        try { refreshCalculosFromDB(); } catch (_) {}
      }
      toast('Datos sincronizados con el servidor.', 'info');
    } catch (e) {
      console.warn('[RUPE Admin] sync datos', e);
      toast((e && e.message) ? e.message : 'Error al cargar datos de la BD.', 'warning');
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => setTimeout(init, 250));
  } else {
    setTimeout(init, 250);
  }



  function aplicarFiltrosRazas(showToast) {
    const tabla = document.getElementById('tablaRazas');
    if (!tabla) return;
    const qEl = document.getElementById('filtroRazaBuscar');
    const eEl = document.getElementById('filtroRazaEstado');
    const tEl = document.getElementById('filtroRazaTamano');
    const sEl = document.getElementById('filtroRazaEspecie');
    const q = (qEl && qEl.value ? qEl.value : '').trim().toLowerCase();
    const estado = eEl ? eEl.value : '';
    const tamano = tEl ? tEl.value : '';
    const especie = sEl ? sEl.value : '';
    let visible = 0;
    tabla.querySelectorAll('tbody tr').forEach(function (tr) {
      if (tr.querySelector('td[colspan]')) {
        tr.style.display = '';
        return;
      }
      const txt = (tr.getAttribute('data-nombre') || tr.textContent || '').toLowerCase();
      const est = tr.getAttribute('data-estado') || '';
      const tam = tr.getAttribute('data-tamano') || '';
      const esp = (tr.getAttribute('data-especie') || '').toLowerCase();
      let ok = true;
      if (q) {
        const cod = ('rz-' + String(tr.getAttribute('data-id') || '').padStart(2, '0')).toLowerCase();
        if (txt.indexOf(q) < 0 && cod.indexOf(q) < 0) ok = false;
      }
      if (estado && est !== estado) ok = false;
      if (tamano) {
        const tamNorm = (tam || '').trim();
        if (tamNorm !== tamano) ok = false;
      }
      if (especie) {
        const espNorm = (esp || '').toLowerCase();
        const want = especie.toLowerCase();
        if (espNorm !== want) ok = false;
      }
      tr.style.display = ok ? '' : 'none';
      if (ok) visible++;
    });
    const cont = document.getElementById('contadorRazas');
    if (cont) cont.textContent = String(visible);
    if (showToast !== false) {
      toast('Filtro aplicado: ' + visible + ' raza(s).', 'info');
    }
  }
  global.aplicarFiltrosRazas = aplicarFiltrosRazas;

  function limpiarFiltrosRazas() {
    ['filtroRazaBuscar', 'filtroRazaEstado', 'filtroRazaTamano', 'filtroRazaEspecie'].forEach(function (id) {
      const el = document.getElementById(id);
      if (!el) return;
      if (el.tagName === 'SELECT') el.selectedIndex = 0;
      else el.value = '';
    });
    aplicarFiltrosRazas(false);
    toast('Filtros limpiados.', 'info');
  }
  global.limpiarFiltrosRazas = limpiarFiltrosRazas;
  global.filtrarCatalogoPorEstado = function (tablaId, val) {
    const el = document.getElementById('filtroRazaEstado');
    if (el) el.value = val || '';
    aplicarFiltrosRazas(true);
  };
  global.filtrarCatalogoPorTamano = function (tablaId, val) {
    const el = document.getElementById('filtroRazaTamano');
    if (el) el.value = val || '';
    aplicarFiltrosRazas(true);
  };
  global.filtrarTabla = function (input, tablaId) {
    if (input && input.id !== 'filtroRazaBuscar') {
      const b = document.getElementById('filtroRazaBuscar');
      if (b) b.value = input.value || '';
    }
    aplicarFiltrosRazas(false);
  };
  global.limpiarFiltros = function (tablaId) {
    if (tablaId === 'tablaRazas' || !tablaId) limpiarFiltrosRazas();
  };


  // ---------- Cerrar sesión (modal de confirmación) ----------
  function ensureAdminLogoutModal() {
    if (document.getElementById('modalCerrarSesionAdmin')) return;
    const html = `
<div class="modal fade" id="modalCerrarSesionAdmin" tabindex="-1" aria-labelledby="modalCerrarSesionAdminLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white" style="background-color:#6B1D2F;">
        <h5 class="modal-title fw-bold" id="modalCerrarSesionAdminLabel">
          <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">¿Deseas cerrar tu sesión de administrador?</p>
        <p class="small text-muted mb-0">Se cerrará el acceso al panel y regresarás a la página de inicio.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarLogoutAdmin">
          <i class="bi bi-box-arrow-right me-1"></i>Sí, cerrar sesión
        </button>
      </div>
    </div>
  </div>
</div>`;
    document.body.insertAdjacentHTML('beforeend', html);
    const btn = document.getElementById('btnConfirmarLogoutAdmin');
    if (btn) {
      btn.addEventListener('click', function () {
        ejecutarLogoutAdmin();
      });
    }
  }

  async function ejecutarLogoutAdmin() {
    const btn = document.getElementById('btnConfirmarLogoutAdmin');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saliendo…';
    }
    try {
      await fetch('/api/auth/logout', {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: '{}',
      });
    } catch (_) {}
    window.location.href = '/auth/logout';
  }

  function confirmarCerrarSesionAdmin(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    ensureAdminLogoutModal();
    const modalEl = document.getElementById('modalCerrarSesionAdmin');
    if (modalEl && global.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else if (modalEl) {
      modalEl.classList.add('show');
      modalEl.style.display = 'block';
    } else {
      if (confirm('¿Cerrar sesión de administrador?')) ejecutarLogoutAdmin();
    }
    return false;
  }

  global.confirmarCerrarSesionAdmin = confirmarCerrarSesionAdmin;
  global.ejecutarLogoutAdmin = ejecutarLogoutAdmin;
  global.rupeAdminLogout = confirmarCerrarSesionAdmin;


  global.RUPEAdmin = { api, toast, loadRazas, loadAlcaldias, loadUsuarios, loadReportes, loadAuditoria, loadConfig, loadAyuda, loadAdminSesion, refreshCalculosFromDB, logout: confirmarCerrarSesionAdmin, confirmarCerrarSesionAdmin };
})(window);