/**
 * RUPE CDMX — /tutor/gpsqr (rápido)
 * Solo cards con datos reales: foto + GPS
 */
'use strict';

(function () {
  var items = [];
  var bitacoras = [];
  var map = null;
  var markers = [];
  var filtroCard = 'todos';
  var tutorNombre = (typeof window.RUPE_TUTOR_NOMBRE === 'string' && window.RUPE_TUTOR_NOMBRE)
    ? window.RUPE_TUTOR_NOMBRE
    : 'Tutor';
  var loaded = false;

  function resolveFoto(url) {
    var raw = String(url == null ? '' : url).trim();
    if (!raw || /^null$/i.test(raw) || raw === '—' || raw === 'undefined') return '';
    if (raw.indexOf('data:image/') === 0 || /^https?:\/\//i.test(raw)) return raw;
    if (raw.charAt(0) === '/') return raw;
    if (raw.indexOf('assets/') === 0) return '/' + raw;
    if (raw.indexOf('uploads/') === 0) return '/assets/' + raw;
    return '/' + raw.replace(/^\/+/, '');
  }

  function esFotoReal(url) {
    var f = resolveFoto(url);
    if (!f) return false;
    // excluir placeholders
    if (/placeholder/i.test(f)) return false;
    if (/default[-_]?pet/i.test(f)) return false;
    return true;
  }

  function hasGps(a) {
    if (!a) return false;
    var lat = parseFloat(a.latitud);
    var lng = parseFloat(a.longitud);
    return isFinite(lat) && isFinite(lng) && !(Math.abs(lat) < 0.00001 && Math.abs(lng) < 0.00001);
  }

  /** Solo registros útiles: foto real + GPS */
  function esCardValida(a) {
    return !!(a && hasGps(a) && esFotoReal(a.foto_url));
  }

  function fmtFecha(s) {
    if (!s) return '—';
    try {
      var d = new Date(String(s).replace(' ', 'T'));
      if (!isNaN(d.getTime())) {
        return d.toLocaleString('es-MX', {
          day: '2-digit', month: '2-digit', year: 'numeric',
          hour: '2-digit', minute: '2-digit'
        });
      }
    } catch (e) {}
    return String(s);
  }

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function ensureMap() {
    var el = document.getElementById('mapaGPS');
    if (!el || typeof L === 'undefined') return null;
    if (!map) {
      map = L.map(el, { preferCanvas: true }).setView([19.4326, -99.1332], 11);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OSM'
      }).addTo(map);
    }
    setTimeout(function () {
      try { map.invalidateSize(); } catch (e) {}
    }, 120);
    return map;
  }

  function clearMarkers() {
    if (!map) return;
    markers.forEach(function (mk) {
      try { map.removeLayer(mk); } catch (e) {}
    });
    markers = [];
  }

  function pintarMapa(list) {
    var m = ensureMap();
    if (!m) return;
    clearMarkers();
    var bounds = [];
    (list || []).forEach(function (a) {
      if (!hasGps(a)) return;
      var lat = parseFloat(a.latitud);
      var lng = parseFloat(a.longitud);
      var mk = L.marker([lat, lng]).addTo(m);
      mk.bindPopup(
        '<strong>#' + esc(a.folio || '') + '</strong><br>' +
        (a.tipo === 'registro' ? 'Registro' : 'Avistamiento') + '<br>' +
        '<code>' + lat.toFixed(5) + ', ' + lng.toFixed(5) + '</code>'
      );
      markers.push(mk);
      bounds.push([lat, lng]);
    });
    if (bounds.length === 1) m.setView(bounds[0], 16);
    else if (bounds.length > 1) m.fitBounds(bounds, { padding: [24, 24] });
  }

  function centrarEn(lat, lng, meta) {
    lat = parseFloat(lat);
    lng = parseFloat(lng);
    if (!isFinite(lat) || !isFinite(lng)) return;
    var m = ensureMap();
    if (!m) return;
    var mapBox = document.getElementById('mapaGPS');
    if (mapBox) mapBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(function () {
      try {
        m.invalidateSize();
        m.setView([lat, lng], 17);
        L.popup().setLatLng([lat, lng]).setContent(
          '<strong>#' + esc((meta && meta.folio) || '') + '</strong><br><code>' +
          lat.toFixed(5) + ', ' + lng.toFixed(5) + '</code>'
        ).openOn(m);
      } catch (e) {}
    }, 150);
  }

  function filtrar() {
    var folio = (document.getElementById('gpsConsultaFolio') || {}).value || '';
    var q = ((document.getElementById('gpsConsultaBuscar') || {}).value || '').toLowerCase().trim();
    return items.filter(function (a) {
      if (!esCardValida(a)) return false;
      if (folio && String(a.folio || '') !== String(folio)) return false;
      if (filtroCard === 'registro' && a.tipo !== 'registro') return false;
      if (filtroCard === 'ciudadano' && a.tipo !== 'ciudadano') return false;
      if (filtroCard === 'gps' && !hasGps(a)) return false;
      if (q) {
        var blob = [a.folio, a.condicion, a.estatus, a.referencia, a.contacto, a.correo, a.nombre_mascota]
          .join(' ').toLowerCase();
        if (blob.indexOf(q) < 0) return false;
      }
      return true;
    });
  }

  function actualizarKpis() {
    var validos = items.filter(esCardValida);
    var reg = validos.filter(function (a) { return a.tipo === 'registro'; }).length;
    var ciu = validos.filter(function (a) { return a.tipo === 'ciudadano'; }).length;
    function set(id, v) {
      var el = document.getElementById(id);
      if (el) el.textContent = String(v);
    }
    set('numTotal', validos.length);
    set('numGps', validos.length); // todos los válidos tienen GPS
    set('numReg', reg);
    set('numCiu', ciu);
    var n = 0;
    if (filtroCard && filtroCard !== 'todos') n++;
    if ((document.getElementById('gpsConsultaFolio') || {}).value) n++;
    if (((document.getElementById('gpsConsultaBuscar') || {}).value || '').trim()) n++;
    var b = document.getElementById('badgeFiltrosActivos');
    if (b) b.textContent = n + ' filtro' + (n === 1 ? '' : 's') + ' activo' + (n === 1 ? '' : 's');
    document.querySelectorAll('.kpi-card').forEach(function (c) {
      c.classList.toggle('active-filter', c.getAttribute('data-filtro') === filtroCard);
    });
  }

  function pintarCards(list) {
    var box = document.getElementById('listaAvistCards');
    if (!box) return;
    if (!list.length) {
      box.innerHTML = '<div class="col-12 text-center text-muted py-4">' +
        'No hay cards con <strong>foto y GPS</strong> reales. ' +
        'Captura GPS en la ficha o recibe un avistamiento con foto.</div>';
      return;
    }
    box.innerHTML = list.map(function (a, idx) {
      var lat = Number(a.latitud).toFixed(5);
      var lng = Number(a.longitud).toFixed(5);
      var foto = resolveFoto(a.foto_url);
      var esReg = a.tipo === 'registro';
      var badgeTipo = esReg
        ? '<span class="badge bg-secondary">Registro</span>'
        : '<span class="badge bg-success">Ciudadano</span>';
      var quien = esReg
        ? ('<span class="text-muted">Tutor:</span> <strong>' + esc(a.contacto || tutorNombre) + '</strong>')
        : ('<span class="text-muted">Ciudadano:</span> <strong>' + esc(a.contacto || 'Anónimo') + '</strong>');
      var condicion = esReg
        ? esc(a.estatus || 'En Búsqueda')
        : esc(a.condicion || 'Observado');

      var acciones = '';
      if (esReg) {
        acciones =
          '<button type="button" class="btn btn-sm btn-outline-primary btn-map" ' +
          'data-lat="' + lat + '" data-lng="' + lng + '" data-folio="' + esc(a.folio || '') + '" title="Mapa">' +
          '<i class="bi bi-geo-alt-fill"></i></button>';
      } else {
        var disMsg = a.correo ? '' : ' disabled title="Sin correo"';
        acciones =
          '<button type="button" class="btn btn-sm btn-outline-success btn-msg" data-i="' + idx + '"' + disMsg +
          ' title="Mensaje"><i class="bi bi-envelope-heart"></i></button> ' +
          '<button type="button" class="btn btn-sm btn-outline-primary btn-map" ' +
          'data-lat="' + lat + '" data-lng="' + lng + '" data-folio="' + esc(a.folio || '') + '" title="Mapa">' +
          '<i class="bi bi-geo-alt-fill"></i></button>';
      }

      return (
        '<div class="col-md-6 col-lg-4">' +
        '<div class="card avist-card ' + (esReg ? 'tipo-registro' : '') + '">' +
        '<div class="card-body">' +
        '<div class="d-flex gap-2 mb-2">' +
        '<img src="' + esc(foto) + '" class="foto-card" alt="Foto" loading="lazy" ' +
        'onerror="this.closest(\'.col-md-6\').remove()">' +
        '<div class="flex-grow-1 min-w-0">' +
        '<div class="fw-bold text-truncate">#' + esc(a.folio || '—') +
        (a.nombre_mascota ? ' · ' + esc(a.nombre_mascota) : '') + '</div>' +
        badgeTipo + ' <span class="badge bg-warning text-dark">' + condicion + '</span>' +
        '<div class="small text-muted mt-1">' + fmtFecha(a.created_at) + '</div>' +
        '</div></div>' +
        '<p class="small mb-1">' + quien + '</p>' +
        (a.referencia
          ? '<p class="small mb-1 text-truncate"><strong>Ref:</strong> ' + esc(String(a.referencia).slice(0, 80)) + '</p>'
          : '') +
        '<p class="small mb-2"><span class="gps-badge"><i class="bi bi-crosshair"></i> ' + lat + ', ' + lng + '</span></p>' +
        '<div class="d-flex flex-wrap gap-1">' + acciones + '</div>' +
        '</div></div></div>'
      );
    }).join('');
  }

  function pintarBitacora(list) {
    var tbody = document.getElementById('tbodyBitacora');
    var badge = document.getElementById('badgeBitacora');
    if (badge) badge.textContent = String((list || []).length);
    if (!tbody) return;
    if (!list || !list.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Sin bitácora.</td></tr>';
      return;
    }
    tbody.innerHTML = list.map(function (b) {
      return '<tr>' +
        '<td class="ps-3 small fw-semibold">#' + esc(b.folio || '') + '</td>' +
        '<td class="small">' + esc(b.mascota || b.nombre || '') + '</td>' +
        '<td><span class="badge bg-secondary">' + esc(b.estatus || '') + '</span></td>' +
        '<td class="small text-nowrap">' + fmtFecha(b.ultima_actividad || b.created_at) + '</td>' +
        '<td class="small">' + esc(b.bitacora || b.ultimo_tipo || b.condicion || '') + '</td>' +
        '</tr>';
    }).join('');
  }

  function render() {
    var list = filtrar();
    window.__gpsFiltrados = list;
    actualizarKpis();
    pintarCards(list);
    pintarMapa(list);
  }

  function mapMascotaRegistro(m) {
    return {
      tipo: 'registro',
      id: m.id_mascota || m.id,
      folio: m.folio || '',
      nombre_mascota: m.nombre || '',
      condicion: m.estatus || 'En Búsqueda',
      estatus: m.estatus || 'En Búsqueda',
      referencia: [m.colonia, m.direccion_ref || m.direccion, m.calle].filter(Boolean).join(', '),
      contacto: tutorNombre,
      telefono: '',
      correo: '',
      latitud: m.latitud != null && m.latitud !== '' ? m.latitud : null,
      longitud: m.longitud != null && m.longitud !== '' ? m.longitud : null,
      foto_url: m.foto_url || m.foto || '',
      created_at: m.created_at || m.fecha_extravio || ''
    };
  }

  function mapAvistamiento(s) {
    return {
      tipo: 'ciudadano',
      id: s.id_seguimiento || s.id,
      folio: s.folio || '',
      nombre_mascota: s.mascota || s.nombre_mascota || '',
      condicion: s.condicion || 'Observado',
      estatus: s.condicion || 'Observado',
      referencia: s.referencia || '',
      contacto: s.contacto || s.reportado_por || 'Ciudadano',
      telefono: s.telefono || '',
      correo: s.correo || '',
      latitud: s.latitud != null && s.latitud !== '' ? s.latitud : null,
      longitud: s.longitud != null && s.longitud !== '' ? s.longitud : null,
      foto_url: s.foto_url || s.foto || '',
      created_at: s.created_at || s.fecha || ''
    };
  }

  function aplicarPayload(d) {
    d = d || {};
    var mascotas = d.mascotas || d.registros || [];
    var hist = d.historial_seguimiento || [];
    var regs = (Array.isArray(mascotas) ? mascotas : []).map(mapMascotaRegistro);
    var avis = (Array.isArray(hist) ? hist : []).map(mapAvistamiento);
    // Solo datos reales con foto + GPS
    items = regs.concat(avis).filter(esCardValida);
    bitacoras = d.bitacoras || d.bitacora || [];
    if (!Array.isArray(bitacoras)) bitacoras = [];
    if (!bitacoras.length && regs.length) {
      bitacoras = regs.map(function (m) {
        return {
          folio: m.folio,
          mascota: m.nombre_mascota,
          estatus: m.estatus,
          ultima_actividad: m.created_at,
          bitacora: m.estatus || 'Registro'
        };
      });
    }
    pintarBitacora(bitacoras);
    render();
  }

  function cargar() {
    // 1) Boot inmediato (sin esperar red)
    if (!loaded && Array.isArray(window.RUPE_GPS_BOOT_MASCOTAS)) {
      aplicarPayload({
        mascotas: window.RUPE_GPS_BOOT_MASCOTAS,
        historial_seguimiento: window.RUPE_GPS_BOOT_HIST || [],
        bitacoras: window.RUPE_GPS_BOOT_BIT || []
      });
      loaded = true;
    }

    // 2) Refresco API (una sola vez, sin bloqueos)
    fetch('/api/tutor/mascotas', {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
      cache: 'no-store'
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        var d = (data && data.data) ? data.data : (data || {});
        aplicarPayload(d);
        loaded = true;
      })
      .catch(function () {
        if (!loaded) {
          var box = document.getElementById('listaAvistCards');
          if (box) {
            box.innerHTML = '<div class="col-12 text-center text-muted py-4">No se pudieron cargar los datos.</div>';
          }
        }
      });
  }

  function rellenarMensaje(a) {
    if (!a || a.tipo === 'registro') return;
    var f = document.getElementById('msgFolio');
    var n = document.getElementById('msgNombre');
    var c = document.getElementById('msgCorreo');
    var t = document.getElementById('msgTexto');
    if (f) f.value = a.folio || '';
    if (n) n.value = a.contacto || '';
    if (c) c.value = a.correo || '';
    if (t) {
      t.value =
        'Hola ' + (a.contacto || 'vecino(a)') + ',\n\n' +
        'Muchas gracias por reportar el avistamiento' + (a.folio ? ' del folio #' + a.folio : '') +
        '. Tu ayuda es muy valiosa para reunir a las familias con sus mascotas en la Ciudad de México. ' +
        'Agradecemos tu solidaridad y el tiempo que dedicaste a reportar la ubicación.\n\n' +
        '¡Gracias por tu apoyo!\n— Tutor RUPE CDMX';
    }
    var sec = document.getElementById('seccionMensaje');
    if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  document.addEventListener('click', function (ev) {
    var mapBtn = ev.target.closest && ev.target.closest('.btn-map');
    var msgBtn = ev.target.closest && ev.target.closest('.btn-msg');
    if (mapBtn) {
      ev.preventDefault();
      centrarEn(mapBtn.getAttribute('data-lat'), mapBtn.getAttribute('data-lng'), {
        folio: mapBtn.getAttribute('data-folio') || ''
      });
      return;
    }
    if (msgBtn && !msgBtn.disabled) {
      ev.preventDefault();
      var list = window.__gpsFiltrados || [];
      var a2 = list[parseInt(msgBtn.getAttribute('data-i'), 10)];
      if (a2) rellenarMensaje(a2);
    }
  });

  var kpi = document.getElementById('kpiFiltros');
  if (kpi) {
    kpi.addEventListener('click', function (ev) {
      var card = ev.target.closest('.kpi-card');
      if (!card) return;
      filtroCard = card.getAttribute('data-filtro') || 'todos';
      render();
    });
  }


  function validarNombreCiudadano(v) {
    v = String(v || '').trim();
    if (v.length < 2 || v.length > 80) return false;
    // uno o dos nombres + apellidos opcionales, sin números
    return /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+(?:\s+[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)*$/.test(v);
  }
  function validarCorreoCiudadano(v) {
    v = String(v || '').trim();
    return v.length >= 6 && v.length <= 120 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  }
  function validarMensajeCiudadano(v) {
    v = String(v || '').trim();
    return v.length >= 100 && v.length <= 2000;
  }
  function setFieldState(input, okEl, ok, showInvalid) {
    if (!input) return;
    input.classList.remove('is-valid', 'is-invalid');
    if (ok) {
      input.classList.add('is-valid');
      if (okEl) okEl.classList.remove('d-none');
    } else {
      if (okEl) okEl.classList.add('d-none');
      if (showInvalid && String(input.value || '').trim() !== '') {
        input.classList.add('is-invalid');
      }
    }
  }
  function actualizarBtnEnviarMsg() {
    var nombre = (document.getElementById('msgNombre') || {}).value || '';
    var correo = (document.getElementById('msgCorreo') || {}).value || '';
    var mensaje = (document.getElementById('msgTexto') || {}).value || '';
    var ok = validarNombreCiudadano(nombre) && validarCorreoCiudadano(correo) && validarMensajeCiudadano(mensaje);
    var btn = document.getElementById('btnEnviarMsg');
    if (btn) btn.disabled = !ok;
    return ok;
  }
  function wireValidacionMensajeCiudadano() {
    var nombreEl = document.getElementById('msgNombre');
    var correoEl = document.getElementById('msgCorreo');
    var textoEl = document.getElementById('msgTexto');
    var folioEl = document.getElementById('msgFolio');
    var okN = document.getElementById('okMsgNombre');
    var okC = document.getElementById('okMsgCorreo');
    var okT = document.getElementById('okMsgTexto');
    var okF = document.getElementById('okMsgFolio');
    var countEl = document.getElementById('msgTextoCount');

    function paint() {
      var nOk = validarNombreCiudadano(nombreEl ? nombreEl.value : '');
      var cOk = validarCorreoCiudadano(correoEl ? correoEl.value : '');
      var tOk = validarMensajeCiudadano(textoEl ? textoEl.value : '');
      setFieldState(nombreEl, okN, nOk, true);
      setFieldState(correoEl, okC, cOk, true);
      setFieldState(textoEl, okT, tOk, true);
      if (folioEl) {
        var fv = String(folioEl.value || '').trim();
        if (fv.length >= 3) {
          folioEl.classList.add('is-valid');
          folioEl.classList.remove('is-invalid');
          if (okF) okF.classList.remove('d-none');
        } else {
          folioEl.classList.remove('is-valid', 'is-invalid');
          if (okF) okF.classList.add('d-none');
        }
      }
      if (countEl && textoEl) {
        var len = String(textoEl.value || '').trim().length;
        countEl.textContent = len + ' / 100';
        countEl.classList.toggle('text-success', len >= 100);
        countEl.classList.toggle('text-danger', len > 0 && len < 100);
      }
      actualizarBtnEnviarMsg();
    }

    [nombreEl, correoEl, textoEl, folioEl].forEach(function (el) {
      if (!el || el._msgValWired) return;
      el._msgValWired = true;
      el.addEventListener('input', paint);
      el.addEventListener('blur', paint);
    });
    paint();
  }
  wireValidacionMensajeCiudadano();

  var form = document.getElementById('formMensajeCiudadano');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var alertEl = document.getElementById('msgAlert');
      var nombre = (document.getElementById('msgNombre').value || '').trim();
      var correo = (document.getElementById('msgCorreo').value || '').trim();
      var mensaje = (document.getElementById('msgTexto').value || '').trim();
      var folio = (document.getElementById('msgFolio').value || '').trim();
      function show(msg, type) {
        if (!alertEl) return;
        alertEl.className = 'alert alert-' + type + ' py-2 small';
        alertEl.textContent = msg;
        alertEl.classList.remove('d-none');
      }
      if (!validarNombreCiudadano(nombre)) { show('Nombre inválido (solo letras, mín. 2).', 'warning'); return; }
      if (!validarCorreoCiudadano(correo)) { show('Correo no válido.', 'warning'); return; }
      if (!validarMensajeCiudadano(mensaje)) { show('El mensaje debe tener al menos 100 caracteres.', 'warning'); return; }
      var btn = document.getElementById('btnEnviarMsg');
      if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando…'; }
      fetch('/api/tutor/mensaje-ciudadano', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ nombre: nombre, correo: correo, mensaje: mensaje, folio: folio })
      })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
        .then(function (res) {
          if (!res.ok || !(res.j && res.j.success)) {
            show((res.j && res.j.message) || 'No se pudo enviar.', 'danger');
            return;
          }
          show(res.j.message || 'Mensaje enviado.', 'success');
          var modal = document.getElementById('modalMsgOk');
          if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
          form.reset();
          ['msgNombre','msgCorreo','msgTexto','msgFolio'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.classList.remove('is-valid', 'is-invalid');
          });
          ['okMsgNombre','okMsgCorreo','okMsgTexto','okMsgFolio'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('d-none');
          });
          var countEl = document.getElementById('msgTextoCount');
          if (countEl) { countEl.textContent = '0 / 100'; countEl.classList.remove('text-success', 'text-danger'); }
          if (btn) btn.disabled = true;
        })
        .catch(function () { show('Error de red.', 'danger'); })
        .finally(function () {
          if (btn) {
            btn.disabled = !actualizarBtnEnviarMsg();
            btn.innerHTML = '<i class="bi bi-send-fill me-1"></i>Enviar mensaje';
          }
        });
    });
  }

  var selFolio = document.getElementById('gpsConsultaFolio');
  if (selFolio) selFolio.addEventListener('change', render);
  var buscar = document.getElementById('gpsConsultaBuscar');
  if (buscar) {
    buscar.addEventListener('input', function () {
      clearTimeout(window.__gpsT);
      window.__gpsT = setTimeout(render, 150);
    });
  }
  var btnLimpiar = document.getElementById('btnGpsLimpiar');
  if (btnLimpiar) {
    btnLimpiar.addEventListener('click', function () {
      filtroCard = 'todos';
      if (selFolio) selFolio.value = '';
      if (buscar) buscar.value = '';
      render();
    });
  }
  var btnRef = document.getElementById('btnGpsRefrescar');
  if (btnRef) btnRef.addEventListener('click', function () { loaded = false; cargar(); });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', cargar);
  } else {
    cargar();
  }
})();
