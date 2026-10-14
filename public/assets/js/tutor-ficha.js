/**
 * RUPE CDMX — Ficha + QR + GPS mapa + selectores de folio
 */
'use strict';

(function (global) {
  var mapaFicha = null;
  var marcadorFicha = null;


  function limpioTxt(v) {
    if (v === null || v === undefined) return '';
    var s = String(v).trim();
    if (!s || s === '—' || /^null$/i.test(s) || /^undefined$/i.test(s)) return '';
    return s;
  }

  /** colonia + direccion_ref → { colonia, calle, referencia } sin vacíos */
  function parseDireccionFicha(reg) {
    var colonia = limpioTxt(reg.colonia);
    var dir = limpioTxt(reg.direccion_ref || reg.direccion || '');
    var calle = limpioTxt(reg.calle);
    var referencia = limpioTxt(reg.referencia || reg.referencia_ubicacion);
    // Si no vienen separados, partir direccion_ref por coma
    if ((!calle || !referencia) && dir) {
      var parts = dir.split(',').map(function (p) { return p.trim(); }).filter(Boolean);
      if (parts.length >= 2) {
        if (!calle) calle = parts[0];
        if (!referencia) referencia = parts.slice(1).join(', ');
      } else if (parts.length === 1) {
        if (!calle) calle = parts[0];
      }
    }
    // Evitar duplicar colonia dentro de calle
    if (colonia && calle && calle.toLowerCase() === colonia.toLowerCase()) {
      calle = '';
    }
    return { colonia: colonia, calle: calle, referencia: referencia, direccion: dir };
  }

  function extractMeta(senas) {
    var o = { tamano: '', collar: '', chip: '', limpio: senas || '' };
    if (!senas) return o;
    var s = String(senas);
    var m;
    m = s.match(/Tama[ñn]o\s*:\s*([^|]+)/i);
    if (m) o.tamano = m[1].trim();
    m = s.match(/Collar\s*:\s*([^|]+)/i);
    if (m) o.collar = m[1].trim();
    m = s.match(/Chip(?:\s*\/\s*tatuaje)?\s*:\s*([^|]+)/i);
    if (m) o.chip = m[1].trim();
    m = s.match(/Microchip\s*:\s*([^|]+)/i);
    if (m && !o.chip) o.chip = m[1].trim();
    o.limpio = s
      .replace(/(?:Raza|Tama[ñn]o|Collar|Chip(?:\s*\/\s*tatuaje)?|Microchip|Hora extrav[ií]o)\s*:\s*[^|]*/gi, '')
      .replace(/\s*\|\s*\|\s*/g, ' | ')
      .replace(/^\s*\|\s*|\s*\|\s*$/g, '')
      .trim();
    return o;
  }

  function mapSexo(v) {
    var s = String(v || '').toLowerCase();
    if (s.indexOf('hembra') >= 0) return 'Hembra';
    if (s.indexOf('macho') >= 0) return 'Macho';
    if (!s || s === 'desconocido') return '';
    return String(v);
  }

  function disp(val, emptyLabel) {
    if (val === null || val === undefined) return emptyLabel || 'No registrado';
    var s = String(val).trim();
    if (!s || s === '—' || s.toLowerCase() === 'null' || s.toLowerCase() === 'undefined') {
      return emptyLabel || 'No registrado';
    }
    return s;
  }

  function setT(id, val, emptyLabel) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = disp(val, emptyLabel);
  }

  function setSrc(id, src, especie) {
    var el = document.getElementById(id);
    if (!el) return;
    var esp = String(especie || 'perro').toLowerCase();
    var ph = esp.indexOf('gato') >= 0 ? '/assets/img/placeholder-gato.svg' : '/assets/img/placeholder-perro.svg';
    var url = src && String(src).trim() && String(src).toLowerCase() !== 'null' ? String(src).trim() : ph;
    if (url.indexOf('http') !== 0 && url.indexOf('data:') !== 0 && url.charAt(0) !== '/') url = '/' + url;
    el.style.display = 'block';
    el.style.maxHeight = '240px';
    el.style.width = '100%';
    el.style.objectFit = 'cover';
    el.onerror = function () {
      this.onerror = null;
      this.src = ph;
    };
    el.src = url;
  }

  function parseCoord(v) {
    if (v === null || v === undefined || v === '') return null;
    var n = Number(v);
    return isFinite(n) ? n : null;
  }

  function enrich(reg) {
    if (!reg) return reg;
    try {
      var pool = [].concat(
        (global.RUPE_INIT && global.RUPE_INIT.mascotas) || [],
        (global.state && global.state.registros) || []
      );
      var full = pool.find(function (r) {
        return (
          r &&
          (String(r.folio) === String(reg.folio) ||
            String(r.id || r.id_mascota) === String(reg.id || reg.id_mascota || ''))
        );
      });
      if (full) reg = Object.assign({}, full, reg);
    } catch (_) {}
    return reg;
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

  /** Rellena selects de folio (filtros de la ficha) */
  function actualizarSelectoresFolioTutor() {
    var list = listRegistros();
    var selFicha = document.getElementById('tutorSelectFolioFicha');
    var selRapido = document.getElementById('tutorSelectFolioRapido');
    var datalist = document.getElementById('tutorListaFolios');

    function fill(sel, withEmpty) {
      if (!sel) return;
      var cur = sel.value;
      sel.innerHTML = '';
      if (withEmpty || !list.length) {
        var o0 = document.createElement('option');
        o0.value = '';
        o0.textContent = list.length ? '— Selecciona folio —' : '— Sin registros —';
        sel.appendChild(o0);
      }
      list.forEach(function (r) {
        if (!r || !r.folio) return;
        var opt = document.createElement('option');
        opt.value = String(r.folio);
        opt.textContent = '#' + r.folio + ' · ' + (r.nombre || 'Mascota');
        sel.appendChild(opt);
      });
      var prefer =
        (global.state && global.state.folioSeleccionado) ||
        cur ||
        (list[0] && list[0].folio) ||
        '';
      if (prefer) {
        try {
          sel.value = String(prefer);
        } catch (_) {}
      }
    }

    fill(selFicha, true);
    fill(selRapido, true);

    if (datalist) {
      datalist.innerHTML = '';
      list.forEach(function (r) {
        if (!r || !r.folio) return;
        var o = document.createElement('option');
        o.value = String(r.folio);
        datalist.appendChild(o);
      });
    }
  }
  global.actualizarSelectoresFolioTutor = actualizarSelectoresFolioTutor;

  function setMapaStatic(lat, lng) {
    var img = document.getElementById('tutorFichaMapaStatic');
    if (!img) return;
    if (lat === null || lng === null || !isFinite(lat) || !isFinite(lng)) {
      img.removeAttribute('src');
      img.alt = 'Sin GPS';
      return;
    }
    // Static map OSM (para PDF / impresión)
    var z = 15;
    var url =
      'https://staticmap.openstreetmap.de/staticmap.php?center=' +
      lat +
      ',' +
      lng +
      '&zoom=' +
      z +
      '&size=640x200&markers=' +
      lat +
      ',' +
      lng +
      ',red-pushpin';
    img.src = url;
    img.alt = 'Mapa GPS ' + lat.toFixed(5) + ', ' + lng.toFixed(5);
    img.classList.remove('d-none');
    img.classList.add('d-print-block');
  }

  function notificarMapaFicha(mensaje, tipo) {
    var box = document.getElementById('mapaFichaTutor');
    if (!box) return;
    var html =
      '<div class="fw-semibold mb-1">' +
      (tipo === 'success' ? '✅ GPS capturado' : tipo === 'warning' ? '⚠️ Aviso' : tipo === 'info' ? '📡 GPS' : '📍 Ubicación') +
      '</div><div class="small">' +
      String(mensaje || '') +
      '</div>';
    try {
      if (typeof L !== 'undefined' && marcadorFicha) {
        marcadorFicha.bindPopup(html, { maxWidth: 260, className: 'rupe-gps-popup' }).openPopup();
      }
    } catch (_) {}
    // Badge flotante sobre el mapa (siempre visible)
    var badge = document.getElementById('mapaFichaNotif');
    if (!badge && box.parentElement) {
      badge = document.createElement('div');
      badge.id = 'mapaFichaNotif';
      badge.setAttribute('role', 'status');
      badge.style.cssText =
        'position:absolute;left:10px;top:10px;z-index:1000;max-width:90%;' +
        'background:#fff;border-radius:10px;padding:8px 12px;font-size:12px;' +
        'box-shadow:0 4px 14px rgba(0,0,0,.18);border-left:4px solid #198754;';
      var parent = box.parentElement;
      if (parent && getComputedStyle(parent).position === 'static') {
        parent.style.position = 'relative';
      }
      parent.appendChild(badge);
    }
    if (badge) {
      var color = tipo === 'success' ? '#198754' : tipo === 'danger' ? '#dc3545' : tipo === 'warning' ? '#fd7e14' : '#0d6efd';
      badge.style.borderLeftColor = color;
      badge.innerHTML = html;
      badge.style.display = 'block';
      clearTimeout(badge._rupeHide);
      badge._rupeHide = setTimeout(function () {
        try { badge.style.display = 'none'; } catch (_) {}
      }, 8000);
    }
  }
  global.notificarMapaFicha = notificarMapaFicha;

  function pintarMapaFicha(lat, lng, opts) {
    opts = opts || {};
    var box = document.getElementById('mapaFichaTutor');
    var wrap = document.getElementById('tutorFichaMapaWrap');
    if (!box) return;
    if (wrap) wrap.classList.remove('d-none');
    var sinGps = false;
    if (lat === null || lng === null || !isFinite(Number(lat)) || !isFinite(Number(lng))) {
      lat = 19.4326;
      lng = -99.1332;
      sinGps = true;
      box.setAttribute('data-sin-gps', '1');
    } else {
      lat = Number(lat);
      lng = Number(lng);
      box.removeAttribute('data-sin-gps');
    }
    try {
      if (typeof L === 'undefined') {
        box.innerHTML =
          '<p class="small text-muted mb-0 p-2">Mapa no disponible. Coordenadas: ' +
          lat.toFixed(5) + ', ' + lng.toFixed(5) + '</p>';
        return;
      }
      if (!mapaFicha) {
        box.innerHTML = '';
        mapaFicha = L.map(box, { zoomControl: true, attributionControl: true }).setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; OpenStreetMap',
        }).addTo(mapaFicha);
        marcadorFicha = L.marker([lat, lng]).addTo(mapaFicha);
      } else {
        mapaFicha.setView([lat, lng], 15);
        if (marcadorFicha) {
          marcadorFicha.setLatLng([lat, lng]);
        } else {
          marcadorFicha = L.marker([lat, lng]).addTo(mapaFicha);
        }
      }
      var msgPopup;
      if (opts.mensaje) {
        msgPopup = opts.mensaje;
      } else if (sinGps) {
        msgPopup = 'Sin GPS capturado aún. Usa el botón «Capturar GPS» de esta ficha.';
      } else {
        msgPopup = 'Última ubicación GPS<br><code>' + lat.toFixed(5) + ', ' + lng.toFixed(5) + '</code>';
      }
      try {
        marcadorFicha
          .bindPopup(
            '<div class="fw-semibold mb-1">' +
              (sinGps ? '📍 Mapa' : '✅ GPS en ficha') +
              '</div><div class="small">' +
              msgPopup +
              '</div>',
            { maxWidth: 260 }
          )
          .openPopup();
      } catch (_) {}
      if (opts.notificar) {
        notificarMapaFicha(msgPopup.replace(/<[^>]+>/g, ' '), opts.tipo || (sinGps ? 'warning' : 'success'));
      }
      setTimeout(function () {
        try {
          mapaFicha.invalidateSize();
          if (marcadorFicha) marcadorFicha.openPopup();
        } catch (_) {}
      }, 250);
    } catch (err) {
      console.warn('[RUPE] mapa ficha', err);
    }
  }
  global.pintarMapaFicha = pintarMapaFicha;


  function renderizarFichaTutor(reg) {
    var contVacio = document.getElementById('tutorContenedorFichaVacia');
    var cont = document.getElementById('tutorContenedorFicha');
    reg = enrich(reg);

    // Refresco GPS desde API si faltan coordenadas
    var idRef = reg.id || reg.id_mascota;
    if (idRef && (reg.latitud === undefined || reg.latitud === null || String(reg.latitud) === '')) {
      try {
        var csrf = (global.RUPE_INIT && global.RUPE_INIT.csrf) || global.__CSRF__ || '';
        fetch('/api/tutor/mascotas/' + encodeURIComponent(idRef), {
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrf,
          },
          credentials: 'same-origin',
        })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            var m = (data.data && data.data.mascota) || data.data || {};
            // Completar dirección desde BD si faltaba
            if (m.colonia) reg.colonia = m.colonia;
            if (m.direccion_ref) reg.direccion_ref = m.direccion_ref;
            if (m.hora_extravio) reg.hora_extravio = m.hora_extravio;
            if (m.latitud && m.longitud) {
              reg.latitud = m.latitud;
              reg.longitud = m.longitud;
              if (global.state && Array.isArray(global.state.registros)) {
                var ix = global.state.registros.findIndex(function (x) {
                  return (x.id || x.id_mascota) == idRef;
                });
                if (ix >= 0) {
                  global.state.registros[ix].latitud = m.latitud;
                  global.state.registros[ix].longitud = m.longitud;
                }
              }
              // Re-pintar solo GPS y mapa
              var lat2 = parseCoord(m.latitud);
              var lng2 = parseCoord(m.longitud);
              var dp = parseDireccionFicha(reg);
              var alc2 = limpioTxt(reg.alcaldia || reg.alcaldia_nombre || '');
              setT('tutorFichaLugar', [alc2, dp.colonia ? 'Col. ' + dp.colonia : ''].filter(Boolean).join(', '), 'No registrada');
              setT('tutorFichaCalle', dp.calle, 'Sin calle registrada');
              setT('tutorFichaReferencia', dp.referencia, 'Sin referencia');
              if (lat2 !== null && lng2 !== null) {
                setT('tutorFichaGps', lat2.toFixed(5) + ', ' + lng2.toFixed(5), 'Sin GPS capturado');
                var note = document.getElementById('tutorFichaGpsNota');
                if (note) note.textContent = 'QR incluye ubicación GPS: ' + lat2.toFixed(5) + ', ' + lng2.toFixed(5);
                pintarMapaFicha(lat2, lng2);
                setMapaStatic(lat2, lng2);
              }
            }
          })
          .catch(function () {});
      } catch (_) {}
    }

    if (!reg || !reg.folio) {
      if (contVacio) contVacio.classList.remove('d-none');
      if (cont) cont.classList.add('d-none');
      return;
    }
    if (contVacio) contVacio.classList.add('d-none');
    if (cont) cont.classList.remove('d-none');

    var senasRaw = String(reg.senas || reg.senas_particulares || '');
    var meta = extractMeta(senasRaw);
    var sexoTxt = mapSexo(reg.sexo);
    var tamanoTxt = reg.tamano || meta.tamano || '';
    var edadTxt = reg.edad_aprox || reg.edad || '';
    var colorTxt = reg.color || '';
    var collarTxt = reg.collar || meta.collar || '';
    var chipTxt = reg.microchip || reg.chip || meta.chip || '';
    var senasTxt = meta.limpio || '';
    var razaTxt = reg.raza || reg.raza_nombre || '';
    var alcTxt = limpioTxt(reg.alcaldia || reg.alcaldia_nombre || '');
    var dirParts = parseDireccionFicha(reg);
    var colTxt = dirParts.colonia;
    var calleTxt = dirParts.calle;
    var refTxt = dirParts.referencia;
    var fechaTxt = limpioTxt(reg.fecha || reg.fecha_extravio || '');
    // Hora si existe
    var horaTxt = limpioTxt(reg.hora_extravio || reg.hora || '');
    if (!horaTxt && reg.notas) {
      var mh = String(reg.notas).match(/Hora extrav[ií]o\s*:\s*(\d{1,2}:\d{2})/i);
      if (mh) horaTxt = mh[1];
    }
    var especieTxt = reg.especie || '';
    var estatusTxt = reg.estatus || '';
    var fotoSrc = reg.foto || reg.foto_url || '';
    var folio = String(reg.folio || '');
    var token = reg.qr_token || folio;
    var lat = parseCoord(reg.latitud);
    var lng = parseCoord(reg.longitud);
    var gpsTxt =
      lat !== null && lng !== null ? lat.toFixed(5) + ', ' + lng.toFixed(5) : '';

    var base = (global.location && global.location.origin) || '';
    var enlaceQR = base + '/mascota/' + encodeURIComponent(token) + '?avistar=1';
    if (lat !== null && lng !== null) {
      enlaceQR +=
        '?lat=' + encodeURIComponent(String(lat)) + '&lng=' + encodeURIComponent(String(lng));
    }
    var qrImg =
      'https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=8&data=' +
      encodeURIComponent(enlaceQR);

    setT('tutorFichaFolio', '#' + folio, '—');
    setT('tutorFichaFechaGen', 'Generada: ' + new Date().toLocaleString('es-MX'), '');
    setSrc('tutorFichaFoto', fotoSrc, especieTxt);
    setT('tutorFichaNombre', String(reg.nombre || '').toUpperCase(), 'SIN NOMBRE');
    setT(
      'tutorFichaRaza',
      [especieTxt ? especieTxt.charAt(0).toUpperCase() + especieTxt.slice(1) : '', razaTxt]
        .filter(Boolean)
        .join(' · '),
      'No registrada'
    );
    setT('tutorFichaSexoTamano', [sexoTxt, tamanoTxt].filter(Boolean).join(' · '), 'No registrado');
    setT(
      'tutorFichaEdad',
      edadTxt ? (String(edadTxt).match(/año|mes/i) ? edadTxt : edadTxt + ' año(s)') : '',
      'No registrada'
    );
    setT('tutorFichaColor', colorTxt, 'No registrado');
    setT(
      'tutorFichaLugar',
      [alcTxt, colTxt ? 'Col. ' + colTxt : ''].filter(Boolean).join(', '),
      'No registrada'
    );
    setT('tutorFichaCalle', calleTxt, 'Sin calle registrada');
    setT('tutorFichaReferencia', refTxt, 'Sin referencia');
    setT(
      'tutorFichaFecha',
      [fechaTxt, horaTxt ? ('Hora ' + horaTxt.slice(0, 5)) : ''].filter(Boolean).join(' · '),
      'No registrada'
    );
    setT('tutorFichaCollar', collarTxt, 'No registrado');
    setT('tutorFichaMicrochip', chipTxt, 'No registrado');
    if (!document.getElementById('tutorFichaMicrochip') && chipTxt) {
      setT(
        'tutorFichaCollar',
        [collarTxt, 'Microchip: ' + chipTxt].filter(Boolean).join(' · '),
        'No registrado'
      );
    }
    setT('tutorFichaSenas', senasTxt, 'Sin señas registradas');
    setT('tutorFichaEnlaceQR', enlaceQR, '');
    setT('tutorFichaEstatus', estatusTxt, 'En Búsqueda');
    setT('tutorFichaGps', gpsTxt, 'Sin GPS capturado');
    (function () {
      var qel = document.getElementById('tutorFichaQR');
      if (qel) {
        qel.src = qrImg;
        qel.alt = 'QR ' + folio;
        qel.style.display = 'block';
        qel.width = 140;
        qel.height = 140;
        qel.onerror = function () {
          this.onerror = null;
          this.src = 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=' + encodeURIComponent(enlaceQR);
        };
      }
    })();

    // Nota GPS bajo el QR
    var gpsNote = document.getElementById('tutorFichaGpsNota');
    if (gpsNote) {
      gpsNote.textContent =
        lat !== null && lng !== null
          ? 'QR incluye ubicación GPS: ' + gpsTxt
          : 'Sin coordenadas GPS en este folio (captúralas en GPS / QR).';
    }

    if (lat !== null && lng !== null) {
      pintarMapaFicha(lat, lng);
      setMapaStatic(lat, lng);
    } else {
      pintarMapaFicha(null, null);
      setMapaStatic(null, null);
      var st = document.getElementById('tutorFichaMapaStatic');
      if (st) {
        st.src =
          'data:image/svg+xml,' +
          encodeURIComponent(
            '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="160"><rect fill="#f0f0f0" width="640" height="160"/><text x="50%" y="50%" text-anchor="middle" fill="#888" font-size="16">Sin GPS capturado</text></svg>'
          );
        st.alt = 'Sin GPS capturado';
      }
    }

    if (global.state) global.state.folioSeleccionado = folio;
  }

  global.renderizarFichaTutor = renderizarFichaTutor;
  global.rupeRenderFicha = renderizarFichaTutor;

  function cambiarFolioFichaTutor() {
    var sel = document.getElementById('tutorSelectFolioFicha');
    var folio = sel ? sel.value : '';
    var list = listRegistros();
    var reg = list.find(function (r) {
      return String(r.folio) === String(folio);
    });
    if (global.state) global.state.folioSeleccionado = folio;
    renderizarFichaTutor(reg || null);
  }
  global.cambiarFolioFichaTutor = cambiarFolioFichaTutor;

  function imprimirFichaTutor() {
    var list = listRegistros();
    var folio =
      (document.getElementById('tutorSelectFolioFicha') || {}).value ||
      (global.state && global.state.folioSeleccionado) ||
      '';
    var reg = list.find(function (r) {
      return String(r.folio) === String(folio);
    }) || list[0];
    if (!reg || !reg.folio) {
      if (typeof global.rupeToast === 'function') {
        global.rupeToast('No hay ficha para imprimir. Selecciona un folio.', 'warning');
      } else {
        alert('No hay ficha para imprimir. Selecciona un folio.');
      }
      return;
    }
    renderizarFichaTutor(reg);
    // Asegurar módulo visible
    try {
      document.querySelectorAll('.modulo-seccion').forEach(function (s) {
        s.classList.remove('activo');
      });
      var sec = document.getElementById('modulo-ficha-pdf');
      if (sec) sec.classList.add('activo');
      var cont = document.getElementById('tutorContenedorFicha');
      if (cont) cont.classList.remove('d-none');
    } catch (_) {}
    setTimeout(function () {
      try {
        if (mapaFicha) mapaFicha.invalidateSize();
      } catch (_) {}
      // Diálogo de impresión: tamaño carta (Letter) vía @page CSS
      window.print();
    }, 400);
  }
  global.imprimirFichaTutor = imprimirFichaTutor;

  function bind() {
    actualizarSelectoresFolioTutor();
    var sel = document.getElementById('tutorSelectFolioFicha');
    if (sel && !sel._rupeFichaBound) {
      sel._rupeFichaBound = true;
      sel.addEventListener('change', cambiarFolioFichaTutor);
    }
    var list = listRegistros();
    var prefer =
      (global.state && global.state.folioSeleccionado) ||
      (sel && sel.value) ||
      (list[0] && list[0].folio);
    if (prefer) {
      var reg = list.find(function (r) {
        return String(r.folio) === String(prefer);
      });
      if (reg) renderizarFichaTutor(reg);
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind);
  else bind();
  
  // Al abrir módulo Ficha + QR, forzar carga del mapa y QR
  document.addEventListener('click', function (ev) {
    var btn = ev.target.closest('[data-modulo="ficha-pdf"]');
    if (!btn) return;
    setTimeout(function () {
      try {
        actualizarSelectoresFolioTutor();
        var list = listRegistros();
        var prefer =
          (global.state && global.state.folioSeleccionado) ||
          (document.getElementById('tutorSelectFolioFicha') || {}).value ||
          (list[0] && list[0].folio);
        var reg = list.find(function (r) {
          return String(r.folio) === String(prefer);
        }) || list[0];
        if (reg) renderizarFichaTutor(reg);
        if (mapaFicha) {
          setTimeout(function () {
            try { mapaFicha.invalidateSize(); } catch (_) {}
          }, 350);
        }
      } catch (err) {
        console.warn('[RUPE] ficha map on module', err);
      }
    }, 200);
  });

  global.addEventListener('load', function () {
    bind();
    setTimeout(function () {
      try {
        if (mapaFicha) mapaFicha.invalidateSize();
      } catch (_) {}
    }, 400);
  });
})(typeof window !== 'undefined' ? window : globalThis);
