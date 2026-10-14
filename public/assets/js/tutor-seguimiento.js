/**
 * RUPE CDMX — Seguimiento: tabla
 * | Fecha / Hora | Condición | Fotografía | Ubicación GPS | Referencia | Reportado por | Acciones |
 */
'use strict';

(function (global) {
  function toast(msg, type) {
    if (typeof global.rupeToast === 'function') return global.rupeToast(msg, type || 'info');
    if (typeof global.mostrarToastTutor === 'function') return global.mostrarToastTutor(msg, type || 'success');
  }

  function listRegistros() {
    if (global.state && Array.isArray(global.state.registros) && global.state.registros.length) {
      return global.state.registros;
    }
    if (global.RUPE_INIT && Array.isArray(global.RUPE_INIT.mascotas)) {
      return global.RUPE_INIT.mascotas;
    }
    return [];
  }

  function historialInit() {
    if (global.RUPE_INIT && Array.isArray(global.RUPE_INIT.historial_seguimiento)) {
      return global.RUPE_INIT.historial_seguimiento;
    }
    if (global.state && Array.isArray(global.state.historial_seguimiento)) {
      return global.state.historial_seguimiento;
    }
    return [];
  }

  function resolveFoto(url, especie) {
    var raw = String(url || '').trim();
    if (!raw || raw.toLowerCase() === 'null') {
      var e = String(especie || 'perro').toLowerCase();
      return e.indexOf('gato') >= 0
        ? '/assets/img/placeholder-gato.svg'
        : '/assets/img/placeholder-perro.svg';
    }
    if (raw.indexOf('data:image/') === 0 || /^https?:\/\//i.test(raw)) return raw;
    if (raw.charAt(0) === '/') return raw;
    if (raw.indexOf('assets/') === 0) return '/' + raw;
    if (/^mascota_/.test(raw)) return '/assets/uploads/' + raw;
    return '/' + raw.replace(/^\/+/, '');
  }

  function parseAvistamiento(s) {
    var d = '';
    if (typeof s === 'string') d = s;
    else if (s && typeof s === 'object') {
      d = String(s.descripcion || s.bitacora || s.referencia || '');
    }
    var out = {
      condicion: 'Avistamiento',
      referencia: '',
      reportadoPor: 'Ciudadano',
      fechaHora: '',
      lat: '',
      lng: '',
      foto: '',
      folio: '',
      mascota: '',
    };
    if (s && typeof s === 'object') {
      out.fechaHora = s.created_at || s.fecha || s.fechaHora || '';
      out.lat = s.latitud != null && s.latitud !== '' ? String(s.latitud) : s.lat != null ? String(s.lat) : '';
      out.lng = s.longitud != null && s.longitud !== '' ? String(s.longitud) : s.lng != null ? String(s.lng) : '';
      out.foto = s.foto_url || s.foto || '';
      out.folio = s.folio || '';
      out.mascota = s.mascota || s.nombre || '';
      if (s.usuario_nombre) out.reportadoPor = s.usuario_nombre;
      if (s.reporteroNombre) out.reportadoPor = s.reporteroNombre;
      if (s.tipo) out.condicion = String(s.tipo);
    }
    var m;
    m = d.match(/Avistamiento\s*:\s*([^·|]+)/i);
    if (m) out.condicion = m[1].trim();
    m = d.match(/Ref(?:erencia)?\s*:\s*([^·|]+)/i);
    if (m) out.referencia = m[1].trim();
    m = d.match(/Report[oó]\s*:\s*([^·|]+)/i);
    if (m) out.reportadoPor = m[1].trim();
    m = d.match(/GPS\s*:\s*([-\d.]+)\s*,\s*([-\d.]+)/i);
    if (m) {
      if (!out.lat) out.lat = m[1];
      if (!out.lng) out.lng = m[2];
    }
    if (!out.condicion || out.condicion === 'nota' || out.condicion === 'avistamiento') {
      if (/resguard/i.test(d) || /resguard/i.test(String(out.condicion))) out.condicion = 'Resguardado';
      else if (/visto|calle|observ/i.test(d) || /visto|observ/i.test(String(out.condicion))) out.condicion = 'Observado';
      else if (/avistamiento/i.test(d) || (s && s.tipo === 'avistamiento')) out.condicion = 'Observado';
      else if (s && s.tipo) out.condicion = String(s.tipo);
    }
    // Solo dos etiquetas visibles: Observado | Resguardado
    out.condicion = normalizarCondicionSeg(out.condicion, d);
    if (!out.referencia) {
      if (d && !/^Avistamiento/i.test(d)) out.referencia = d.slice(0, 140);
      else if (d) out.referencia = d.slice(0, 140);
    }
    if (s && typeof s === 'object') {
      var idNum = parseInt(s.id_seguimiento != null ? s.id_seguimiento : s.id, 10);
      if (isFinite(idNum) && idNum > 0) {
        out.id = idNum;
        out.id_seguimiento = idNum;
      }
      if (s.condicion) out.condicion = String(s.condicion);
      if (s.referencia) out.referencia = String(s.referencia);
      if (s.reportado_por) out.reportadoPor = String(s.reportado_por);
    }
    ['condicion','referencia','reportadoPor','fechaHora','lat','lng','foto','folio','mascota'].forEach(function (k) {
      var v = out[k];
      if (v == null || String(v).toLowerCase() === 'null') {
        if (k === 'condicion') out[k] = 'Observado';
        else if (k === 'reportadoPor') out[k] = 'Ciudadano';
        else out[k] = '';
      }
    });
    return out;
  }

  /**
   * Condiciones oficiales de avistamiento (coinciden con filtro y BD):
   * Observado | Resguardado | En albergue | Herido | Localizado
   */
  function normalizarCondicionSeg(cond, texto) {
    var c = String(cond || '').trim();
    var t = String(texto || '').trim();
    var blob = (c + ' ' + t).toLowerCase();

    // Ruido de sistema (no es avistamiento)
    if (/^(cambio_estatus|estatus|nota|ubicacion|ubicación|gps actualizado|registro inicial)$/i.test(c)) {
      return '';
    }

    if (/localiz/i.test(blob)) return 'Localizado';
    if (/herid|lesion|lesión|maltrat/i.test(blob)) return 'Herido';
    if (/albergue|refugio|rescate/i.test(blob)) return 'En albergue';
    if (/resguard/i.test(blob)) return 'Resguardado';
    if (/observ|visto|calle|libre|avistamiento|v[ií]a p[uú]blica/i.test(blob)) return 'Observado';

    // Si viene un valor ya oficial, respetarlo
    var oficiales = ['Observado', 'Resguardado', 'En albergue', 'Herido', 'Localizado'];
    for (var i = 0; i < oficiales.length; i++) {
      if (c.toLowerCase() === oficiales[i].toLowerCase()) return oficiales[i];
    }

    // Default seguro para avistamientos reales
    if (c) return 'Observado';
    return 'Observado';
  }


  /** Avistamientos reales (nueva estructura: condicion; legacy: tipo/descripcion) */
  function esAvistamientoUtil(s) {
    if (!s) return false;
    if (typeof s === 'string') {
      return /Avistamiento\s*:/i.test(s) || s.trim().length > 0;
    }
    var tipo = String(s.tipo || '').toLowerCase();
    var cond = String(s.condicion || '').trim();
    var d = String(s.descripcion || s.bitacora || '');
    if (/registro inicial/i.test(cond) || /gps actualizado/i.test(cond)) {
      return false;
    }
    if (cond) {
      return true;
    }
    if (tipo === 'cambio_estatus' || tipo === 'nota') return false;
    if (tipo === 'ubicacion' || tipo === 'ubicación') {
      return /Avistamiento\s*:/i.test(d);
    }
    if (tipo === 'avistamiento') return true;
    if (Number(s.id_seguimiento || s.id) > 0 && (cond || s.latitud || s.lat)) return true;
    return /Avistamiento\s*:/i.test(d);
  }

  /** Un solo registro por id_seguimiento numérico; fallback geo+minuto */
  function dedupeAvistamientos(list) {
    var byId = {};
    var byKey = {};
    var out = [];
    (Array.isArray(list) ? list : []).forEach(function (a) {
      if (!a) return;
      var idNum = parseInt(a.id_seguimiento != null ? a.id_seguimiento : a.id, 10) || 0;
      if (idNum > 0) {
        if (byId[idNum]) return;
        byId[idNum] = true;
        a.id = idNum;
        a.id_seguimiento = idNum;
        out.push(a);
        return;
      }
      // sin id: clave compuesta
      var latN = Number(a.lat != null ? a.lat : a.latitud);
      var lngN = Number(a.lng != null ? a.lng : a.longitud);
      var key = [
        String(a.folio || ''),
        String(a.condicion || '').toLowerCase(),
        isFinite(latN) ? latN.toFixed(4) : '',
        isFinite(lngN) ? lngN.toFixed(4) : '',
        String(a.fechaHora || a.created_at || '').slice(0, 16),
      ].join('|');
      if (byKey[key]) return;
      byKey[key] = true;
      out.push(a);
    });
    return out;
  }


  function formatFecha(s) {
    if (!s) return '—';
    try {
      var dt = new Date(s);
      if (!isNaN(dt.getTime())) {
        return dt.toLocaleString('es-MX', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
        });
      }
    } catch (_) {}
    return String(s);
  }

  function llenarSelectSeguimiento(forceList) {
    var sel = document.getElementById('selectFolioSeguimiento');
    if (!sel) return;
    var list = Array.isArray(forceList) ? forceList : listRegistros();
    var prev = sel.value || (global.state && global.state.folioSeleccionado) || '';

    // Conservar opciones PHP existentes si la lista aún no llegó
    if (!list.length) {
      // intentar desde opciones ya en el DOM
      var fromDom = [];
      Array.from(sel.options).forEach(function (op) {
        if (!op.value) return;
        fromDom.push({
          folio: op.value,
          nombre: (op.textContent || '').split('·')[1] || '',
          id: parseInt(op.getAttribute('data-id') || op.dataset.id || '0', 10) || 0,
          id_mascota: parseInt(op.getAttribute('data-id') || op.dataset.id || '0', 10) || 0,
        });
      });
      if (fromDom.length) list = fromDom;
    }

    sel.innerHTML = '';
    var o0 = document.createElement('option');
    o0.value = '';
    o0.textContent = list.length ? '— Todos los folios —' : '— Sin registros (recarga) —';
    sel.appendChild(o0);
    var seen = {};
    list.forEach(function (r) {
      if (!r) return;
      var folio = String(r.folio || '').trim();
      if (!folio || seen[folio]) return;
      seen[folio] = true;
      var opt = document.createElement('option');
      opt.value = folio;
      var idM = parseInt(r.id_mascota != null ? r.id_mascota : r.id, 10);
      opt.dataset.id = isFinite(idM) && idM > 0 ? String(idM) : '';
      opt.setAttribute('data-id', opt.dataset.id);
      opt.textContent =
        '#' + folio + ' · ' + (r.nombre || r.mascota || 'Mascota') +
        (r.estatus ? ' · ' + r.estatus : '');
      sel.appendChild(opt);
    });
    if (prev) {
      try { sel.value = String(prev); } catch (_) {}
    }
    // contador visible
    var badge = document.getElementById('badgeSeguimientoFolio');
    if (badge && !prev) {
      badge.innerHTML =
        '<i class="bi bi-shield-check text-success me-1"></i> Folios: <strong>' +
        list.length +
        '</strong>';
    }
  }
  global.llenarSelectSeguimiento = llenarSelectSeguimiento;

  function aplicarFiltrosTablaSeguimiento() {
    var tbody = document.getElementById('tbodySeguimiento');
    if (!tbody) return 0;
    var condRaw = '';
    var q = '';
    try {
      condRaw = String((document.getElementById('filtroSegCondicion') || {}).value || '').trim();
      q = String((document.getElementById('filtroSegBuscar') || {}).value || '').toLowerCase().trim();
    } catch (_) {}
    var condNorm = condRaw ? normalizarCondicionSeg(condRaw, '') : '';
    var rows = Array.from(tbody.querySelectorAll('tr'));
    var visible = 0;
    var emptyRow = null;
    rows.forEach(function (tr) {
      if (tr.querySelector('td[colspan]')) {
        emptyRow = tr;
        tr.style.display = 'none';
        return;
      }
      var cAttr = String(tr.getAttribute('data-condicion') || '').trim();
      var cNorm = normalizarCondicionSeg(cAttr, cAttr);
      var text = String(tr.textContent || '').toLowerCase();
      var okC = !condNorm || cNorm.toLowerCase() === condNorm.toLowerCase();
      var okQ = !q || text.indexOf(q) >= 0;
      var show = okC && okQ;
      tr.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    if (emptyRow) {
      emptyRow.style.display = visible === 0 ? '' : 'none';
      if (visible === 0) {
        emptyRow.innerHTML = '<td colspan="11" class="text-center text-muted py-4">No hay avistamientos con la condición seleccionada.</td>';
      }
    } else if (visible === 0 && rows.length) {
      // sin fila vacía: no forzar
    }
    return visible;
  }
  global.aplicarFiltrosTablaSeguimiento = aplicarFiltrosTablaSeguimiento;

  function bindFiltrosSeguimientoUI() {
    var sel = document.getElementById('selectFolioSeguimiento');
    if (sel && !sel._rupeSegBound) {
      sel._rupeSegBound = true;
      sel.addEventListener('change', function () {
        var folio = String(sel.value || '');
        if (global.state) global.state.folioSeleccionado = folio;
        Promise.resolve(cargarSeguimientoFolio(folio)).then(function () {
          aplicarFiltrosTablaSeguimiento();
        });
      });
    }
    var btn = document.getElementById('btnFiltrarSeguimiento');
    if (btn && !btn._rupeSegBound) {
      btn._rupeSegBound = true;
      btn.addEventListener('click', function () {
        var folio = sel ? String(sel.value || '') : '';
        Promise.resolve(cargarSeguimientoFolio(folio)).then(function () {
          aplicarFiltrosTablaSeguimiento();
        });
      });
    }
    ['filtroSegCondicion', 'filtroSegBuscar'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el && !el._rupeSegBound) {
        el._rupeSegBound = true;
        el.addEventListener(id === 'filtroSegBuscar' ? 'input' : 'change', function () {
          aplicarFiltrosTablaSeguimiento();
        });
      }
    });
  }
  global.bindFiltrosSeguimientoUI = bindFiltrosSeguimientoUI;
  global.aplicarFiltrosSeguimiento = aplicarFiltrosTablaSeguimiento;
  global.renderTablaSeguimiento = aplicarFiltrosTablaSeguimiento;

  async function refrescarFiltrosSeguimiento() {
    try {
      var r = await fetch('/api/tutor/mascotas', {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });
      var data = await r.json().catch(function () { return {}; });
      var arr = (data.data && data.data.mascotas) || data.mascotas || [];
      var hist = (data.data && data.data.historial_seguimiento) || data.historial_seguimiento || [];
      if (Array.isArray(arr) && arr.length) {
        if (global.state) global.state.registros = arr.slice();
        if (!global.RUPE_INIT) global.RUPE_INIT = {};
        global.RUPE_INIT.mascotas = arr.slice();
        llenarSelectSeguimiento(arr);
      } else {
        llenarSelectSeguimiento();
      }
      if (Array.isArray(hist) && hist.length) {
        if (!global.RUPE_INIT) global.RUPE_INIT = {};
        global.RUPE_INIT.historial_seguimiento = hist;
        if (global.state) global.state.historial_seguimiento = hist;
      }
      return { mascotas: arr, historial: hist };
    } catch (err) {
      console.warn('[RUPE SEG] filtros', err);
      llenarSelectSeguimiento();
      return { mascotas: [], historial: [] };
    }
  }
  global.refrescarFiltrosSeguimiento = refrescarFiltrosSeguimiento;

  function pintarTablaAvistamientos(items, reg) {
    items = dedupeAvistamientos(
      (Array.isArray(items) ? items : [])
        .map(function (raw) {
          var p = typeof raw === 'object' && raw.condicion ? raw : parseAvistamiento(raw);
          var base = typeof raw === 'object' ? Object.assign({}, raw, p) : p;
          base.condicion = normalizarCondicionSeg(base.condicion, base.descripcion || base.referencia || '');
          return base.condicion ? base : null;
        })
        .filter(Boolean)
    );
    var tbody = document.getElementById('tbodySeguimiento');
    var badge = document.getElementById('badgeSeguimientoFolio');
    if (!tbody) return;

    if (badge) {
      if (reg && reg.folio) {
        badge.innerHTML =
          '<i class="bi bi-shield-check text-success me-1"></i> Folio Activo: <strong>#' +
          reg.folio +
          (reg.nombre ? ' (' + reg.nombre + ')' : '') +
          '</strong>';
      } else {
        badge.innerHTML =
          '<i class="bi bi-shield-check text-success me-1"></i> Folio Activo: <strong>Todos</strong>';
      }
    }

    tbody.innerHTML = '';
    items = dedupeAvistamientos(items || []);
    if (!items || !items.length) {
      tbody.innerHTML =
        '<tr><td colspan="11" class="text-center py-4 text-muted">Aún no hay avistamientos registrados. Envía uno desde <strong>GPS / QR</strong>.</td></tr>';
      return;
    }

    items.forEach(function (raw, idx) {
      var a = Object.assign({}, parseAvistamiento(raw), typeof raw === 'object' ? raw : {});
      a.condicion = normalizarCondicionSeg(a.condicion || (raw && raw.condicion), a.descripcion || a.referencia || '');
      if (!a.condicion) return;
      var fotoSrc = resolveFoto(
        a.foto ||
          (raw && (raw.foto_url || raw.foto)) ||
          (reg && (reg.foto || reg.foto_url)),
        (reg && reg.especie) || ''
      );
      var gpsTxt =
        a.lat && a.lng
          ? Number(a.lat).toFixed(5) + ', ' + Number(a.lng).toFixed(5)
          : 'Sin GPS';
      var badgeCls = (function (c) {
        c = String(c || '').toLowerCase();
        if (c.indexOf('resguard') >= 0) return 'bg-success';
        if (c.indexOf('localiz') >= 0) return 'bg-primary';
        if (c.indexOf('herid') >= 0) return 'bg-danger';
        if (c.indexOf('albergue') >= 0) return 'bg-info text-dark';
        return 'bg-warning text-dark';
      })(a.condicion);

      var descFull = '';
      if (raw && typeof raw === 'object') {
        descFull = String(raw.descripcion || a.referencia || '');
      } else {
        descFull = String(a.referencia || '');
      }

      var tr = document.createElement('tr');
      var idSeg = parseInt(a.id_seguimiento != null ? a.id_seguimiento : a.id, 10) || 0;
      if (!isFinite(idSeg) || idSeg < 0) idSeg = 0;
      tr.setAttribute('data-id', String(idSeg));
      tr.setAttribute('data-idx', String(idx));
      tr.setAttribute('data-lat', a.lat || '');
      tr.setAttribute('data-lng', a.lng || '');
      tr.setAttribute('data-foto', fotoSrc);
      tr.setAttribute('data-condicion', a.condicion || '');
      tr.setAttribute('data-ref', a.referencia || '');
      tr.setAttribute('data-reportado', a.reportadoPor || '');
      tr.setAttribute('data-fecha', a.fechaHora || '');
      tr.setAttribute('data-desc', encodeURIComponent(descFull));
      tr.setAttribute('data-folio', (reg && reg.folio) || a.folio || '');
      tr.setAttribute('data-nombre', (reg && reg.nombre) || a.mascota || '');

      var contacto = a.contacto || a.reportadoPor || a.reportado_por || 'Ciudadano';
      var tel = a.telefono || '';
      var mail = a.correo || '';
      var folioTxt = a.folio || (reg && reg.folio) || '';
      tr.innerHTML =
        '<td class="ps-4 small fw-semibold">#' + String(folioTxt).replace(/</g, '&lt;') + '</td>' +
        '<td class="small text-nowrap">' + formatFecha(a.fechaHora || a.created_at) + '</td>' +
        '<td><span class="badge ' + badgeCls + '">' + String(a.condicion || '—').replace(/</g, '&lt;') + '</span></td>' +
        '<td><img src="' + fotoSrc + '" alt="Foto" width="56" height="56" class="rounded border shadow-sm" style="object-fit:cover;background:#f0f0f0;cursor:pointer" onclick="detalleAvistamientoSeguimiento(this.closest(\'tr\'))" onerror="this.onerror=null;this.src=\'/assets/img/placeholder-mascota.svg\'"></td>' +
        '<td class="small">' + String(a.referencia || '—').replace(/</g, '&lt;').slice(0, 80) + '</td>' +
        '<td class="small">' + String(contacto).replace(/</g, '&lt;') + '</td>' +
        '<td class="small">' + String(tel || '—').replace(/</g, '&lt;') + '</td>' +
        '<td class="small">' + String(mail || '—').replace(/</g, '&lt;') + '</td>' +
        '<td class="small"><code>' + (a.lat || a.latitud || '—') + '</code></td>' +
        '<td class="small"><code>' + (a.lng || a.longitud || '—') + '</code></td>' +
        '<td class="text-end pe-4 text-nowrap">' +
        (a.lat && a.lng
          ? '<button type="button" class="btn btn-sm btn-outline-primary me-1" title="Ver mapa" data-lat="' + a.lat + '" data-lng="' + a.lng + '" onclick="verMapaSeguimiento(this)"><i class="bi bi-map"></i></button>'
          : '') +
        '<button type="button" class="btn btn-sm btn-outline-secondary" title="Ver detalles" onclick="detalleAvistamientoSeguimiento(this.closest(\'tr\'))"><i class="bi bi-eye"></i></button></td>';
      tbody.appendChild(tr);
    });
    try { aplicarFiltrosTablaSeguimiento(); } catch (_) {}
  }

  async function cargarSeguimientoFolio(folio) {
    var list = listRegistros();
    var reg = null;
    if (folio) {
      reg = list.find(function (r) {
        return String(r.folio) === String(folio);
      });
    }
    if (global.state && folio) global.state.folioSeleccionado = folio;

    // Sin folio: solo avistamientos del historial API (dedupe)
    if (!folio) {
      var hist = dedupeAvistamientos(
        historialInit().filter(function (h) {
          return esAvistamientoUtil(h);
        })
      );
      pintarTablaAvistamientos(hist, null);
      try {
        var r = await fetch('/api/tutor/mascotas', {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });
        var data = await r.json().catch(function () { return {}; });
        var arr = (data.data && data.data.mascotas) || data.mascotas || [];
        if (Array.isArray(arr) && arr.length) {
          if (global.state) global.state.registros = arr.slice();
          if (global.RUPE_INIT) global.RUPE_INIT.mascotas = arr.slice();
          llenarSelectSeguimiento();
        }
      } catch (_) {}
      return;
    }

    if (!reg) {
      // intentar resolver ID vía lista API
      try {
        var r0 = await fetch('/api/tutor/mascotas', {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });
        var d0 = await r0.json().catch(function () { return {}; });
        var arr0 = (d0.data && d0.data.mascotas) || d0.mascotas || [];
        if (Array.isArray(arr0) && arr0.length) {
          if (global.state) global.state.registros = arr0.slice();
          if (global.RUPE_INIT) global.RUPE_INIT.mascotas = arr0.slice();
          list = arr0;
          reg = list.find(function (r) { return String(r.folio) === String(folio); });
          llenarSelectSeguimiento();
        }
      } catch (_) {}
    }
    if (!reg) {
      pintarTablaAvistamientos([], null);
      return;
    }

    var id = reg.id || reg.id_mascota;
    if (!id) {
      pintarTablaAvistamientos([], null);
      return;
    }

    // Solo API de esta mascota (nunca mezclar seeds ni historial local)
    try {
      var res = await fetch('/api/tutor/mascotas/' + encodeURIComponent(id), {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      });
      var data = await res.json().catch(function () { return {}; });
      var segs = (data.data && data.data.seguimientos) || data.seguimientos || [];
      var avis = dedupeAvistamientos(
        (Array.isArray(segs) ? segs : [])
          .filter(function (s) {
            return esAvistamientoUtil(s);
          })
          .map(function (s) {
            var p = parseAvistamiento(s);
            var cond = normalizarCondicionSeg(p.condicion, s.descripcion || p.referencia || '');
            if (!cond) {
              // forzar etiqueta si tipo avistamiento
              if (String(s.tipo || '').toLowerCase() === 'avistamiento') cond = 'Observado';
              else return null;
            }
            return Object.assign({}, s, {
              id: parseInt(s.id_seguimiento != null ? s.id_seguimiento : (s.id || p.id), 10) || 0,
              id_seguimiento: parseInt(s.id_seguimiento != null ? s.id_seguimiento : (s.id || p.id), 10) || 0,
              fechaHora: p.fechaHora || s.created_at,
              condicion: cond,
              referencia: p.referencia,
              reporteroNombre: p.reportadoPor,
              reportadoPor: p.reportadoPor,
              lat: p.lat || s.latitud,
              lng: p.lng || s.longitud,
              foto: p.foto || s.foto_url || '',
              foto_url: s.foto_url || p.foto || '',
              descripcion: s.descripcion || p.referencia,
            });
          })
          .filter(Boolean)
      );
      reg.avistamientos = avis;
      pintarTablaAvistamientos(avis, reg);
      var first = avis.find(function (a) {
        return a.lat && a.lng;
      });
      if (first) {
        try {
          mostrarMapaSeguimiento(Number(first.lat), Number(first.lng));
        } catch (_) {}
      }
    } catch (err) {
      console.warn('[RUPE SEG]', err);
      pintarTablaAvistamientos([], reg);
    }
  }
  function limpiarFilasDuplicadasSeguimiento() {
    var tbody = document.getElementById('tbodySeguimiento');
    if (!tbody) return;
    var seen = {};
    Array.from(tbody.querySelectorAll('tr[data-id]')).forEach(function (tr) {
      var id = String(tr.getAttribute('data-id') || '');
      if (!id || id === '0') {
        // dedupe by text content key
        var key = (tr.textContent || '').replace(/\s+/g, ' ').trim();
        if (seen['t:' + key]) {
          tr.remove();
          return;
        }
        seen['t:' + key] = true;
        return;
      }
      if (seen[id]) {
        tr.remove();
        return;
      }
      seen[id] = true;
    });
  }
  global.limpiarFilasDuplicadasSeguimiento = limpiarFilasDuplicadasSeguimiento;


  var _segMapMain = null;
  var _segMapMarker = null;
  var _segMapModal = null;
  var _segMapModalMarker = null;

  function ensureLeaflet() {
    return typeof L !== 'undefined' && L.map;
  }

  function mostrarMapaSeguimiento(lat, lng, containerId) {
    lat = Number(lat);
    lng = Number(lng);
    if (!isFinite(lat) || !isFinite(lng)) return;
    containerId = containerId || 'mapaSeguimiento';
    var el = document.getElementById(containerId);
    if (!el) return;
    if (!ensureLeaflet()) {
      console.warn('[RUPE SEG] Leaflet no cargado');
      return;
    }

    if (containerId === 'mapaSeguimiento') {
      var wrap = document.getElementById('mapaSeguimientoWrap');
      if (wrap) wrap.classList.remove('d-none');
      try {
        if (!_segMapMain) {
          _segMapMain = L.map(el).setView([lat, lng], 16);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
          }).addTo(_segMapMain);
          _segMapMarker = L.marker([lat, lng]).addTo(_segMapMain);
        } else {
          _segMapMain.setView([lat, lng], 16);
          if (_segMapMarker) {
            _segMapMarker.setLatLng([lat, lng]);
          } else {
            _segMapMarker = L.marker([lat, lng]).addTo(_segMapMain);
          }
        }
        setTimeout(function () {
          try { _segMapMain.invalidateSize(); } catch (_) {}
        }, 200);
      } catch (err) {
        console.warn('[RUPE SEG] mapa principal', err);
      }
      return;
    }

    if (containerId === 'mapaSeguimientoModal') {
      try {
        if (_segMapModal) {
          try { _segMapModal.remove(); } catch (_) {}
          _segMapModal = null;
          _segMapModalMarker = null;
        }
        // limpiar contenedor por si Leaflet dejó basura
        el.innerHTML = '';
        if (el._leaflet_id) {
          try { delete el._leaflet_id; } catch (_) {}
        }
        _segMapModal = L.map(el).setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; OpenStreetMap',
        }).addTo(_segMapModal);
        _segMapModalMarker = L.marker([lat, lng]).addTo(_segMapModal);
        setTimeout(function () {
          try { _segMapModal.invalidateSize(); } catch (_) {}
        }, 250);
        setTimeout(function () {
          try { _segMapModal.invalidateSize(); } catch (_) {}
        }, 500);
      } catch (err) {
        console.warn('[RUPE SEG] mapa modal', err);
      }
    }
  }
  global.mostrarMapaSeguimiento = mostrarMapaSeguimiento;

  function verMapaSeguimiento(btn) {
    if (!btn) return;
    var lat = Number(btn.getAttribute('data-lat') || btn.dataset.lat || '');
    var lng = Number(btn.getAttribute('data-lng') || btn.dataset.lng || '');
    if (!isFinite(lat) || !isFinite(lng)) {
      var tr = btn.closest ? btn.closest('tr') : null;
      if (tr) {
        lat = Number(tr.getAttribute('data-lat') || '');
        lng = Number(tr.getAttribute('data-lng') || '');
      }
    }
    if (!isFinite(lat) || !isFinite(lng)) return;
    mostrarMapaSeguimiento(lat, lng, 'mapaSeguimiento');
    var wrap = document.getElementById('mapaSeguimientoWrap');
    if (wrap) {
      try { wrap.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (_) {}
    }
  }
  global.verMapaSeguimiento = verMapaSeguimiento;

  function detalleAvistamientoSeguimiento(tr) {
    if (!tr) return;
    var foto = tr.getAttribute('data-foto') || '/assets/img/placeholder-mascota.svg';
    var folio = tr.getAttribute('data-folio') || '';
    var fecha = tr.getAttribute('data-fecha') || '';
    var condicion = tr.getAttribute('data-condicion') || '—';
    var lat = tr.getAttribute('data-lat') || '';
    var lng = tr.getAttribute('data-lng') || '';
    var ref = tr.getAttribute('data-ref') || '—';
    var reportado = tr.getAttribute('data-reportado') || '—';
    var desc = '—';
    try {
      desc = decodeURIComponent(tr.getAttribute('data-desc') || '') || ref || '—';
    } catch (_) {
      desc = ref || '—';
    }

    var setTxt = function (id, val) {
      var el = document.getElementById(id);
      if (el) el.textContent = val != null && String(val).trim() !== '' ? String(val) : '—';
    };

    var img = document.getElementById('modalSegFoto');
    if (img) {
      img.src = foto;
      img.onerror = function () {
        this.onerror = null;
        this.src = '/assets/img/placeholder-mascota.svg';
      };
    }
    setTxt('modalSegFolio', folio ? '#' + folio : '—');
    setTxt('modalSegFecha', fecha ? formatFecha(fecha) : '—');
    setTxt('modalSegCondicion', condicion);
    setTxt(
      'modalSegGps',
      lat && lng ? Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5) : 'Sin GPS'
    );
    setTxt('modalSegReferencia', ref);
    setTxt('modalSegReportado', reportado);
    setTxt('modalSegDesc', desc);

    var modalEl = document.getElementById('modalDetalleAvistamiento');
    if (!modalEl) return;

    var showMap = function () {
      if (lat && lng && isFinite(Number(lat)) && isFinite(Number(lng))) {
        mostrarMapaSeguimiento(Number(lat), Number(lng), 'mapaSeguimientoModal');
      }
    };

    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
      var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modalEl.addEventListener(
        'shown.bs.modal',
        function onShown() {
          modalEl.removeEventListener('shown.bs.modal', onShown);
          showMap();
        },
        { once: true }
      );
      modal.show();
    } else {
      modalEl.classList.add('show');
      modalEl.style.display = 'block';
      setTimeout(showMap, 200);
    }

    // También mostrar mapa principal bajo la tabla
    if (lat && lng && isFinite(Number(lat)) && isFinite(Number(lng))) {
      try {
        mostrarMapaSeguimiento(Number(lat), Number(lng), 'mapaSeguimiento');
      } catch (_) {}
    }
  }
  global.detalleAvistamientoSeguimiento = detalleAvistamientoSeguimiento;

  global.cargarSeguimientoFolio = cargarSeguimientoFolio;

    document.addEventListener('DOMContentLoaded', function () {
    var folioQ = '';
    var segQ = 0;
    try {
      var p = new URLSearchParams(window.location.search || '');
      folioQ = p.get('folio') || '';
      segQ = parseInt(p.get('seg') || '0', 10) || 0;
    } catch (_) {}
    if (!folioQ) {
      try {
        folioQ = sessionStorage.getItem('rupe_seg_folio') || '';
        sessionStorage.removeItem('rupe_seg_folio');
      } catch (_) {}
    }
    if (!segQ) {
      try {
        segQ = parseInt(sessionStorage.getItem('rupe_seg_id') || '0', 10) || 0;
        sessionStorage.removeItem('rupe_seg_id');
      } catch (_) {}
    }
    bindFiltrosSeguimientoUI();
    llenarSelectSeguimiento();
    // Cargar folios reales desde API para el filtro
    Promise.resolve(refrescarFiltrosSeguimiento()).then(function () {
      bindFiltrosSeguimientoUI();
      llenarSelectSeguimiento();
    }).catch(function () {
      llenarSelectSeguimiento();
    });
    var after = function () {
      aplicarFiltrosTablaSeguimiento();
      if (typeof limpiarFilasDuplicadasSeguimiento === 'function') limpiarFilasDuplicadasSeguimiento();
      // Si venimos de un alta, resaltar solo ese id (no borrar otros del folio)
      if (segQ > 0) {
        var tbody = document.getElementById('tbodySeguimiento');
        if (tbody) {
          var rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
          var seen = {};
          rows.forEach(function (tr) {
            var id = parseInt(tr.getAttribute('data-id') || '0', 10) || 0;
            if (id <= 0) {
              tr.remove();
              return;
            }
            if (seen[id]) {
              tr.remove();
              return;
            }
            seen[id] = true;
          });
        }
      }
    };
    if (folioQ) {
      var sel = document.getElementById('selectFolioSeguimiento');
      if (sel) {
        try { sel.value = folioQ; } catch (_) {}
      }
      Promise.resolve(cargarSeguimientoFolio(folioQ)).then(after).catch(after);
    } else {
      Promise.resolve(cargarSeguimientoFolio('')).then(after).catch(after);
    }
  });

})(typeof window !== 'undefined' ? window : globalThis);
