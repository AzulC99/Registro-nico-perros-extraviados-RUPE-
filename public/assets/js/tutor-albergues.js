/**
 * RUPE CDMX — Albergues (API + BD real + mapa GPS)
 * public/assets/js/tutor-albergues.js
 */
'use strict';

(function (global) {
  var albMapa = null;
  var albMarker = null;
  var viewMapa = null;
  var viewMarker = null;

  function csrf() {
    return global.__CSRF__ || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  }

  function toast(msg, type) {
    if (typeof global.mostrarToastTutor === 'function') {
      global.mostrarToastTutor(msg, type || 'info');
      return;
    }
    if (typeof global.rupeToast === 'function') {
      global.rupeToast(msg, type || 'info');
      return;
    }
    console.log('[albergues]', msg);
  }

  function albIdNum(a) {
    var n = parseInt(a && (a.id_albergue != null ? a.id_albergue : a.id), 10);
    return Number.isFinite(n) && n > 0 ? n : 0;
  }

  function normalizarFila(a) {
    if (!a || typeof a !== 'object') return null;
    var id = albIdNum(a);
    if (id <= 0) return null;
    var lat = a.latitud != null && a.latitud !== '' ? Number(a.latitud) : null;
    var lng = a.longitud != null && a.longitud !== '' ? Number(a.longitud) : null;
    return {
      id_albergue: id,
      id: id,
      nombre: String(a.nombre || ''),
      tipo: String(a.tipo || 'Albergue'),
      alcaldia: String(a.alcaldia || a.alcaldia_nombre || ''),
      id_alcaldia: parseInt(a.id_alcaldia, 10) || 0,
      direccion: String(a.direccion || ''),
      telefono: String(a.telefono || ''),
      correo: String(a.correo || a.email || '').trim(),
      email: String(a.correo || a.email || '').trim(),
      latitud: lat != null && !isNaN(lat) ? lat : null,
      longitud: lng != null && !isNaN(lng) ? lng : null,
      capacidad: parseInt(a.capacidad, 10) || 0,
      ocupacion: parseInt(a.ocupacion, 10) || 0,
      activo: parseInt(a.activo, 10) === 0 ? 0 : 1
    };
  }

  function ensureState() {
    if (typeof global.state === 'undefined' || !global.state) {
      global.state = { albergues: [], registros: [] };
    }
    if (!Array.isArray(global.state.albergues)) global.state.albergues = [];
    return global.state;
  }

  async function cargarMascotasParaKpis() {
    try {
      var res = await fetch('/api/tutor/mascotas', {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf()
        },
        credentials: 'same-origin'
      });
      var data = await res.json().catch(function () { return {}; });
      var arr = [];
      if (data && data.data && Array.isArray(data.data.mascotas)) arr = data.data.mascotas;
      else if (data && Array.isArray(data.data)) arr = data.data;
      else if (data && Array.isArray(data.mascotas)) arr = data.mascotas;
      if (arr.length) {
        if (!global.state) global.state = {};
        global.state.registros = arr;
        if (!global.RUPE_INIT) global.RUPE_INIT = {};
        global.RUPE_INIT.mascotas = arr;
      }
      return arr;
    } catch (_) {
      return [];
    }
  }

  async function cargarAlberguesDesdeApi() {
    var st = ensureState();
    var loaded = false;
    try {
      var res = await fetch('/api/tutor/albergues', {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf()
        },
        credentials: 'same-origin'
      });
      var data = await res.json().catch(function () { return {}; });
      var rows = [];
      if (Array.isArray(data)) rows = data;
      else if (data && Array.isArray(data.data)) rows = data.data;
      else if (data && data.data && Array.isArray(data.data.data)) rows = data.data.data;
      st.albergues = rows.map(normalizarFila).filter(Boolean);
      loaded = st.albergues.length > 0;
    } catch (err) {
      console.warn('[RUPE] cargarAlbergues', err);
    }
    if (!loaded && Array.isArray(global.__RUPE_ALBERGUES__) && global.__RUPE_ALBERGUES__.length) {
      st.albergues = global.__RUPE_ALBERGUES__.map(normalizarFila).filter(Boolean);
    }
    await cargarMascotasParaKpis();
    renderizarDirectorioAlbergues();
    renderizarCasosResguardo();
    actualizarKpisAlbergues();
    return st.albergues;
  }

  function normTxtAlb(s) {
    s = String(s || '').toLowerCase().trim();
    try { s = s.normalize('NFD').replace(/[\u0300-\u036f]/g, ''); } catch (_) {}
    return s.replace(/\s+/g, ' ');
  }

  function syncAlberguesState() {
    var st = ensureState();
    // Unificar con state global del panel tutor (inline)
    if (global.state && Array.isArray(global.state.albergues) && global.state.albergues.length) {
      st.albergues = global.state.albergues;
    } else if (st.albergues && st.albergues.length && global.state) {
      global.state.albergues = st.albergues;
    }
    return st.albergues || [];
  }

  function obtenerListaAlberguesFiltrada() {
    var list = syncAlberguesState().slice();
    var q = normTxtAlb((document.getElementById('filtroAlbBuscar') || {}).value || '');
    var selAlc = document.getElementById('filtroAlbAlcaldia');
    var selOpt = selAlc && selAlc.selectedOptions ? selAlc.selectedOptions[0] : null;
    var alcVal = (selOpt && String(selOpt.value || '').trim() !== '') ? String(selOpt.textContent || '').trim() : '';
    var alcId = 0;
    if (selOpt) {
      alcId = parseInt(selOpt.getAttribute('data-id-alcaldia') || '0', 10) || 0;
      if (!alcId && /^\d+$/.test(String(selOpt.value || '').trim())) alcId = parseInt(selOpt.value, 10) || 0;
    }
    var tipoVal = String((document.getElementById('filtroAlbTipo') || {}).value || '').trim();
    var alcN = normTxtAlb(alcVal);
    var tipoN = normTxtAlb(tipoVal);

    if (q) {
      list = list.filter(function (a) {
        return normTxtAlb([a.nombre, a.direccion, a.telefono, a.correo, a.alcaldia, a.alcaldia_nombre, a.tipo, a.id_albergue].join(' ')).indexOf(q) >= 0;
      });
    }
    if (alcVal) {
      list = list.filter(function (a) {
        var an = normTxtAlb(a.alcaldia || a.alcaldia_nombre || '');
        var idA = parseInt(a.id_alcaldia, 10) || 0;
        if (alcId > 0 && idA === alcId) return true;
        if (an && an === alcN) return true;
        if (an && alcN && (an.indexOf(alcN) >= 0 || alcN.indexOf(an) >= 0)) return true;
        return false;
      });
    }
    if (tipoVal) {
      list = list.filter(function (a) {
        var tn = normTxtAlb(a.tipo || 'Albergue');
        return tn === tipoN;
      });
    }
    return list;
  }

  function aplicarFiltrosAlbergues() {
    try {
      // NO reconstruir el select aquí (perdía la selección del usuario)
      syncAlberguesState();
      renderizarDirectorioAlbergues();
      var n = obtenerListaAlberguesFiltrada().length;
      var total = (ensureState().albergues || []).length;
      toast(n + ' de ' + total + ' albergue(s) con el filtro aplicado.', n ? 'success' : 'info');
    } catch (err) {
      console.warn('[RUPE] aplicarFiltrosAlbergues', err);
      toast('No se pudieron aplicar los filtros. Recarga la página.', 'warning');
    }
  }

  function renderizarDirectorioAlbergues() {
    var tbody = document.getElementById('tbodyDirectorioAlbergues');
    if (!tbody) return;
    var listAll = ensureState().albergues || [];
    var list = obtenerListaAlberguesFiltrada();
    tbody.innerHTML = '';
    if (!listAll.length) {
      tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-muted">No hay albergues en la base de datos. Agrega el primero.</td></tr>';
      return;
    }
    if (!list.length) {
      tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-muted">Ningún albergue coincide con los filtros. <button type="button" class="btn btn-link btn-sm" id="btnLimpiarFiltrosAlb">Limpiar filtros</button></td></tr>';
      var btnL = document.getElementById('btnLimpiarFiltrosAlb');
      if (btnL) {
        btnL.addEventListener('click', function () {
          var a = document.getElementById('filtroAlbBuscar');
          var b = document.getElementById('filtroAlbAlcaldia');
          var c = document.getElementById('filtroAlbTipo');
          if (a) a.value = '';
          if (b) b.value = '';
          if (c) c.value = '';
          renderizarDirectorioAlbergues();
        });
      }
      return;
    }
    list.forEach(function (alb) {
      var idNum = albIdNum(alb);
      var ocupados = Number(alb.ocupacion) || 0;
      var cap = Number(alb.capacidad) || 0;
      var pct = cap > 0 ? Math.min(100, Math.round((ocupados / cap) * 100)) : 0;
      var colorBarra = pct >= 100 ? 'bg-danger' : pct >= 70 ? 'bg-warning' : 'bg-success';
      var lat = alb.latitud;
      var lng = alb.longitud;
      var hasGps = lat != null && lng != null && !isNaN(lat) && !isNaN(lng);
      var gpsTxt = hasGps ? Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5) : '—';
      var activo = Number(alb.activo) === 0 ? 0 : 1;
      var tr = document.createElement('tr');
      tr.dataset.id = String(idNum);
      tr.innerHTML =
        '<td class="ps-4 fw-semibold">' + idNum + '</td>' +
        '<td><strong>' + esc(alb.nombre) + '</strong>' +
        (alb.direccion ? '<div class="small text-muted">' + esc(alb.direccion) + '</div>' : '') +
        '</td>' +
        '<td><span class="badge bg-light text-dark border">' + esc(alb.tipo || 'Albergue') + '</span></td>' +
        '<td>' + esc(alb.alcaldia || '—') + '</td>' +
        '<td class="small">' + esc(alb.telefono || '—') + '</td>' +
        '<td class="small">' + esc(alb.correo || alb.email || '—') + '</td>' +
        '<td class="small text-nowrap">' +
        gpsTxt +
        (hasGps
          ? ' <button type="button" class="btn btn-link btn-sm p-0 ms-1" title="Ver mapa" data-alb-map="' + idNum + '"><i class="bi bi-map"></i></button>'
          : '') +
        '</td>' +
        '<td class="text-center">' + (cap || '—') + '</td>' +
        '<td style="min-width:110px;">' +
        '<div class="d-flex justify-content-between small mb-1"><span>' + ocupados + ' / ' + (cap || 0) + '</span><span>' + pct + ' %</span></div>' +
        '<div class="progress" style="height:6px;"><div class="progress-bar ' + colorBarra + '" style="width:' + pct + '%"></div></div>' +
        '</td>' +
        '<td class="text-nowrap">' +
        '<button type="button" class="btn btn-sm ' +
        (activo === 1 ? 'btn-success' : 'btn-outline-secondary') +
        '" data-alb-toggle="' + idNum + '" data-alb-activo="' + activo + '" title="Cambiar activo/inactivo">' +
        (activo === 1 ? '<i class="bi bi-check-circle me-1"></i>Activo' : '<i class="bi bi-pause-circle me-1"></i>Inactivo') +
        '</button></td>' +
        '<td class="text-end pe-4 text-nowrap">' +
        '<button type="button" class="btn btn-sm btn-outline-danger" data-alb-del="' + idNum + '"' +
        (ocupados > 0
          ? ' disabled title="Tiene casos asignados"'
          : ' title="Eliminar" onclick="window.rupeEliminarAlb(' + idNum + ')"') +
        '><i class="bi bi-trash"></i> Eliminar</button>' +
        '</td>';
      tbody.appendChild(tr);
    });
  }

  function esc(s) {
    return String(s || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function listMascotasTutor() {
    if (global.state && Array.isArray(global.state.registros) && global.state.registros.length) {
      return global.state.registros;
    }
    if (global.RUPE_INIT && Array.isArray(global.RUPE_INIT.mascotas)) {
      return global.RUPE_INIT.mascotas;
    }
    if (Array.isArray(global.__RUPE_MASCOTAS__)) {
      return global.__RUPE_MASCOTAS__;
    }
    return [];
  }

  function esResguardo(m) {
    var e = String((m && (m.estatus || m.status)) || '').toLowerCase();
    return e.indexOf('resguardo') >= 0;
  }

  function idAlbergueMascota(m) {
    if (!m) return 0;
    var n = parseInt(m.id_albergue != null ? m.id_albergue : (m.albergueId != null ? m.albergueId : m.albergue_id), 10);
    return Number.isFinite(n) && n > 0 ? n : 0;
  }

  function actualizarKpisAlbergues() {
    var list = ensureState().albergues || [];
    var activos = list.filter(function (a) {
      return Number(a.activo) !== 0;
    });
    var capacidadTotal = activos.reduce(function (s, a) {
      return s + (Number(a.capacidad) || 0);
    }, 0);
    // Ocupación desde BD (campo ocupacion del JOIN) o desde mascotas en resguardo asignadas
    var ocupacionDesdeAlb = list.reduce(function (s, a) {
      return s + (Number(a.ocupacion) || 0);
    }, 0);
    var mascotas = listMascotasTutor();
    var casosResguardo = mascotas.filter(esResguardo).length;
    if (casosResguardo === 0 && ocupacionDesdeAlb > 0) {
      casosResguardo = ocupacionDesdeAlb;
    }
    // Preferir conteo real de mascotas en resguardo si hay lista
    if (mascotas.length) {
      casosResguardo = mascotas.filter(esResguardo).length;
    }
    var sinAsignar = mascotas.filter(function (m) {
      return esResguardo(m) && idAlbergueMascota(m) <= 0;
    }).length;
    // Si no hay mascotas en memoria, estimar sin asignar = max(0, casos - ocupación asignada)
    if (!mascotas.length && casosResguardo > 0) {
      sinAsignar = Math.max(0, casosResguardo - ocupacionDesdeAlb);
    }

    var set = function (id, val) {
      var el = document.getElementById(id);
      if (el) el.textContent = String(val);
    };
    set('kpiAlberguesTotal', activos.length);
    set('kpiAlberguesCapacidad', capacidadTotal);
    set('kpiAlberguesOcupacion', casosResguardo);
    set('kpiCasosSinAlbergue', sinAsignar);
  }

  function waitLeaflet(cb, tries) {
    tries = tries || 0;
    if (typeof L !== 'undefined') {
      cb();
      return;
    }
    if (tries > 40) {
      toast('No se cargó Leaflet (mapa). Recarga la página.', 'warning');
      return;
    }
    setTimeout(function () { waitLeaflet(cb, tries + 1); }, 50);
  }

  function setAlbGpsInputs(lat, lng, msg) {
    var la = document.getElementById('albLatitud');
    var lo = document.getElementById('albLongitud');
    var st = document.getElementById('albGpsStatus');
    if (la) {
      la.value = Number(lat).toFixed(6);
      la.classList.add('is-valid');
      la.classList.remove('is-invalid');
    }
    if (lo) {
      lo.value = Number(lng).toFixed(6);
      lo.classList.add('is-valid');
      lo.classList.remove('is-invalid');
    }
    if (st) {
      st.textContent = (msg || 'GPS listo') + ': ' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5);
    }
  }

  function initMapaAlbergue() {
    var el = document.getElementById('mapaAlbergue');
    if (!el) return null;
    if (typeof L === 'undefined') return null;
    var lat0 = 19.4326;
    var lng0 = -99.1332;
    var la = document.getElementById('albLatitud');
    var lo = document.getElementById('albLongitud');
    if (la && lo && la.value && lo.value) {
      var a = parseFloat(la.value);
      var b = parseFloat(lo.value);
      if (!isNaN(a) && !isNaN(b)) {
        lat0 = a;
        lng0 = b;
      }
    }
    if (albMapa) {
      try {
        albMapa.invalidateSize();
        albMapa.setView([lat0, lng0], albMapa.getZoom() || 13);
        if (albMarker) albMarker.setLatLng([lat0, lng0]);
      } catch (_) {}
      return albMapa;
    }
    albMapa = L.map('mapaAlbergue').setView([lat0, lng0], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(albMapa);
    albMarker = L.marker([lat0, lng0], { draggable: true }).addTo(albMapa);
    albMarker.on('dragend', function () {
      var p = albMarker.getLatLng();
      setAlbGpsInputs(p.lat, p.lng, 'Marcador movido');
    });
    albMapa.on('click', function (e) {
      setAlbGpsInputs(e.latlng.lat, e.latlng.lng, 'Punto en el mapa');
      albMarker.setLatLng(e.latlng);
    });
    setTimeout(function () {
      try { albMapa.invalidateSize(); } catch (_) {}
    }, 300);
    return albMapa;
  }

  function capturarGpsAlbergue() {
    var st = document.getElementById('albGpsStatus');
    var btn = document.getElementById('btnAlbCapturarGps');
    if (btn) btn.disabled = true;
    if (st) st.textContent = 'Obteniendo ubicación…';

    function fail(msg) {
      if (st) st.textContent = msg;
      toast(msg, 'warning');
      if (btn) btn.disabled = false;
    }

    function ok(lat, lng) {
      try {
        waitLeaflet(function () {
          initMapaAlbergue();
          setAlbGpsInputs(lat, lng, 'Ubicación capturada');
          if (albMapa && albMarker) {
            try {
              albMapa.setView([lat, lng], 16);
              albMarker.setLatLng([lat, lng]);
            } catch (_) {}
          }
          setTimeout(function () {
            try { if (albMapa) albMapa.invalidateSize(); } catch (_) {}
          }, 200);
        });
      } catch (_) {
        setAlbGpsInputs(lat, lng, 'Ubicación capturada');
      }
      toast('GPS capturado: ' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5), 'success');
      if (btn) btn.disabled = false;
      try {
        if (typeof validarCampoAlbLive === 'function') {
          validarCampoAlbLive('albLatitud');
          validarCampoAlbLive('albLongitud');
        }
      } catch (_) {}
    }

    if (!('geolocation' in navigator)) {
      fail('Geolocalización no disponible. Usa «Buscar dirección en el mapa» o escribe lat/lng.');
      return;
    }
    // Requiere contexto seguro (HTTPS o localhost)
    try {
      if (typeof window.isSecureContext !== 'undefined' && !window.isSecureContext &&
          location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        fail('El GPS del navegador requiere HTTPS. Usa buscar dirección o escribe coordenadas.');
        return;
      }
    } catch (_) {}

    navigator.geolocation.getCurrentPosition(
      function (pos) {
        ok(pos.coords.latitude, pos.coords.longitude);
      },
      function (err) {
        var mensajes = {
          1: 'Permiso de ubicación denegado. Actívalo en el candado de la barra de direcciones.',
          2: 'No se pudo determinar la ubicación.',
          3: 'Tiempo de espera agotado. Intenta de nuevo o busca la dirección.'
        };
        var msg = (err && mensajes[err.code]) || (err && err.message) || 'No se pudo obtener GPS.';
        fail(msg);
      },
      { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
    );
  }

  function buscarDireccionAlbergue() {
    var dir = ((document.getElementById('albDireccion') || {}).value || '').trim();
    var alcEl = document.getElementById('albAlcaldia');
    var alcNom = '';
    if (alcEl && alcEl.selectedIndex > 0) {
      alcNom = (alcEl.options[alcEl.selectedIndex].textContent || '').trim();
    }
    var q = [dir, alcNom, 'Ciudad de México', 'México'].filter(Boolean).join(', ');
    var st = document.getElementById('albGpsStatus');
    if (q.length < 5) {
      if (st) st.textContent = 'Escribe dirección y alcaldía.';
      toast('Escribe dirección y alcaldía para buscar.', 'warning');
      return;
    }
    if (st) st.textContent = 'Buscando dirección…';
    waitLeaflet(function () {
      initMapaAlbergue();
      fetch(
        'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=mx&q=' +
          encodeURIComponent(q),
        { headers: { Accept: 'application/json' } }
      )
        .then(function (r) { return r.json(); })
        .then(function (arr) {
          if (!Array.isArray(arr) || !arr.length) {
            if (st) st.textContent = 'No se encontró la dirección.';
            toast('No se encontró la dirección.', 'warning');
            return;
          }
          var lat = parseFloat(arr[0].lat);
          var lng = parseFloat(arr[0].lon);
          setAlbGpsInputs(lat, lng, 'Dirección localizada');
          if (albMapa && albMarker) {
            albMapa.setView([lat, lng], 16);
            albMarker.setLatLng([lat, lng]);
          }
          toast('Dirección localizada en el mapa.', 'success');
        })
        .catch(function () {
          if (st) st.textContent = 'Error de red al buscar dirección.';
          toast('Error de red al buscar dirección.', 'danger');
        });
    });
  }

  function mostrarFormAlbergue() {
    var card = document.getElementById('cardFormAlbergue');
    if (card) card.classList.remove('d-none');
    waitLeaflet(function () {
      setTimeout(function () { initMapaAlbergue(); }, 50);
    });
  }

  function ocultarFormAlbergue() {
    var card = document.getElementById('cardFormAlbergue');
    if (card) card.classList.add('d-none');
    var form = document.getElementById('formAlbergue');
    if (form) form.reset();
  }

  async function guardarAlbergue(e) {
    if (e && e.preventDefault) e.preventDefault();
    var nombre = ((document.getElementById('albNombre') || {}).value || '').trim();
    var capacidad = parseInt((document.getElementById('albCapacidad') || {}).value || '0', 10) || 1;
    var albAlcaldiaEl = document.getElementById('albAlcaldia');
    var id_alcaldia = albAlcaldiaEl ? parseInt(albAlcaldiaEl.value, 10) || 0 : 0;
    var direccion = ((document.getElementById('albDireccion') || {}).value || '').trim();
    var telefono = String((document.getElementById('albTelefono') || {}).value || '').replace(/\D/g, '');
    var correo = ((document.getElementById('albCorreo') || {}).value || '').trim();
    var latRaw = ((document.getElementById('albLatitud') || {}).value || '').trim().replace(',', '.');
    var lngRaw = ((document.getElementById('albLongitud') || {}).value || '').trim().replace(',', '.');
    var activoVal = parseInt((document.getElementById('albActivo') || {}).value || '1', 10) === 0 ? 0 : 1;
    var latitud = latRaw !== '' && !isNaN(parseFloat(latRaw)) ? parseFloat(latRaw) : null;
    var longitud = lngRaw !== '' && !isNaN(parseFloat(lngRaw)) ? parseFloat(lngRaw) : null;

    if (typeof validarFormularioAlbergueCompleto === 'function' && !validarFormularioAlbergueCompleto()) {
      toast('Revisa los campos marcados en rojo (nombre, tipo, capacidad, teléfono, etc.).', 'warning');
      return;
    }
    if (nombre.length < 2) {
      toast('Nombre del albergue obligatorio (mín. 2 caracteres).', 'warning');
      return;
    }
    if (telefono && telefono.length !== 10) {
      toast('Teléfono: exactamente 10 dígitos.', 'warning');
      return;
    }
    if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(correo)) {
      toast('Correo no válido.', 'warning');
      return;
    }

    var tipoAlb = ((document.getElementById('albTipo') || {}).value || '').trim();
    var payload = {
      nombre: nombre,
      tipo: tipoAlb || 'Albergue',
      direccion: direccion,
      id_alcaldia: id_alcaldia > 0 ? id_alcaldia : null,
      telefono: telefono || '',
      correo: correo || '',
      latitud: latitud,
      longitud: longitud,
      capacidad: capacidad,
      activo: activoVal,
      _csrf: csrf()
    };

    try {
      var res = await fetch('/api/tutor/albergues', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf()
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      });
      var data = await res.json().catch(function () { return {}; });
      if (!res.ok || data.success === false) {
        toast((data && data.message) || 'No se pudo guardar el albergue.', 'danger');
        return;
      }
      await cargarAlberguesDesdeApi();
      ocultarFormAlbergue();
      var saved = data.data || {};
      toast(
        'Albergue "' + (saved.nombre || nombre) + '" guardado (ID ' + (saved.id_albergue || saved.id || '') + ').',
        'success'
      );
    } catch (err) {
      console.warn('[RUPE] guardarAlbergue', err);
      toast('Error de red al guardar albergue.', 'danger');
    }
  }

  function abrirModalEliminarAlbergue(id) {
    var idNum = parseInt(id, 10);
    if (!Number.isFinite(idNum) || idNum <= 0) {
      toast('ID de albergue inválido.', 'warning');
      return;
    }
    function matchId(a) {
      return albIdNum(a) === idNum;
    }
    var alb = (ensureState().albergues || []).find(matchId);
    if (!alb && global.state && Array.isArray(global.state.albergues)) {
      alb = global.state.albergues.find(matchId) || null;
    }
    if (!alb && Array.isArray(global.__RUPE_ALBERGUES__)) {
      alb = global.__RUPE_ALBERGUES__.find(matchId) || null;
    }
    if (!alb) {
      var tr = document.querySelector('#tbodyDirectorioAlbergues tr[data-id="' + idNum + '"]');
      alb = {
        id_albergue: idNum,
        id: idNum,
        nombre: tr && tr.querySelector('strong') ? tr.querySelector('strong').textContent : ('Albergue #' + idNum),
        alcaldia: tr ? (tr.dataset.alcaldia || '') : '',
        telefono: '',
        ocupacion: 0
      };
    }
    if ((Number(alb.ocupacion) || 0) > 0) {
      toast('No se puede eliminar: tiene casos asignados.', 'warning');
      return;
    }
    var idEl = document.getElementById('modalEliminarAlbergueId');
    var nomEl = document.getElementById('modalEliminarAlbergueNombre');
    var detEl = document.getElementById('modalEliminarAlbergueDetalle');
    if (idEl) idEl.value = String(idNum);
    if (nomEl) nomEl.textContent = alb.nombre || ('Albergue #' + idNum);
    if (detEl) {
      detEl.textContent =
        'ID ' + idNum +
        (alb.alcaldia ? ' · ' + alb.alcaldia : '') +
        (alb.telefono ? ' · ' + alb.telefono : '');
    }
    var modalEl = document.getElementById('modalEliminarAlbergue');
    if (!modalEl) {
      toast('Modal de eliminar no encontrado. Recarga la página.', 'danger');
      return;
    }
    try {
      if (global.bootstrap && global.bootstrap.Modal) {
        global.bootstrap.Modal.getOrCreateInstance(modalEl).show();
      } else {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.removeAttribute('aria-hidden');
        if (!document.getElementById('rupeAlbDelBackdrop')) {
          var backdrop = document.createElement('div');
          backdrop.className = 'modal-backdrop fade show';
          backdrop.id = 'rupeAlbDelBackdrop';
          document.body.appendChild(backdrop);
        }
        document.body.classList.add('modal-open');
      }
    } catch (err) {
      console.warn(err);
      toast('No se pudo abrir el modal de eliminar.', 'danger');
    }
  }

  async function confirmarEliminarAlbergue() {
    var idEl = document.getElementById('modalEliminarAlbergueId');
    var idNum = parseInt(idEl && idEl.value, 10);
    if (!Number.isFinite(idNum) || idNum <= 0) return;
    try {
      var res = await fetch('/api/tutor/albergues/' + idNum + '/eliminar', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf()
        },
        credentials: 'same-origin',
        body: JSON.stringify({ _csrf: csrf() })
      });
      var data = await res.json().catch(function () { return {}; });
      if (!res.ok || data.success === false) {
        toast((data && data.message) || 'No se pudo eliminar.', 'danger');
        return;
      }
      var modalEl = document.getElementById('modalEliminarAlbergue');
      if (modalEl && global.bootstrap && bootstrap.Modal) {
        var m = bootstrap.Modal.getInstance(modalEl);
        if (m) m.hide();
      }
      await cargarAlberguesDesdeApi();
      toast('Albergue eliminado.', 'success');
    } catch (_) {
      toast('Error de red al eliminar.', 'danger');
    }
  }

  async function eliminarAlbergue(id) {
    abrirModalEliminarAlbergue(id);
  }

  async function toggleActivoAlbergue(id, actual) {
    var idNum = parseInt(id, 10);
    if (!Number.isFinite(idNum) || idNum <= 0) return;
    var nuevo = parseInt(actual, 10) === 1 ? 0 : 1;
    try {
      var res = await fetch('/api/tutor/albergues/' + idNum, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf()
        },
        credentials: 'same-origin',
        body: JSON.stringify({ activo: nuevo, _csrf: csrf() })
      });
      var data = await res.json().catch(function () { return {}; });
      if (!res.ok || data.success === false) {
        toast((data && data.message) || 'No se pudo cambiar el estado.', 'danger');
        return;
      }
      await cargarAlberguesDesdeApi();
      toast(nuevo === 1 ? 'Albergue activado.' : 'Albergue desactivado.', 'success');
    } catch (_) {
      toast('Error de red al cambiar estado.', 'danger');
    }
  }

  function setPalomita(okId, show) {
    var el = document.getElementById(okId);
    if (!el) return;
    el.style.display = show ? 'inline' : 'none';
    el.classList.toggle('d-none', !show);
  }

  function validarCampoAlbLive(campo) {
    var map = {
      albNombre: { ok: 'albNombreOk', test: function (v) { return v.trim().length >= 2 && v.trim().length <= 80 && !/\d/.test(v.replace(/\s/g, '') === v ? '' : ''); } },
    };
    // explicit rules
    var el = document.getElementById(campo);
    if (!el) return false;
    var v = String(el.value || '');
    var ok = false;
    var okId = '';
    if (campo === 'albNombre') {
      okId = 'albNombreOk';
      var n = v.trim().replace(/\s+/g, ' ');
      ok = n.length >= 2 && n.length <= 80;
      var cont = document.getElementById('albNombreContador');
      if (cont) cont.textContent = String(n.length);
    } else if (campo === 'albTipo') {
      okId = 'albTipoOk';
      ok = v.trim() !== '';
    } else if (campo === 'albCapacidad') {
      okId = 'albCapacidadOk';
      var c = parseInt(v, 10);
      ok = Number.isFinite(c) && c >= 1 && c <= 9999;
    } else if (campo === 'albAlcaldia') {
      okId = 'albAlcaldiaOk';
      ok = parseInt(v, 10) > 0 || (v.trim() !== '' && v !== '');
    } else if (campo === 'albDireccion') {
      okId = 'albDireccionOk';
      ok = v.trim().length >= 3;
      var cd = document.getElementById('albDireccionContador');
      if (cd) cd.textContent = String(v.trim().length);
    } else if (campo === 'albTelefono') {
      okId = 'albTelefonoOk';
      var digits = v.replace(/\D/g, '').slice(0, 10);
      if (el.value !== digits) el.value = digits;
      ok = digits.length === 10;
    } else if (campo === 'albCorreo') {
      okId = 'albCorreoOk';
      var mail = v.trim();
      ok = mail === '' || /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(mail);
      // correo opcional: vacío no es error visual fuerte, pero sin palomita
      if (mail === '') {
        el.classList.remove('is-valid', 'is-invalid');
        setPalomita(okId, false);
        return true;
      }
    } else if (campo === 'albActivo') {
      okId = 'albActivoOk';
      ok = v === '0' || v === '1';
    } else if (campo === 'albLatitud' || campo === 'albLongitud') {
      var num = parseFloat(String(v).replace(',', '.'));
      ok = v.trim() === '' || (!isNaN(num) && isFinite(num));
      el.classList.toggle('is-valid', ok && v.trim() !== '');
      el.classList.toggle('is-invalid', !ok);
      return ok;
    } else {
      return true;
    }
    el.classList.toggle('is-valid', ok);
    el.classList.toggle('is-invalid', !ok && v.trim() !== '');
    setPalomita(okId, ok);
    return ok;
  }

  function validarFormularioAlbergueCompleto() {
    var campos = ['albNombre', 'albTipo', 'albCapacidad', 'albAlcaldia', 'albDireccion', 'albTelefono', 'albCorreo', 'albActivo'];
    var allOk = true;
    campos.forEach(function (c) {
      if (!validarCampoAlbLive(c)) allOk = false;
    });
    return allOk;
  }

  function verMapaAlbergue(id) {
    var idNum = parseInt(id, 10);
    var alb = (ensureState().albergues || []).find(function (a) { return albIdNum(a) === idNum; });
    if (!alb || alb.latitud == null || alb.longitud == null) {
      toast('Este albergue no tiene GPS registrado.', 'warning');
      return;
    }
    var modalEl = document.getElementById('modalMapaAlbergue');
    if (!modalEl) {
      modalEl = document.createElement('div');
      modalEl.id = 'modalMapaAlbergue';
      modalEl.className = 'modal fade';
      modalEl.tabIndex = -1;
      modalEl.innerHTML =
        '<div class="modal-dialog modal-lg modal-dialog-centered">' +
        '<div class="modal-content">' +
        '<div class="modal-header py-2">' +
        '<h6 class="modal-title fw-bold" id="modalMapaAlbergueTitulo">Mapa del albergue</h6>' +
        '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
        '</div>' +
        '<div class="modal-body p-2">' +
        '<div id="mapaAlbergueVista" style="height:360px;border-radius:10px;"></div>' +
        '<p class="small text-muted mb-0 mt-2" id="modalMapaAlbergueCoords"></p>' +
        '</div></div></div>';
      document.body.appendChild(modalEl);
    }
    var titulo = document.getElementById('modalMapaAlbergueTitulo');
    var coords = document.getElementById('modalMapaAlbergueCoords');
    if (titulo) titulo.textContent = alb.nombre || 'Albergue #' + idNum;
    if (coords) {
      coords.textContent =
        'GPS: ' + Number(alb.latitud).toFixed(6) + ', ' + Number(alb.longitud).toFixed(6) +
        (alb.direccion ? ' · ' + alb.direccion : '');
    }
    var show = function () {
      waitLeaflet(function () {
        var mapDiv = document.getElementById('mapaAlbergueVista');
        if (!mapDiv) return;
        if (viewMapa) {
          try { viewMapa.remove(); } catch (_) {}
          viewMapa = null;
          viewMarker = null;
        }
        mapDiv.innerHTML = '';
        viewMapa = L.map('mapaAlbergueVista').setView([alb.latitud, alb.longitud], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; OpenStreetMap'
        }).addTo(viewMapa);
        viewMarker = L.marker([alb.latitud, alb.longitud]).addTo(viewMapa)
          .bindPopup(esc(alb.nombre || 'Albergue')).openPopup();
        setTimeout(function () {
          try { viewMapa.invalidateSize(); } catch (_) {}
        }, 250);
      });
    };
    if (global.bootstrap && bootstrap.Modal) {
      var m = bootstrap.Modal.getOrCreateInstance(modalEl);
      modalEl.addEventListener('shown.bs.modal', show, { once: true });
      m.show();
    } else {
      modalEl.style.display = 'block';
      show();
    }
  }

  // Delegación de clics en tabla
  document.addEventListener('click', function (e) {
    var mapBtn = e.target.closest && e.target.closest('[data-alb-map]');
    if (mapBtn) {
      e.preventDefault();
      verMapaAlbergue(mapBtn.getAttribute('data-alb-map'));
      return;
    }
    var togBtn = e.target.closest && e.target.closest('[data-alb-toggle]');
    if (togBtn) {
      e.preventDefault();
      toggleActivoAlbergue(togBtn.getAttribute('data-alb-toggle'), togBtn.getAttribute('data-alb-activo'));
      return;
    }
    var delBtn = e.target.closest && e.target.closest('[data-alb-del]');
    if (delBtn) {
      e.preventDefault();
      if (global.RUPE_ALBERGUES_FULL) return; // albergues.php maneja el modal propio
      abrirModalEliminarAlbergue(delBtn.getAttribute('data-alb-del'));
      return;
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    var btnConf = document.getElementById('btnConfirmarEliminarAlbergue');
    if (btnConf) {
      btnConf.addEventListener('click', function () {
        confirmarEliminarAlbergue();
      });
    }
    // En albergues.php (RUPE_ALBERGUES_FULL) los filtros se enlazan en la propia vista para evitar disparos duplicados.
    var btnFil = document.getElementById('btnFiltrarAlbergues');
    if (!global.RUPE_ALBERGUES_FULL && btnFil && !btnFil._rupeBound) {
      btnFil._rupeBound = true;
      btnFil.addEventListener('click', function (ev) {
        ev.preventDefault();
        aplicarFiltrosAlbergues();
      });
    }
    var inpBus = document.getElementById('filtroAlbBuscar');
    if (!global.RUPE_ALBERGUES_FULL && inpBus && !inpBus._rupeBound) {
      inpBus._rupeBound = true;
      inpBus.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter') {
          ev.preventDefault();
          aplicarFiltrosAlbergues();
        }
      });
    }
    ['albNombre','albTipo','albCapacidad','albAlcaldia','albDireccion','albTelefono','albCorreo','albActivo','albLatitud','albLongitud'].forEach(function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.addEventListener('input', function () { validarCampoAlbLive(id); });
      el.addEventListener('change', function () { validarCampoAlbLive(id); });
      el.addEventListener('blur', function () { validarCampoAlbLive(id); });
    });
  });


  function renderizarCasosResguardo() {
    var tbody = document.getElementById('tbodyAlbergues');
    if (!tbody) return;
    tbody.innerHTML = '';
    var mascotas = listMascotasTutor();
    var casos = mascotas.filter(function (r) {
      return esResguardo(r);
    });
    var albs = (ensureState().albergues || []).filter(function (a) {
      return Number(a.activo) !== 0;
    });

    if (!casos.length) {
      tbody.innerHTML =
        '<tr><td colspan="6" class="text-center py-4 text-muted">No hay casos en resguardo en este momento.</td></tr>';
      return;
    }

    casos.forEach(function (reg) {
      var idM = parseInt(reg.id_mascota != null ? reg.id_mascota : reg.id, 10) || 0;
      var idAlbActual = idAlbergueMascota(reg);
      var opciones = albs.map(function (alb) {
        var id = albIdNum(alb);
        var sel = id === idAlbActual ? ' selected' : '';
        return '<option value="' + id + '"' + sel + '>' + esc(alb.nombre || ('Albergue #' + id)) + '</option>';
      }).join('');

      var tr = document.createElement('tr');
      tr.dataset.id = String(idM);
      tr.dataset.folio = String(reg.folio || '');
      var okCls = idAlbActual > 0 ? '' : ' d-none';
      tr.innerHTML =
        '<td class="ps-4 fw-bold text-danger">#' + esc(reg.folio || '—') + '</td>' +
        '<td>' + esc(reg.nombre || reg.mascota || '—') + '</td>' +
        '<td>' + esc(reg.raza || reg.raza_nombre || '—') + '</td>' +
        '<td>' + esc(reg.alcaldia || reg.alcaldia_nombre || '—') + '</td>' +
        '<td>' +
        '<div class="d-flex align-items-center gap-2">' +
        '<select class="form-select form-select-sm" data-asig-mascota="' + idM + '" data-asig-folio="' + esc(reg.folio || '') + '" aria-label="Asignar albergue">' +
        '<option value="">— Sin asignar —</option>' +
        opciones +
        '</select>' +
        '<span class="text-success small fw-semibold asig-ok' + okCls + '" data-asig-ok="' + idM + '" title="Guardado">' +
        '<i class="bi bi-check-circle-fill"></i></span>' +
        '</div></td>' +
        '<td class="text-end pe-4 text-nowrap">' +
        '<button type="button" class="btn btn-sm btn-outline-primary" data-ver-ficha="' + esc(reg.folio || '') + '" data-ver-id="' + idM + '" title="Ver ficha completa">' +
        '<i class="bi bi-eye"></i> Ficha</button></td>';
      tbody.appendChild(tr);
    });
  }

  async function asignarAlbergue(folioOrId, albergueId) {
    var mascotas = listMascotasTutor();
    var reg = null;
    var idNum = parseInt(folioOrId, 10);
    if (Number.isFinite(idNum) && idNum > 0) {
      reg = mascotas.find(function (r) {
        return parseInt(r.id_mascota != null ? r.id_mascota : r.id, 10) === idNum;
      });
    }
    if (!reg) {
      reg = mascotas.find(function (r) { return String(r.folio) === String(folioOrId); });
    }
    if (!reg) {
      toast('No se encontró el caso a asignar.', 'warning');
      return;
    }
    var idMascota = parseInt(reg.id_mascota != null ? reg.id_mascota : reg.id, 10) || 0;
    if (idMascota <= 0) {
      toast('ID de mascota inválido.', 'warning');
      return;
    }
    var idAlb = parseInt(albergueId, 10);
    if (!Number.isFinite(idAlb) || idAlb < 0) idAlb = 0;
    var token = csrf();
    var payload = {
      id_albergue: idAlb > 0 ? idAlb : 0,
      estatus: 'En Resguardo',
      _csrf: token
    };
    var sel = document.querySelector('select[data-asig-mascota="' + idMascota + '"]');
    if (sel) {
      sel.disabled = true;
      sel.classList.remove('is-valid', 'is-invalid');
    }
    try {
      var res = await fetch('/api/tutor/mascotas/' + encodeURIComponent(idMascota), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': token
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      });
      var data = await res.json().catch(function () { return {}; });
      if (!res.ok || data.success === false) {
        if (sel) {
          sel.disabled = false;
          sel.classList.add('is-invalid');
        }
        toast((data && data.message) || 'No se pudo guardar el albergue asignado.', 'danger');
        return;
      }
      // Actualizar estado local
      reg.id_albergue = idAlb > 0 ? idAlb : 0;
      reg.albergueId = reg.id_albergue;
      var alb = (ensureState().albergues || []).find(function (a) { return albIdNum(a) === idAlb; });
      reg.albergue = alb ? alb.nombre : '';
      reg.albergue_nombre = reg.albergue;
      reg.estatus = 'En Resguardo';
      if (data.data && data.data.mascota) {
        var pm = data.data.mascota;
        if (pm.id_albergue != null) reg.id_albergue = parseInt(pm.id_albergue, 10) || 0;
        reg.albergueId = reg.id_albergue;
      }
      // Palomita verde
      try {
        var okEl = document.querySelector('[data-asig-ok="' + idMascota + '"]');
        if (okEl) {
          if (idAlb > 0) {
            okEl.classList.remove('d-none');
            okEl.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
          } else {
            okEl.classList.add('d-none');
          }
        }
        if (sel) {
          sel.disabled = false;
          sel.value = String(idAlb > 0 ? idAlb : '');
          sel.classList.add('is-valid');
          sel.classList.remove('is-invalid');
        }
      } catch (_) {}
      toast(
        idAlb > 0
          ? ('✓ Guardado: ' + (alb ? alb.nombre : ('Albergue #' + idAlb)))
          : '✓ Guardado: sin albergue asignado',
        'success'
      );
      // Refresco suave (KPIs + ocupación) sin perder la palomita
      try { await cargarMascotasParaKpis(); } catch (_) {}
      try {
        await cargarAlberguesDesdeApi();
      } catch (_) {
        renderizarCasosResguardo();
        actualizarKpisAlbergues();
      }
      setTimeout(function () {
        var ok2 = document.querySelector('[data-asig-ok="' + idMascota + '"]');
        if (ok2 && idAlb > 0) {
          ok2.classList.remove('d-none');
          ok2.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
        }
        var sel2 = document.querySelector('select[data-asig-mascota="' + idMascota + '"]');
        if (sel2) {
          sel2.disabled = false;
          sel2.value = String(idAlb > 0 ? idAlb : '');
          if (idAlb > 0) sel2.classList.add('is-valid');
        }
      }, 80);
    } catch (err) {
      console.warn('[RUPE] asignarAlbergue', err);
      if (sel) {
        sel.disabled = false;
        sel.classList.add('is-invalid');
      }
      toast('Error de red al guardar albergue asignado.', 'danger');
    }
  }


  // Exponer globales (sobrescribe versiones inline)
  global.cargarAlberguesDesdeApi = cargarAlberguesDesdeApi;
  global.rupeRenderDirectorioAlb = renderizarDirectorioAlbergues;
  if (typeof global.renderizarDirectorioAlbergues !== 'function') global.renderizarDirectorioAlbergues = renderizarDirectorioAlbergues;
  global.actualizarKpisAlbergues = actualizarKpisAlbergues;
  global.renderizarCasosResguardo = renderizarCasosResguardo;
  global.asignarAlbergue = asignarAlbergue;
  global.mostrarFormAlbergue = mostrarFormAlbergue;
  global.ocultarFormAlbergue = ocultarFormAlbergue;
  global.guardarAlbergue = guardarAlbergue;
  global.eliminarAlbergue = eliminarAlbergue;
  global.abrirModalEliminarAlbergue = abrirModalEliminarAlbergue;
  global.confirmarEliminarAlbergue = confirmarEliminarAlbergue;
  global.toggleActivoAlbergue = toggleActivoAlbergue;
  global.validarCampoAlbLive = validarCampoAlbLive;
  global.validarFormularioAlbergueCompleto = validarFormularioAlbergueCompleto;
  global.capturarGpsAlbergue = capturarGpsAlbergue;
  if (typeof global.aplicarFiltrosAlbergues !== 'function') global.aplicarFiltrosAlbergues = aplicarFiltrosAlbergues;
  if (typeof global.obtenerListaAlberguesFiltrada !== 'function') global.obtenerListaAlberguesFiltrada = obtenerListaAlberguesFiltrada;
  global.buscarDireccionAlbergue = buscarDireccionAlbergue;
  global.initMapaAlbergue = initMapaAlbergue;
  global.verMapaAlbergue = verMapaAlbergue;
  global.albIdNum = albIdNum;


  function verFichaCasoResguardo(folio, idMascota) {
    folio = String(folio || '').replace(/^#/, '').trim();
    var id = parseInt(idMascota, 10) || 0;
    if (!folio && id > 0) {
      var regs = listMascotasTutor();
      var r = regs.find(function (x) {
        return parseInt(x.id_mascota != null ? x.id_mascota : x.id, 10) === id;
      });
      if (r) folio = String(r.folio || '').replace(/^#/, '').trim();
    }
    if (!folio && id <= 0) {
      toast('No hay folio para ver la ficha.', 'warning');
      return;
    }
    var url = '/tutor/ficha-pdf';
    var qs = [];
    if (folio) qs.push('folio=' + encodeURIComponent(folio));
    if (id > 0) qs.push('id=' + encodeURIComponent(String(id)));
    if (qs.length) url += '?' + qs.join('&');
    try {
      window.location.assign(url);
    } catch (_) {
      window.location.href = url;
    }
  }
  global.verFichaCasoResguardo = verFichaCasoResguardo;
  global.seleccionarYVerPDF = function (folio) {
    verFichaCasoResguardo(folio, 0);
  };

  // Delegación: cambio de select albergue + clic ver ficha
  if (!global.__rupeAlbAsigBound) {
    global.__rupeAlbAsigBound = true;
    document.addEventListener('change', function (e) {
      var sel = e.target && e.target.closest ? e.target.closest('select[data-asig-mascota]') : null;
      if (!sel) return;
      var idM = sel.getAttribute('data-asig-mascota') || '';
      asignarAlbergue(idM, sel.value);
    });
    document.addEventListener('click', function (e) {
      var btn = e.target && e.target.closest ? e.target.closest('[data-ver-ficha]') : null;
      if (!btn) return;
      e.preventDefault();
      var folio = btn.getAttribute('data-ver-ficha') || '';
      var idM = btn.getAttribute('data-ver-id') || '';
      verFichaCasoResguardo(folio, idM);
    });
  }


  function boot() {
    ensureState();
    if (Array.isArray(global.__RUPE_ALBERGUES__) && global.__RUPE_ALBERGUES__.length) {
      global.state.albergues = global.__RUPE_ALBERGUES__.map(normalizarFila).filter(Boolean);
      renderizarDirectorioAlbergues();
      actualizarKpisAlbergues();
    }
    cargarAlberguesDesdeApi();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})(window);
