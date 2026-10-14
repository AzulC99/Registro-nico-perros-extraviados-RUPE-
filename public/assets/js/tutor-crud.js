/**
 * RUPE CDMX — CRUD Tutor con datos reales de BD (sin api.js)
 * public/assets/js/tutor-crud.js
 * Inicializa desde window.RUPE_INIT (inyectado por PHP) y mutaciones vía fetch.
 */
'use strict';

(function (global) {
  const INIT = global.RUPE_INIT || {};
  const CSRF = function () {
    return global.__CSRF__ || (INIT.csrf || '');
  };

  function toast(msg, type) {
    const t = (type === 'error' ? 'danger' : (type || 'success'));
    if (typeof global.rupeToast === 'function') {
      try { global.rupeToast(msg, t); return; } catch (_) {}
    }
    if (typeof global.mostrarToastTutor === 'function') {
      try { global.mostrarToastTutor(msg, t); return; } catch (_) {}
    }
    let el = document.getElementById('rupeTutorToast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'rupeTutorToast';
      el.style.cssText =
        'position:fixed;bottom:24px;right:24px;z-index:99999;min-width:240px;max-width:380px;padding:14px 18px;border-radius:12px;color:#fff;font-size:14px;box-shadow:0 8px 24px rgba(0,0,0,.18);opacity:0;transition:opacity .25s;';
      document.body.appendChild(el);
    }
    const colors = { success: '#198754', error: '#dc3545', warning: '#e8a33d', info: '#6B1D2F' };
    el.style.background = colors[type] || colors.info;
    el.textContent = msg;
    el.style.opacity = '1';
    clearTimeout(el._t);
    el._t = setTimeout(function () { el.style.opacity = '0'; }, 3800);
  }

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
    return global.state;
  }

  async function request(method, path, body) {
    const headers = {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-Token': CSRF(),
    };
    let payload = body;
    if (body && !(body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
      payload = JSON.stringify(Object.assign({}, body, { _csrf: CSRF() }));
    } else if (body instanceof FormData) {
      if (!body.has('_csrf')) body.append('_csrf', CSRF());
      payload = body;
    }
    const res = await fetch(path, {
      method: method,
      headers: headers,
      body: method === 'GET' || method === 'HEAD' ? undefined : payload,
      credentials: 'same-origin',
    });
    const data = await res.json().catch(function () { return {}; });
    if (data && data.data && data.data.csrf) {
      global.__CSRF__ = data.data.csrf;
    }
    if (!res.ok || data.success === false) {
      const err = new Error(data.message || 'Error de servidor');
      err.status = res.status;
      err.errors = data.errors || '';
      throw err;
    }
    return data;
  }

  function applyInit() {
    const st = ensureState();
    const user = INIT.user || {};
    st.tutorActual = {
      nombre: user.nombre || '',
      telefono: user.telefono || '',
      correo: user.correo || '',
      aceptaPrivacidad: true,
    };
    st.registros = (Array.isArray(INIT.mascotas) ? INIT.mascotas : []).map(function (m) {
      return normalizeRegistro({
        id: m.id || m.id_mascota,
        id_mascota: m.id_mascota || m.id,
        folio: m.folio,
        nombre: m.nombre,
        raza: m.raza || m.raza_nombre || '',
        raza_nombre: m.raza_nombre || m.raza || '',
        id_raza: m.id_raza,
        alcaldia: m.alcaldia || m.alcaldia_nombre || '',
        alcaldia_nombre: m.alcaldia_nombre || m.alcaldia || '',
        id_alcaldia: m.id_alcaldia,
        colonia: m.colonia || '',
        calle: m.calle || '',
        direccion_ref: m.direccion_ref || '',
        referencia: m.referencia || '',
        fecha: m.fecha || m.fecha_extravio || '',
        fecha_extravio: m.fecha_extravio || m.fecha || '',
        estatus: m.estatus || 'En Búsqueda',
        foto: m.foto || m.foto_url || '',
        foto_url: m.foto_url || m.foto || '',
        especie: m.especie || 'perro',
        qr_token: m.qr_token || '',
      });
    });
    st.statsBackend = INIT.stats || {};
    st.albergues = Array.isArray(INIT.albergues) ? INIT.albergues : [];
    global.__RUPE_ALCALDIAS__ = Array.isArray(INIT.alcaldias) ? INIT.alcaldias : [];
    global.__RUPE_RAZAS__ = Array.isArray(INIT.razas) ? INIT.razas : [];
    global.__RUPE_ALBERGUES__ = st.albergues;
    global.__RUPE_USER__ = user;
    if (INIT.csrf) global.__CSRF__ = INIT.csrf;
    if (st.registros.length) {
      st.folioSeleccionado = st.registros[0].folio;
    } else {
      st.folioSeleccionado = '';
    }
    // Alias para scripts inline
    try { global.state = st; } catch (_) {}
    return st;
  }

  function cleanVal(v, fallback) {
    if (v === null || v === undefined) return fallback !== undefined ? fallback : '—';
    var s = String(v).trim();
    if (!s || s === 'null' || s === 'undefined' || s === 'NaN' || s === '—') {
      return fallback !== undefined ? fallback : '—';
    }
    return s;
  }

  function normalizeRegistro(m) {
    if (!m || typeof m !== 'object') return m;
    var out = Object.assign({}, m);
    // IDs
    out.id = parseInt(out.id || out.id_mascota || 0, 10) || 0;
    out.id_mascota = parseInt(out.id_mascota || out.id || 0, 10) || 0;
    out.id_raza = parseInt(out.id_raza || 0, 10) || 0;
    out.id_alcaldia = parseInt(out.id_alcaldia || 0, 10) || 0;
    // Texto: limpiar null
    ['nombre','raza','raza_nombre','alcaldia','alcaldia_nombre','colonia','calle','referencia','direccion_ref','fecha','fecha_extravio','estatus','color','sexo','tamano','folio','foto','foto_url'].forEach(function (k) {
      if (out[k] === null || out[k] === undefined || String(out[k]).trim() === 'null' || String(out[k]).trim() === 'undefined') {
        out[k] = '';
      } else {
        out[k] = String(out[k]).trim();
      }
    });
    // Aliases de JOIN
    if (!out.raza) out.raza = out.raza_nombre || '';
    if (!out.raza_nombre) out.raza_nombre = out.raza || '';
    if (!out.alcaldia) out.alcaldia = out.alcaldia_nombre || '';
    if (!out.alcaldia_nombre) out.alcaldia_nombre = out.alcaldia || '';
    if (!out.fecha) out.fecha = out.fecha_extravio || (out.created_at ? String(out.created_at).slice(0, 10) : '');
    if (!out.fecha_extravio) out.fecha_extravio = out.fecha || '';
    if (!out.estatus) out.estatus = 'En Búsqueda';
    // Dirección
    if (!out.direccion_ref && (out.calle || out.referencia)) {
      var c = String(out.calle || '').trim();
      var r = String(out.referencia || '').trim();
      if (c && r) out.direccion_ref = 'Calle: ' + c + ' | Ref: ' + r;
      else if (c) out.direccion_ref = 'Calle: ' + c;
      else if (r) out.direccion_ref = 'Ref: ' + r;
    }
    // Parsear calle/ref desde direccion_ref si faltan
    if (out.direccion_ref && (!out.calle || !out.referencia)) {
      var dir = out.direccion_ref;
      var mCalle = dir.match(/Calle:\s*([^|]+)/i);
      var mRef = dir.match(/Ref(?:erencia)?:\s*(.+)$/i);
      if (!out.calle && mCalle) out.calle = mCalle[1].trim();
      if (!out.referencia && mRef) out.referencia = mRef[1].trim();
      if (!out.calle && !mCalle && dir.indexOf(',') >= 0) {
        var parts = dir.split(',');
        out.calle = (parts[0] || '').trim();
        if (!out.referencia) out.referencia = parts.slice(1).join(',').trim();
      }
    }
    if (!out.foto || out.foto === 'null') {
      out.foto = out.foto_url && out.foto_url !== 'null' ? out.foto_url : '';
    }
    if (!out.foto_url || out.foto_url === 'null') out.foto_url = out.foto || '';
    return out;
  }


  const RUPE_COLONIAS = ["Roma Norte", "Roma Sur", "Condesa", "Hipódromo", "Del Valle Centro", "Narvarte", "Coyoacán Centro", "Pedregal", "San Ángel", "Polanco", "Anzures", "Lindavista", "Industrial", "Doctores", "Juárez", "Centro Histórico", "Santa María la Ribera", "Tacubaya", "Mixcoac", "Portales", "Villa Coapa", "Culhuacán", "San Miguel (Iztapalapa)", "Agrícola Oriental", "Jardín Balbuena", "Xochimilco Centro", "Tlalpan Centro", "Copilco", "Universidad", "Otro / Otra colonia"];

  function llenarSelectColoniasEdit(selected) {
    const sel = document.getElementById('editColonia');
    if (!sel) return;
    const cur = selected || sel.value || '';
    // Si ya tiene opciones (PHP), solo seleccionar
    if (sel.options.length <= 1) {
      sel.innerHTML = '<option value="">Selecciona colonia…</option>';
      RUPE_COLONIAS.forEach(function (c) {
        const opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c;
        sel.appendChild(opt);
      });
    }
    if (cur) {
      let found = false;
      for (let i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value === cur) { found = true; break; }
      }
      if (!found && cur) {
        const opt = document.createElement('option');
        opt.value = cur;
        opt.textContent = cur;
        sel.appendChild(opt);
      }
      try { sel.value = cur; } catch (_) {}
    }
  }

  function marcarEditCampo(el, ok) {
    if (!el) return;
    el.classList.toggle('is-valid', !!ok);
    el.classList.toggle('is-invalid', !ok);
    const okId = el.id + 'Ok';
    const hint = document.getElementById(okId);
    if (hint) hint.classList.toggle('d-none', !ok);
  }

  function validarModalEdicionLive() {
    const nombre = document.getElementById('editNombre');
    const raza = document.getElementById('editRaza');
    const alc = document.getElementById('editAlcaldia');
    const col = document.getElementById('editColonia');
    const calle = document.getElementById('editCalle');
    const ref = document.getElementById('editReferencia');
    const fecha = document.getElementById('editFecha');

    if (nombre) {
      const v = (nombre.value || '').trim();
      const ok = v.length >= 2 && v.length <= 20 && /^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ0-9 .'\-]+$/.test(v);
      marcarEditCampo(nombre, ok);
    }
    if (raza) marcarEditCampo(raza, !!(raza.value && String(raza.value) !== ''));
    if (alc) marcarEditCampo(alc, !!(alc.value && String(alc.value) !== ''));
    if (col) marcarEditCampo(col, !!(col.value && String(col.value).trim()));
    if (calle) {
      const v = (calle.value || '').trim();
      marcarEditCampo(calle, v.length >= 2 && v.length <= 120);
    }
    if (ref) {
      const v = (ref.value || '').trim();
      if (!v) {
        ref.classList.remove('is-valid', 'is-invalid');
        const h = document.getElementById('editReferenciaOk');
        if (h) h.classList.add('d-none');
      } else {
        marcarEditCampo(ref, v.length <= 120);
      }
    }
    if (fecha) marcarEditCampo(fecha, !!(fecha.value && /^\d{4}-\d{2}-\d{2}$/.test(fecha.value)));
  }

  function bindValidacionModalEditar() {
    const form = document.getElementById('formEditarRegistro');
    if (!form || form._rupeEditValBound) return;
    form._rupeEditValBound = true;
    ['editNombre', 'editRaza', 'editAlcaldia', 'editColonia', 'editCalle', 'editReferencia', 'editFecha'].forEach(function (id) {
      const el = document.getElementById(id);
      if (!el) return;
      el.addEventListener('input', validarModalEdicionLive);
      el.addEventListener('change', validarModalEdicionLive);
    });
  }


  function badgeClass(est) {
    if (est === 'En Búsqueda') return 'bg-danger';
    if (est === 'En Resguardo') return 'bg-warning text-dark';
    if (est === 'Localizado') return 'bg-success';
    return 'bg-secondary';
  }

  function splitDireccionRef(reg) {
    var colonia = String((reg && (reg.colonia || '')) || '').trim();
    var dir = String((reg && (reg.direccion_ref || reg.referencia || '')) || '').trim();
    var calle = String((reg && (reg.calle || '')) || '').trim();
    var referencia = '';
    if (!calle && dir) {
      var mCalle = dir.match(/Calle:\s*([^|]+)/i);
      var mRef = dir.match(/Ref(?:erencia)?:\s*(.+)$/i);
      if (mCalle) calle = mCalle[1].trim();
      if (mRef) referencia = mRef[1].trim();
      else if (dir.indexOf(',') >= 0 && !calle) {
        var parts = dir.split(',');
        calle = (parts[0] || '').trim();
        referencia = parts.slice(1).join(',').trim();
      } else if (!calle) {
        referencia = dir;
      }
    } else if (dir) {
      var mRef2 = dir.match(/Ref(?:erencia)?:\s*(.+)$/i);
      referencia = mRef2 ? mRef2[1].trim() : dir;
    }
    return { colonia: colonia, calle: calle, referencia: referencia };
  }

  function renderTablaCrud(filtro) {
    const tbody = document.getElementById('tbodyCrud');
    if (!tbody) return;
    const st = ensureState();
    const q = String(
      filtro !== undefined && filtro !== ''
        ? filtro
        : (document.getElementById('inputBuscadorCrud') && document.getElementById('inputBuscadorCrud').value) || ''
    )
      .toLowerCase()
      .trim();
    const regs = st.registros || [];
    const filtrados = !q
      ? regs
      : regs.filter(function (r) {
          return (
            String(r.folio || '').toLowerCase().indexOf(q) !== -1 ||
            String(r.nombre || '').toLowerCase().indexOf(q) !== -1
          );
        });

    if (!filtrados.length) {
      // No borrar filas PHP si aún no hay datos en memoria
      if (!regs.length && tbody.querySelector('tr[data-id], tr[data-folio]')) {
        return;
      }
      tbody.innerHTML =
        '<tr><td colspan="11" class="text-center text-muted py-5">' +
        '<i class="bi bi-inbox fs-3 d-block mb-2"></i>' +
        (regs.length
          ? 'Sin resultados para el filtro.'
          : 'No hay registros. Usa el módulo <strong>Registro</strong> para crear el primero.') +
        '</td></tr>';
      return;
    }

    tbody.innerHTML = filtrados
      .map(function (reg) {
        const idx = regs.findIndex(function (r) {
          return r.folio === reg.folio || r.id === reg.id;
        });
        const est = reg.estatus || 'En Búsqueda';
        reg = normalizeRegistro(reg);
        const folio = cleanVal(reg.folio).replace(/'/g, '&#39;');
        const nombre = cleanVal(reg.nombre).replace(/</g, '&lt;');
        const raza = cleanVal(reg.raza || reg.raza_nombre).replace(/</g, '&lt;');
        const alc = cleanVal(reg.alcaldia || reg.alcaldia_nombre).replace(/</g, '&lt;');
        const addr = splitDireccionRef(reg);
        const colonia = cleanVal(addr.colonia).replace(/</g, '&lt;');
        const calle = cleanVal(addr.calle).replace(/</g, '&lt;');
        const referencia = cleanVal(addr.referencia).replace(/</g, '&lt;');
        const fecha = cleanVal(reg.fecha || reg.fecha_extravio).replace(/</g, '&lt;');
        const id = reg.id || reg.id_mascota || '';
        var fotoSrc = (reg.foto && reg.foto) ? reg.foto
          : ((reg.foto_url && reg.foto_url) ? reg.foto_url
          : (String(reg.especie || '').toLowerCase().indexOf('gato') >= 0
              ? '/assets/img/placeholder-gato.svg'
              : '/assets/img/placeholder-perro.svg'));
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
          '" alt="" class="rounded border" style="width:48px;height:48px;object-fit:cover;" onerror="this.src=\'/assets/img/placeholder-mascota.svg\'"></td>' +
          '<td><strong>' +
          nombre +
          '</strong></td>' +
          '<td>' +
          raza +
          '</td>' +
          '<td>' + alc + '</td>' + '<td class="small">' + colonia + '</td>' + '<td class="small">' + calle + '</td>' + '<td class="small">' + referencia + '</td>' + '<td>' + fecha + '</td>' +
          '<td><span class="badge ' +
          badgeClass(est) +
          '">' +
          est +
          '</span></td>' +
          '<td class="text-end pe-4">' +
          '<button type="button" class="btn btn-sm btn-outline-primary me-1" title="Ver ficha" data-crud="ver" data-index="' +
          idx +
          '"><i class="bi bi-eye"></i></button>' +
          '<button type="button" class="btn btn-sm btn-outline-secondary me-1" title="Editar" data-crud="editar" data-index="' +
          idx +
          '"><i class="bi bi-pencil"></i></button>' +
          '<button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" data-crud="eliminar" data-index="' +
          idx +
          '"><i class="bi bi-trash"></i></button>' +
          '</td></tr>'
        );
      })
      .join('');
  }

  function actualizarKpis() {
    const st = ensureState();
    const regs = st.registros || [];
    const stats = st.statsBackend || {};
    function setT(id, val) {
      const el = document.getElementById(id);
      if (el) el.textContent = String(val);
    }
    setT('kpiTotal', (typeof stats.total === 'number' ? stats.total : regs.length));
    setT(
      'kpiBusqueda',
      typeof stats['En Búsqueda'] === 'number'
        ? stats['En Búsqueda']
        : regs.filter(function (r) {
            return r.estatus === 'En Búsqueda';
          }).length
    );
    setT(
      'kpiResguardo',
      typeof stats['En Resguardo'] === 'number'
        ? stats['En Resguardo']
        : regs.filter(function (r) {
            return r.estatus === 'En Resguardo';
          }).length
    );
    setT(
      'kpiLocalizado',
      typeof stats['Localizado'] === 'number'
        ? stats['Localizado']
        : regs.filter(function (r) {
            return r.estatus === 'Localizado';
          }).length
    );
  }

  function seleccionarYVerPDF(folio) {
    const st = ensureState();
    const reg = (st.registros || []).find(function (r) {
      return String(r.folio) === String(folio);
    });
    if (!reg) {
      toast('No se encontró el folio.', 'warning');
      return;
    }
    st.folioSeleccionado = folio;
    const btn = document.querySelector('[data-modulo="ficha-pdf"]');
    if (btn) btn.click();
    // Pintar ficha con todos los datos (foto, sexo, color, alcaldía, fecha, señas…)
    function paint() {
      st.folioSeleccionado = folio;
      var selF = document.getElementById('tutorSelectFolioFicha');
      if (selF) {
        try { selF.value = String(folio); } catch (_) {}
      }
      if (typeof global.renderizarFichaTutor === 'function') {
        try { global.renderizarFichaTutor(reg); } catch (e) { console.warn(e); }
      }
      var idR = reg.id || reg.id_mascota;
      if (idR) {
        fetch('/api/tutor/mascotas/' + encodeURIComponent(idR), {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (full) {
          var fm = (full.data && full.data.mascota) || {};
          Object.keys(fm).forEach(function (k) {
            if (fm[k] !== undefined && fm[k] !== null && fm[k] !== '') reg[k] = fm[k];
          });
          try { global.renderizarFichaTutor(reg); } catch (e) { console.warn(e); }
        }).catch(function () {});
      }
    }
    paint();
    setTimeout(paint, 30);
    setTimeout(paint, 50);
    // QR
    const token = reg.qr_token || reg.folio;
    const base = (INIT.app_url || (global.location && global.location.origin) || '').replace(/\/$/, '');
    const url = base + '/mascota/' + encodeURIComponent(token);
    if (typeof global.rupeGenerarQR === 'function') {
      global.rupeGenerarQR(url, reg.folio);
    } else {
      const img = document.getElementById('tutorFichaQR');
      if (img) {
        img.src =
          'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + encodeURIComponent(url);
      }
    }
  }

  function abrirModalEditar(index) {
    const st = ensureState();
    const reg = st.registros[index];
    if (!reg) {
      toast('Registro no encontrado.', 'error');
      return;
    }
    const set = function (id, val) {
      const el = document.getElementById(id);
      if (el) el.value = (val !== undefined && val !== '') ? val : '';
    };
    set('editIndex', String(index));
    set('editIdMascota', reg.id || reg.id_mascota || '');
    const folioLabel = document.getElementById('modalFolioId');
    if (folioLabel) folioLabel.textContent = '#' + (reg.folio || '');
    set('editNombre', reg.nombre);
    set('editSenas', reg.senas || reg.senas_particulares || '');
    set('editFecha', (reg.fecha || reg.fecha_extravio || '').toString().substring(0, 10));
    set('editColor', reg.color || '');
    var addrE = splitDireccionRef(reg);
    llenarSelectColoniasEdit(addrE.colonia || '');
    set('editCalle', addrE.calle || '');
    set('editReferencia', addrE.referencia || '');
    // Raza
    const selRaza = document.getElementById('editRaza');
    if (selRaza && reg.id_raza) {
      try { selRaza.value = String(reg.id_raza); } catch (_) {}
    }
    bindValidacionModalEditar();
    setTimeout(validarModalEdicionLive, 50);

    // Alcaldías reales
    const sel = document.getElementById('editAlcaldia');
    if (sel) {
      const list = global.__RUPE_ALCALDIAS__ || (global.RUPE_INIT && global.RUPE_INIT.alcaldias) || [];
      const cur = reg.id_alcaldia ? String(reg.id_alcaldia) : '';
      sel.innerHTML = '<option value="">— Sin cambiar —</option>';
      list.forEach(function (a) {
        const id = a.id_alcaldia || a.id || 0;
        const opt = document.createElement('option');
        opt.value = id;
        opt.textContent = a.nombre || ('Alcaldía ' + id);
        if (cur && String(id) === cur) opt.selected = true;
        sel.appendChild(opt);
      });
    }

    const prev = document.getElementById('editFotoPreview');
    if (prev) {
      const foto = reg.foto || reg.foto_url || '';
      prev.src = foto || "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='96' height='96'%3E%3Crect fill='%23eee' width='96' height='96'/%3E%3Ctext fill='%23999' x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-size='10'%3ESin foto%3C/text%3E%3C/svg%3E";
    }
    const fi = document.getElementById('editFoto');
    if (fi) fi.value = '';
    const alertEl = document.getElementById('editAlert');
    if (alertEl) { alertEl.classList.add('d-none'); alertEl.textContent = ''; }

    try {
      const modal = document.getElementById('modalEditarRegistro');
      if (!modal) {
        toast('No se encontró el formulario de edición.', 'error');
        return;
      }
      if (global.bootstrap && global.bootstrap.Modal) {
        global.modalEditarBs = global.bootstrap.Modal.getOrCreateInstance(modal);
        global.modalEditarBs.show();
      } else if (window.bootstrap && window.bootstrap.Modal) {
        global.modalEditarBs = window.bootstrap.Modal.getOrCreateInstance(modal);
        global.modalEditarBs.show();
      } else {
        modal.classList.add('show');
        modal.style.display = 'block';
      }
    } catch (err) {
      console.error('[RUPE] modal editar', err);
      toast('No se pudo abrir el editor.', 'error');
    }
  }

  async function guardarEdicionRegistro(e) {
    if (e && e.preventDefault) e.preventDefault();
    validarModalEdicionLive();
    const nEl = document.getElementById('editNombre');
    const colEl = document.getElementById('editColonia');
    const calleEl = document.getElementById('editCalle');
    const fechaEl = document.getElementById('editFecha');
    if (nEl && nEl.classList.contains('is-invalid')) {
      toast('Nombre de mascota: 2 a 20 caracteres.', 'warning');
      return;
    }
    if (colEl && !colEl.value) {
      marcarEditCampo(colEl, false);
      toast('Selecciona una colonia.', 'warning');
      return;
    }
    if (calleEl && (calleEl.value || '').trim().length < 2) {
      marcarEditCampo(calleEl, false);
      toast('Calle: mínimo 2 caracteres.', 'warning');
      return;
    }
    if (fechaEl && !fechaEl.value) {
      marcarEditCampo(fechaEl, false);
      toast('Indica la fecha de extravío.', 'warning');
      return;
    }
    const st = ensureState();
    const index = parseInt(
      (document.getElementById('editIndex') && document.getElementById('editIndex').value) || '-1',
      10
    );
    const reg = st.registros[index];
    if (!reg || !(reg.id || reg.id_mascota)) {
      toast('Registro no válido para editar.', 'error');
      return;
    }
    const id = reg.id || reg.id_mascota;
    const nombre = ((document.getElementById('editNombre') && document.getElementById('editNombre').value) || '').trim();
    if (!nombre || nombre.length < 2) {
      toast('El nombre es obligatorio (mín. 2 caracteres).', 'warning');
      return;
    }
    const idRazaEl = document.getElementById('editRaza');
    const idAlcEl = document.getElementById('editAlcaldia');
    const idRaza = idRazaEl && idRazaEl.value ? parseInt(idRazaEl.value, 10) : 0;
    const idAlc = idAlcEl && idAlcEl.value ? parseInt(idAlcEl.value, 10) : 0;
    if (!idRaza) {
      toast('Selecciona una raza del catálogo.', 'warning');
      return;
    }
    if (!idAlc) {
      toast('Selecciona una alcaldía del catálogo.', 'warning');
      return;
    }
    const payload = {
      nombre: nombre,
      id_raza: idRaza,
      id_alcaldia: idAlc,
      senas_particulares: ((document.getElementById('editSenas') && document.getElementById('editSenas').value) || '').trim() || '',
      fecha_extravio: (document.getElementById('editFecha') && document.getElementById('editFecha').value) || '',
      color: ((document.getElementById('editColor') && document.getElementById('editColor').value) || '').trim() || '',
      colonia: ((document.getElementById('editColonia') && document.getElementById('editColonia').value) || '').trim() || '',
      calle: ((document.getElementById('editCalle') && document.getElementById('editCalle').value) || '').trim() || '',
      referencia: ((document.getElementById('editReferencia') && document.getElementById('editReferencia').value) || '').trim() || '',
      direccion_ref: (function () {
        var c = ((document.getElementById('editCalle') && document.getElementById('editCalle').value) || '').trim();
        var r = ((document.getElementById('editReferencia') && document.getElementById('editReferencia').value) || '').trim();
        if (c && r) return 'Calle: ' + c + ' | Ref: ' + r;
        if (c) return 'Calle: ' + c;
        if (r) return 'Ref: ' + r;
        return '';
      })(),
    };

    const fileInput = document.getElementById('editFoto');
    const file = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : '';
    const csrf = global.__CSRF__ || '';
    const btn = document.getElementById('btnGuardarEdicion');
    if (btn) btn.disabled = true;

    try {
      let res;
      const fd = new FormData();
      Object.keys(payload).forEach(function (k) {
        if (payload[k] !== undefined && payload[k] !== null) fd.append(k, String(payload[k]));
      });
      fd.append('_csrf', csrf);
      fd.append('_method', 'PUT');
      if (file) fd.append('foto', file);
      res = await fetch('/api/tutor/mascotas/' + encodeURIComponent(id), {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf,
        },
        credentials: 'same-origin',
        body: fd,
      });
      const data = await res.json().catch(function () { return {}; });
      if (!res.ok || data.success === false) {
        throw new Error(data.message || 'No se pudo guardar.');
      }
      const m = (data.data && data.data.mascota) || {};
      reg.nombre = m.nombre || payload.nombre || reg.nombre || '';
      reg.id_raza = m.id_raza != null && m.id_raza !== '' ? parseInt(m.id_raza, 10) : (payload.id_raza || reg.id_raza || 0);
      reg.raza = m.raza || m.raza_nombre || (document.getElementById('editRaza') && document.getElementById('editRaza').selectedOptions[0] ? document.getElementById('editRaza').selectedOptions[0].textContent.trim() : '') || reg.raza || '';
      reg.raza_nombre = reg.raza;
      reg.id_alcaldia = m.id_alcaldia != null && m.id_alcaldia !== '' ? parseInt(m.id_alcaldia, 10) : (payload.id_alcaldia || reg.id_alcaldia || 0);
      reg.alcaldia = m.alcaldia || m.alcaldia_nombre || (document.getElementById('editAlcaldia') && document.getElementById('editAlcaldia').selectedOptions[0] ? document.getElementById('editAlcaldia').selectedOptions[0].textContent.trim() : '') || reg.alcaldia || '';
      reg.alcaldia_nombre = reg.alcaldia;
      reg.senas = m.senas || m.senas_particulares || payload.senas_particulares || '';
      reg.senas_particulares = reg.senas;
      reg.fecha = m.fecha || m.fecha_extravio || payload.fecha_extravio || '';
      reg.fecha_extravio = reg.fecha;
      reg.color = m.color || payload.color || '';
      reg.colonia = cleanVal(m.colonia || payload.colonia || '', '');
      reg.calle = cleanVal(payload.calle || '', '');
      reg.direccion_ref = cleanVal(m.direccion_ref || payload.direccion_ref || '', '');
      reg.referencia = cleanVal(payload.referencia || '', '');
      reg = normalizeRegistro(reg);
      reg.calle = payload.calle || '';
      reg.direccion_ref = m.direccion_ref || payload.direccion_ref || '';
      reg.referencia = payload.referencia || '';
      reg.estatus = m.estatus || reg.estatus || 'En Búsqueda';
      if (m.foto || m.foto_url) {
        reg.foto = m.foto || m.foto_url;
        reg.foto_url = reg.foto;
      }
      // Refrescar tabla 8 columnas
      if (typeof global.pintarTablaCrud === 'function') global.pintarTablaCrud();
      else if (typeof renderTablaCrud === 'function') renderTablaCrud();
      if (typeof global.renderizarFichaTutor === 'function') {
        try { global.renderizarFichaTutor(reg); } catch (_) {}
      }
      toast(data.message || 'Cambios guardados.', 'success');
      renderTablaCrud();
      if (global.modalEditarBs) global.modalEditarBs.hide();
      else {
        const modal = document.getElementById('modalEditarRegistro');
        if (modal && global.bootstrap) {
          const inst = global.bootstrap.Modal.getInstance(modal);
          if (inst) inst.hide();
        }
      }
    } catch (err) {
      toast(err.message || 'No se pudo guardar.', 'error');
    } finally {
      if (btn) btn.disabled = false;
    }
  }


  async function eliminarRegistro(index) {
    if (typeof global.abrirModalEliminarRegistro === 'function') {
      return global.abrirModalEliminarRegistro(index);
    }
    if (typeof global.confirmarEliminarRegistro === 'function') {
      return global.confirmarEliminarRegistro(index);
    }
    toast('Modal de eliminar no disponible. Recarga la página.', 'warning');
  }

  function filtrarTablaCrud() {
    const q = document.getElementById('inputBuscadorCrud');
    renderTablaCrud(q ? q.value : '');
  }

  function bindCrudClicks() {
    if (document._rupeCrudBound) return;
    document._rupeCrudBound = true;
    document.addEventListener('click', function (ev) {
      const btn = ev.target.closest('[data-crud]');
      if (!btn) return;
      // Solo botones del CRUD tutor
      if (!btn.closest('#tbodyCrud') && !btn.closest('#tablaCrudTutor')) return;
      ev.preventDefault();
      ev.stopPropagation();
      const action = btn.getAttribute('data-crud');
      let index = parseInt(btn.getAttribute('data-index') || '-1', 10);
      const st = ensureState();
      const regs = st.registros || [];
      // Si el índice no es válido, intentar por data-folio de la fila
      if (index < 0 || index >= regs.length) {
        const tr = btn.closest('tr');
        const folio = tr && tr.getAttribute('data-folio');
        if (folio) {
          index = regs.findIndex(function (r) {
            return String(r.folio) === String(folio);
          });
        }
      }
      if (action === 'ver') {
        const folio = (regs[index] && regs[index].folio) || (btn.closest('tr') && btn.closest('tr').getAttribute('data-folio')) || '';
        seleccionarYVerPDF(folio);
      } else if (action === 'editar') {
        if (index < 0) {
          toast('No se encontró el registro a editar.', 'warning');
          return;
        }
        abrirModalEditar(index);
      } else if (action === 'eliminar') {
        if (index < 0) {
          toast('No se encontró el registro a eliminar.', 'warning');
          return;
        }
        eliminarRegistro(index);
      }
    });
  }


  function fillTutorFields() {
    const st = ensureState();
    const u = st.tutorActual || {};
    function setVal(id, v) {
      const el = document.getElementById(id);
      if (el && (el.value === '' || !el.value)) el.value = v || '';
    }
    setVal('regTutorNombre', u.nombre);
    setVal('regTutorTel', u.telefono);
    setVal('regTutorEmail', u.correo);
    setVal('perfilInputNombre', u.nombre);
    setVal('perfilInputTelefono', u.telefono);
    setVal('perfilInputCorreo', u.correo);
    const auto = document.getElementById('regTutorAuto');
    if (auto && u.nombre) auto.textContent = 'Datos de contacto de tu cuenta: ' + u.nombre;
  }

  function fillSelects() {
    const init = global.RUPE_INIT || {};
    const alcs = global.__RUPE_ALCALDIAS__ || init.alcaldias || [];
    const razas = global.__RUPE_RAZAS__ || init.razas || [];
    global.__RUPE_ALCALDIAS__ = alcs;
    global.__RUPE_RAZAS__ = razas;

    const selAlc = document.getElementById('regAlcaldia');
    if (selAlc && alcs.length) {
      const cur = selAlc.value;
      selAlc.innerHTML = '<option value="">Selecciona alcaldía…</option>';
      alcs.forEach(function (a) {
        const id = a.id_alcaldia || a.id || 0;
        if (!id) return;
        const opt = document.createElement('option');
        opt.value = String(id); // ID numérico de BD
        opt.textContent = a.nombre || ('Alcaldía ' + id);
        selAlc.appendChild(opt);
      });
      if (cur) selAlc.value = cur;
    }

    const selRaza = document.getElementById('regRaza');
    if (selRaza && razas.length) {
      // Si era input text, no aplica; debe ser SELECT
      if (selRaza.tagName !== 'SELECT') return;
      const cur = selRaza.value;
      selRaza.innerHTML = '<option value="">Selecciona raza…</option>';
      razas.forEach(function (r) {
        const id = r.id_raza || r.id || 0;
        if (!id) return;
        const opt = document.createElement('option');
        opt.value = String(id); // ID numérico de BD (id_raza)
        opt.textContent = (r.nombre || '') + (r.especie ? ' (' + r.especie + ')' : '');
        selRaza.appendChild(opt);
      });
      if (cur) selRaza.value = cur;
    }
  }

  function init() {
    applyInit();
    fillTutorFields();
    fillSelects();
    renderTablaCrud();
    actualizarKpis();
    bindCrudClicks();
    try { renderTablaCrud(); } catch (e) { console.warn(e); }
    if (typeof global.renderizarTodo === 'function') {
      try { global.renderizarTodo(); } catch (_) {}
      // Re-pintar tabla por si renderizarTodo usó datos viejos
      renderTablaCrud();
    }
    toast(
      (ensureState().registros.length
        ? ensureState().registros.length + ' registro(s) cargados desde la base de datos.'
        : 'Sin reportes aún. Crea el primero en Registro.'),
      'info'
    );
  }

  // Exponer globales usados por onclick legacy y otros módulos
  global.renderizarTablaCrud = renderTablaCrud;
  global.pintarTablaCrud = renderTablaCrud;
  global.filtrarTablaCrud = filtrarTablaCrud;
  global.abrirModalEditar = abrirModalEditar;
  global.eliminarRegistro = eliminarRegistro;
  global.seleccionarYVerPDF = seleccionarYVerPDF;
  async function refrescarCrudDesdeApi() {
    try {
      const data = await request('GET', '/api/tutor/mascotas');
      const arr = (data.data && data.data.mascotas) || data.mascotas || [];
      if (!Array.isArray(arr)) return;
      const st = ensureState();
      if (!arr.length) {
        // conservar lo que ya hay en pantalla si API vacía temporalmente
        if (st.registros && st.registros.length) {
          renderTablaCrud();
        }
        return;
      }
      st.registros = arr.map(function (m) {
        // Preferir map del app si existe (incluye foto src)
        if (typeof global.mapMascotaToState === 'function') {
          try { return normalizeRegistro(global.mapMascotaToState(m)); } catch (_) {}
        }
        return normalizeRegistro({
          id: m.id || m.id_mascota,
          id_mascota: m.id_mascota || m.id,
          folio: m.folio,
          nombre: m.nombre,
          raza: m.raza || m.raza_nombre || '',
          raza_nombre: m.raza_nombre || m.raza || '',
          id_raza: m.id_raza,
          alcaldia: m.alcaldia || m.alcaldia_nombre || '',
          alcaldia_nombre: m.alcaldia_nombre || m.alcaldia || '',
          id_alcaldia: m.id_alcaldia,
          colonia: m.colonia || '',
          calle: m.calle || '',
          direccion_ref: m.direccion_ref || '',
          referencia: m.referencia || '',
          fecha: m.fecha || m.fecha_extravio || '',
          fecha_extravio: m.fecha_extravio || m.fecha || '',
          estatus: m.estatus || 'En Búsqueda',
          foto: m.foto || m.foto_url || '',
          foto_url: m.foto_url || m.foto || '',
          especie: m.especie || 'perro',
          color: m.color || '',
          sexo: m.sexo || '',
          tamano: m.tamano || '',
          qr_token: m.qr_token || '',
        });
      });
      if (global.RUPE_INIT) global.RUPE_INIT.mascotas = st.registros.slice();
      try { global.state = st; } catch (_) {}
      renderTablaCrud();
      if (typeof actualizarKpis === 'function') actualizarKpis();
      if (typeof actualizarKpisCrud === 'function') actualizarKpisCrud();
    } catch (err) {
      console.warn('[RUPE CRUD] refresh', err);
    }
  }
  global.refrescarCrudDesdeApi = refrescarCrudDesdeApi;
  global.normalizeRegistro = normalizeRegistro;
  if (typeof global.mapMascotaToState !== 'function' && typeof mapMascotaToState === 'function') {
    global.mapMascotaToState = mapMascotaToState;
  }
  global.guardarEdicionRegistro = guardarEdicionRegistro;
  global.rupeRenderCrud = renderTablaCrud;
  global.rupeApplyDbInit = applyInit;
  global.RUPECrud = {
    init: init,
    renderTablaCrud: renderTablaCrud,
    request: request,
    ensureState: ensureState,
    toast: toast,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      setTimeout(init, 50);
    });
  } else {
    setTimeout(init, 50);
  }
})(window);
