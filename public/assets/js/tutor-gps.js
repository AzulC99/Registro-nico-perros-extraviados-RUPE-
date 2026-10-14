/**
 * RUPE CDMX — Módulo GPS / QR
 * Select de folios, buscar dirección (lupa), capturar GPS, enviar avistamiento
 */
'use strict';

(function (global) {
  /* RUPE_GPS_SAFE */
  try {
  function byId(id) { return document.getElementById(id); }
  function elVal(id, fallback) {
    var el = byId(id);
    return el ? String(el.value || '') : (fallback || '');
  }
  function setVal(id, v) {
    var el = byId(id);
    if (el) el.value = v;
    // aliases
    if (id === 'gpsLatitud') { var a = byId('gpsLat'); if (a) a.value = v; }
    if (id === 'gpsLongitud') { var b = byId('gpsLng'); if (b) b.value = v; }
  }
  function toast(msg, type) {
    if (typeof global.rupeToast === 'function') {
      global.rupeToast(msg, type || 'info');
      return;
    }
    if (typeof global.mostrarToastTutor === 'function') {
      global.mostrarToastTutor(msg, type || 'success');
      return;
    }
    try {
      console.log('[RUPE GPS]', msg);
    } catch (_) {}
  }

  function ensureState() {
    if (!global.state || typeof global.state !== 'object') {
      global.state = {
        registros: [],
        mapa: undefined,
        markerGps: undefined,
        capaAvistamientos: undefined,
        folioSeleccionado: '',
      };
    }
    if (!Array.isArray(global.state.registros)) {
      global.state.registros = [];
    }
    // Sincronizar desde RUPE_INIT si state está vacío
    if (
      global.state.registros.length === 0 &&
      global.RUPE_INIT &&
      Array.isArray(global.RUPE_INIT.mascotas)
    ) {
      global.state.registros = global.RUPE_INIT.mascotas.slice();
    }
    return global.state;
  }

  function listRegistros() {
    const st = ensureState();
    if (st.registros && st.registros.length) return st.registros;
    if (global.RUPE_INIT && Array.isArray(global.RUPE_INIT.mascotas)) {
      return global.RUPE_INIT.mascotas;
    }
    return [];
  }

  function findRegistroByFolio(folio) {
    if (!folio) return null;
    const f = String(folio).replace(/^#/, '').trim();
    const list = listRegistros();
    return (
      list.find(function (r) {
        return r && String(r.folio || '').replace(/^#/, '') === f;
      }) || null
    );
  }

  function ensureMapaGPS() {
    const st = ensureState();
    const el = document.getElementById('mapaGPS');
    if (!el) return null;
    if (typeof L === 'undefined') {
      toast('Mapa no disponible (Leaflet). Recarga la página.', 'warning');
      return null;
    }
    if (!st.mapa) {
      try {
        st.mapa = L.map('mapaGPS').setView([19.4326, -99.1332], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap',
          maxZoom: 19,
        }).addTo(st.mapa);
        st.markerGps = L.marker([19.4326, -99.1332], { draggable: true })
          .addTo(st.mapa)
          .bindPopup('Punto de avistamiento');
        st.markerGps.on('dragend', function (e) {
          const c = e.target.getLatLng();
          const latEl = document.getElementById('gpsLatitud') || document.getElementById('gpsLat');
    const lngEl = document.getElementById('gpsLongitud') || document.getElementById('gpsLng');
          if (latEl) if (latEl) latEl.value = c.lat.toFixed(6);
          if (lngEl) if (lngEl) lngEl.value = c.lng.toFixed(6);
        });
        st.capaAvistamientos = L.layerGroup().addTo(st.mapa);
      } catch (err) {
        console.error('[RUPE GPS] mapa', err);
        return null;
      }
    }
    setTimeout(function () {
      try {
        st.mapa.invalidateSize();
      } catch (_) {}
    }, 250);
    return st.mapa;
  }

  function setCoords(lat, lng) {
    const latN = Number(lat);
    const lngN = Number(lng);
    if (!isFinite(latN) || !isFinite(lngN)) return;
    const latEl = document.getElementById('gpsLatitud') || document.getElementById('gpsLat');
    const lngEl = document.getElementById('gpsLongitud') || document.getElementById('gpsLng');
    if (latEl) if (latEl) latEl.value = latN.toFixed(6);
    if (lngEl) if (lngEl) lngEl.value = lngN.toFixed(6);
    const st = ensureState();
    ensureMapaGPS();
    if (st.markerGps && st.mapa && typeof L !== 'undefined') {
      try {
        const ll = L.latLng(latN, lngN);
        st.markerGps.setLatLng(ll);
        st.mapa.setView(ll, 16);
        st.markerGps.openPopup();
      } catch (_) {}
    }
  }


  function resolveFotoUrl(reg) {
    if (!reg) return '/assets/img/placeholder-perro.svg';
    var raw = reg.foto || reg.foto_url || '';
    raw = String(raw || '').trim();
    if (!raw || raw.toLowerCase() === 'null' || raw.toLowerCase() === 'undefined') {
      var esp = String(reg.especie || 'perro').toLowerCase();
      if (esp.indexOf('gato') >= 0) return '/assets/img/placeholder-gato.svg';
      return '/assets/img/placeholder-perro.svg';
    }
    if (raw.indexOf('data:image/') === 0) return raw;
    if (/^https?:\/\//i.test(raw)) return raw;
    if (raw.charAt(0) === '/') return raw;
    if (raw.indexOf('assets/') === 0) return '/' + raw;
    if (raw.indexOf('uploads/') === 0) return '/assets/' + raw.replace(/^\/?/, '');
    if (/^mascota_[a-zA-Z0-9._-]+\.(jpe?g|png|gif|webp)$/i.test(raw)) {
      return '/assets/uploads/' + raw;
    }
    return '/' + raw.replace(/^\/+/, '');
  }
  global.resolveFotoUrl = resolveFotoUrl;

  function previsualizarFotoAvistamiento(input) {
    var file = input && input.files && input.files[0];
    var preview = document.getElementById('gpsFotoPreview');
    if (!file) {
      if (preview) {
        var folio = getFolioSeleccionadoGPS();
        var reg = findRegistroByFolio(folio);
        preview.src = resolveFotoUrl(reg);
      }
      return;
    }
    if (!file.type || file.type.indexOf('image/') !== 0) {
      toast('Selecciona una imagen (JPG, PNG, WEBP).', 'warning');
      input.value = '';
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      toast('La imagen no debe superar 5 MB.', 'warning');
      input.value = '';
      return;
    }
    var reader = new FileReader();
    reader.onload = function (e) {
      global.fotoAvistamientoCapturada = e.target.result;
      if (preview) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        preview.style.maxHeight = '220px';
        preview.style.objectFit = 'cover';
        preview.style.width = '100%';
      }
      toast('Foto de avistamiento lista.', 'success');
    };
    reader.onerror = function () {
      toast('No se pudo leer la imagen.', 'error');
    };
    reader.readAsDataURL(file);
  }
  global.previsualizarFotoAvistamiento = previsualizarFotoAvistamiento;


  /** Llena el SELECT de folios (filtros) con datos reales */
  function llenarSelectFoliosGPS() {
    const sel = document.getElementById('gpsFolio');
    if (!sel) return;
    let list = listRegistros();
    const prev = sel.value || (global.state && global.state.folioSeleccionado) || '';

    function pintar(lista) {
      if (!lista || !lista.length) {
        // Conservar opciones PHP si ya hay
        if (sel.options.length > 1) return;
        sel.innerHTML = '';
        const opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = '— Sin registros —';
        sel.appendChild(opt0);
        return;
      }
      sel.innerHTML = '';
      const opt0 = document.createElement('option');
      opt0.value = '';
      opt0.textContent = '— Selecciona folio —';
      sel.appendChild(opt0);
      lista.forEach(function (r) {
        if (!r || !r.folio) return;
        const opt = document.createElement('option');
        opt.value = String(r.folio);
        opt.textContent =
          '#' + r.folio + ' · ' + (r.nombre || 'Mascota') + (r.estatus ? ' · ' + r.estatus : '');
        opt.dataset.id = String(r.id || r.id_mascota || '');
        sel.appendChild(opt);
      });
      if (prev) {
        try {
          sel.value = String(prev);
        } catch (_) {}
      }
      if (sel.value) {
        try {
          sincronizarGpsPorFolio();
        } catch (_) {}
      }
    }

    pintar(list);

    // Siempre refrescar desde API (fuente de verdad)
    if (true) {
      fetch('/api/tutor/mascotas', {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          const arr =
            (data.data && data.data.mascotas) ||
            (data.data && Array.isArray(data.data) ? data.data : null) ||
            data.mascotas ||
            [];
          if (!Array.isArray(arr) || !arr.length) return;
          const st = ensureState();
          st.registros = arr.slice();
          if (global.RUPE_INIT) {
            global.RUPE_INIT.mascotas = arr.slice();
          }
          pintar(arr);
        })
        .catch(function (err) {
          console.warn('[RUPE GPS] fetch mascotas', err);
        });
    }
  }
  global.llenarSelectFoliosGPS = llenarSelectFoliosGPS;
  global.renderizarSelectoresFolio = llenarSelectFoliosGPS;

  /** Al cambiar folio: muestra datos y mapa */
  function sincronizarGpsPorFolio() {
    const sel = document.getElementById('gpsFolio');
    const folio = sel ? sel.value : '';
    const st = ensureState();
    st.folioSeleccionado = folio || '';

    const ctx = document.getElementById('gpsContextoFolio');
    const alerta = document.getElementById('gpsAlertaLocalizado');
    if (!folio) {
      if (ctx) {
        ctx.classList.add('d-none');
        ctx.innerHTML = '';
      }
      if (alerta) alerta.classList.add('d-none');
      return;
    }
    const reg = findRegistroByFolio(folio);
    if (!reg) {
      if (ctx) {
        ctx.classList.remove('d-none');
        ctx.innerHTML =
          '<span class="text-warning">Folio no encontrado en memoria. Recarga la página.</span>';
      }
      return;
    }
    if (ctx) {
      ctx.classList.remove('d-none');
      const foto = resolveFotoUrl(reg);
      // Mostrar también en vista previa del avistamiento (foto oficial del registro)
      const prev = document.getElementById('gpsFotoPreview');
      if (prev && !(document.getElementById('gpsFotoArchivo') && document.getElementById('gpsFotoArchivo').files && document.getElementById('gpsFotoArchivo').files[0])) {
        prev.src = foto;
        prev.style.display = 'block';
        prev.style.maxHeight = '220px';
        prev.style.objectFit = 'cover';
        prev.style.width = '100%';
        prev.onerror = function () {
          this.onerror = null;
          this.src = '/assets/img/placeholder-mascota.svg';
        };
      }
      ctx.innerHTML =
        '<img src="' +
        foto +
        '" alt="Foto" width="56" height="56" class="rounded border flex-shrink-0" style="object-fit:cover;background:#f0f0f0" onerror="this.onerror=null;this.src=\'/assets/img/placeholder-mascota.svg\'">' +
        '<div class="flex-grow-1">' +
        '<div class="fw-bold">#' +
        (reg.folio || '') +
        ' · ' +
        (reg.nombre || '') +
        '</div>' +
        '<div class="small text-muted">' +
        [reg.raza || reg.raza_nombre || '', reg.alcaldia || reg.alcaldia_nombre || '', reg.estatus || '']
          .filter(Boolean)
          .join(' · ') +
        '</div>' +
        '</div>';
    }
    if (alerta) {
      if (String(reg.estatus || '').toLowerCase().indexOf('localizado') >= 0) {
        alerta.classList.remove('d-none');
      } else {
        alerta.classList.add('d-none');
      }
    }
    // Si el registro ya tiene GPS, centrar mapa
    const lat = parseFloat(reg.latitud);
    const lng = parseFloat(reg.longitud);
    if (isFinite(lat) && isFinite(lng)) {
      setCoords(lat, lng);
    } else {
      ensureMapaGPS();
    }
  }
  global.sincronizarGpsPorFolio = sincronizarGpsPorFolio;

  async function csrfToken() {
    let csrf =
      global.__CSRF__ || (global.RUPE_INIT && global.RUPE_INIT.csrf) || '';
    if (!csrf) {
      try {
        const r = await fetch('/api/csrf', { credentials: 'same-origin' });
        const d = await r.json();
        csrf = (d.data && d.data.csrf) || d.csrf || '';
        global.__CSRF__ = csrf;
      } catch (_) {}
    }
    return csrf;
  }

  function resolveIdMascotaParaFicha(folio) {
    var f = String(folio || '').replace(/^#/, '').trim();
    var id = 0;
    // 1) option data-id en select ficha
    var sel = document.getElementById('tutorSelectFolioFicha');
    if (sel && sel.selectedOptions && sel.selectedOptions[0]) {
      var opt = sel.selectedOptions[0];
      id = parseInt(opt.getAttribute('data-id') || opt.getAttribute('data-id-mascota') || opt.dataset.id || 0, 10);
      if (!f && opt.value) f = String(opt.value).replace(/^#/, '').trim();
    }
    // 2) registro en state / RUPE_INIT
    if (id <= 0 && f) {
      var reg = findRegistroByFolio(f);
      if (reg) id = parseInt(reg.id || reg.id_mascota || 0, 10);
    }
    // 3) recorrer listRegistros
    if (id <= 0 && f) {
      listRegistros().forEach(function (r) {
        if (id > 0) return;
        if (r && String(r.folio || '').replace(/^#/, '') === f) {
          id = parseInt(r.id || r.id_mascota || 0, 10);
        }
      });
    }
    return { id: id, folio: f };
  }

  async function guardarGpsEnFicha(lat, lng) {
    const folio =
      getFolioSeleccionadoGPS() ||
      (document.getElementById('tutorSelectFolioFicha') || {}).value ||
      (ensureState().folioSeleccionado || '') ||
      '';
    const resolved = resolveIdMascotaParaFicha(folio);
    const idMascota = resolved.id;
    if (!resolved.folio && !idMascota) {
      toast('Selecciona un folio en la ficha antes de capturar GPS.', 'warning');
      return false;
    }
    if (!idMascota) {
      toast('No se encontró el ID de la mascota para el folio ' + (resolved.folio || folio) + '. Recarga la página.', 'warning');
      return false;
    }
    const reg = findRegistroByFolio(resolved.folio || folio);
    const csrf = await csrfToken();
    const latN = Number(lat);
    const lngN = Number(lng);
    if (!isFinite(latN) || !isFinite(lngN)) return false;
    try {
      const res = await fetch(
        '/api/tutor/mascotas/' + encodeURIComponent(idMascota) + '/gps',
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrf,
          },
          credentials: 'same-origin',
          body: JSON.stringify({ _csrf: csrf, latitud: latN, longitud: lngN }),
        }
      );
      const data = await res.json().catch(function () {
        return {};
      });
      if (!res.ok || data.success === false) {
        toast((data && data.message) || 'No se pudo guardar GPS en ficha.', 'warning');
        return false;
      }
      const latS = (data.data && data.data.latitud) != null ? data.data.latitud : latN;
      const lngS = (data.data && data.data.longitud) != null ? data.data.longitud : lngN;
      reg.latitud = latS;
      reg.longitud = lngS;
      listRegistros().forEach(function (r) {
        if ((r.id || r.id_mascota) == idMascota || String(r.folio) === String(folio)) {
          r.latitud = latS;
          r.longitud = lngS;
        }
      });
      toast(
        'GPS en ficha: ' + Number(latS).toFixed(5) + ', ' + Number(lngS).toFixed(5),
        'success'
      );
      try {
        if (typeof global.renderizarFichaTutor === 'function') {
          global.renderizarFichaTutor(reg);
        }
      } catch (_) {}
      return true;
    } catch (err) {
      console.error('[RUPE GPS save]', err);
      return false;
    }
  }
  global.guardarGpsEnFicha = guardarGpsEnFicha;

  function getFolioSeleccionadoGPS() {
    const sel =
      document.getElementById('gpsFolio') ||
      document.getElementById('gpsSelectFolio') ||
      document.getElementById('selectFolioGps');
    if (sel && sel.value) return sel.value;
    const st = ensureState();
    return st.folioSeleccionado || '';
  }

  function capturarUbicacionGPS() {
    if (!('geolocation' in navigator)) {
      toast(
        'Tu navegador no soporta geolocalización. Usa Buscar dirección.',
        'warning'
      );
      return;
    }
    ensureMapaGPS();
    toast('Obteniendo ubicación GPS…', 'info');
    navigator.geolocation.getCurrentPosition(
      function (position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        setCoords(lat, lng);
        toast('Ubicación GPS obtenida.', 'success');
        // Guardar en ficha si hay folio (no bloquea si falla)
        try {
          guardarGpsEnFicha(lat, lng);
        } catch (_) {}
      },
      function (error) {
        const mensajes = {
          1: 'Permiso de ubicación denegado. Actívalo o usa Buscar dirección.',
          2: 'No se pudo determinar tu ubicación. Usa Buscar dirección.',
          3: 'Tiempo de espera agotado. Intenta de nuevo.',
        };
        toast(mensajes[error.code] || 'No se pudo obtener GPS.', 'danger');
      },
      { enableHighAccuracy: false, timeout: 6000, maximumAge: 60000 }
    );
  }
  global.capturarUbicacionGPS = capturarUbicacionGPS;

  function capturarGpsParaFichaActual() {
    const sel = document.getElementById('tutorSelectFolioFicha');
    if (sel && sel.value) {
      ensureState().folioSeleccionado = sel.value;
      const gpsSel = document.getElementById('gpsFolio') || document.getElementById('gpsSelectFolio');
      if (gpsSel) {
        try { gpsSel.value = sel.value; } catch (_) {}
      }
    } else if (!sel || !sel.value) {
      toast('Selecciona un folio en la ficha antes de capturar GPS.', 'warning');
      var st = document.getElementById('gpsFichaStatus');
      if (st) st.textContent = 'Selecciona un folio primero';
      return;
    }
    const status = document.getElementById('gpsFichaStatus');
    const btn = document.getElementById('btnCapturarGpsFicha');
    if (status) status.textContent = 'Obteniendo GPS…';
    if (btn) { btn.disabled = true; }
    toast('Obteniendo ubicación GPS para la ficha…', 'info');
    try {
      if (typeof global.notificarMapaFicha === 'function') {
        global.notificarMapaFicha('Obteniendo ubicación del dispositivo…', 'info');
      }
    } catch (_) {}

    if (!('geolocation' in navigator)) {
      const msg = 'Tu navegador no soporta geolocalización. Usa HTTPS o GPS / QR → Buscar dirección.';
      toast(msg, 'warning');
      if (status) status.textContent = msg;
      if (btn) btn.disabled = false;
      try { if (typeof global.notificarMapaFicha === 'function') global.notificarMapaFicha(msg, 'warning'); } catch (_) {}
      return;
    }

    navigator.geolocation.getCurrentPosition(
      function (position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        try { setCoords(lat, lng); } catch (_) {}
        const txt = lat.toFixed(5) + ', ' + lng.toFixed(5);
        if (status) status.textContent = 'Guardando ' + txt + '…';

        Promise.resolve(guardarGpsEnFicha(lat, lng))
          .then(function (ok) {
            if (ok) {
              toast('GPS guardado en la ficha: ' + txt, 'success');
              if (status) status.textContent = 'Guardado: ' + txt;
              try {
                if (typeof global.pintarMapaFicha === 'function') {
                  global.pintarMapaFicha(lat, lng, {
                    notificar: true,
                    tipo: 'success',
                    mensaje: 'GPS generado y guardado<br><code>' + txt + '</code>',
                  });
                } else if (typeof global.notificarMapaFicha === 'function') {
                  global.notificarMapaFicha('GPS guardado: ' + txt, 'success');
                }
              } catch (_) {}
              try {
                if (typeof global.renderizarFichaTutor === 'function') {
                  const list = listRegistros();
                  const folio = (sel && sel.value) || ensureState().folioSeleccionado;
                  const reg = list.find(function (r) {
                    return String(r.folio || '').replace(/^#/, '') === String(folio || '').replace(/^#/, '');
                  }) || list[0];
                  if (reg) {
                    reg.latitud = lat;
                    reg.longitud = lng;
                    global.renderizarFichaTutor(reg);
                  }
                }
              } catch (_) {}
            } else {
              toast('Se obtuvo GPS (' + txt + ') pero no se pudo guardar. Revisa el folio y recarga (F5).', 'warning');
              if (status) status.textContent = 'Capturado sin guardar: ' + txt;
              try {
                if (typeof global.pintarMapaFicha === 'function') {
                  global.pintarMapaFicha(lat, lng, {
                    notificar: true,
                    tipo: 'warning',
                    mensaje: 'GPS obtenido (no guardado aún)<br><code>' + txt + '</code>',
                  });
                }
              } catch (_) {}
            }
          })
          .catch(function (err) {
            console.warn('[RUPE] guardarGpsEnFicha', err);
            toast('Error al guardar GPS. Intenta de nuevo.', 'danger');
            if (status) status.textContent = 'Error al guardar';
          })
          .finally(function () {
            if (btn) btn.disabled = false;
          });
      },
      function (error) {
        const mensajes = {
          1: 'Permiso de ubicación denegado. Actívalo en el navegador (candado de la barra de direcciones).',
          2: 'No se pudo determinar la ubicación.',
          3: 'Tiempo de espera agotado. Intenta de nuevo.',
        };
        const msg = mensajes[error.code] || 'No se pudo obtener GPS.';
        toast(msg, 'danger');
        if (status) status.textContent = msg;
        if (btn) btn.disabled = false;
        try { if (typeof global.notificarMapaFicha === 'function') global.notificarMapaFicha(msg, 'danger'); } catch (_) {}
      },
      { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 }
    );
  }
  global.capturarGpsParaFichaActual = capturarGpsParaFichaActual;

  /**
   * Buscar dirección (lupa) — Nominatim + fallback Photon
   */
  function buscarDireccionGPS() {
    const input = document.getElementById('gpsBuscarDireccion');
    const msg = document.getElementById('gpsBuscarDireccionMsg');
    const query = (input && input.value ? input.value : '').trim();
    if (!query) {
      toast('Escribe una dirección o colonia (ej. Roma Norte, CDMX).', 'warning');
      if (input) input.focus();
      return;
    }
    if (msg) msg.textContent = 'Buscando…';
    ensureMapaGPS();

    const q = encodeURIComponent(query + (query.toLowerCase().indexOf('cdmx') >= 0 || query.toLowerCase().indexOf('mexico') >= 0 ? '' : ', Ciudad de México'));

    function aplicarResultado(lat, lng, label) {
      setCoords(lat, lng);
      if (msg) msg.textContent = 'Ubicación: ' + (label || query);
      toast('Dirección localizada en el mapa.', 'success');
      try {
        guardarGpsEnFicha(lat, lng);
      } catch (_) {}
    }

    // 1) Nominatim
    fetch(
      'https://nominatim.openstreetmap.org/search?format=json&limit=3&countrycodes=mx&q=' + q,
      {
        headers: {
          Accept: 'application/json',
        },
      }
    )
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function (resultados) {
        if (resultados && resultados.length) {
          const lugar = resultados[0];
          aplicarResultado(parseFloat(lugar.lat), parseFloat(lugar.lon), lugar.display_name);
          return;
        }
        // 2) Fallback Photon
        return fetch(
          'https://photon.komoot.io/api/?limit=3&lang=es&q=' + q
        )
          .then(function (r2) {
            return r2.json();
          })
          .then(function (data) {
            const feats = (data && data.features) || [];
            if (!feats.length) {
              if (msg) msg.textContent = 'No se encontró esa dirección. Prueba con colonia + alcaldía.';
              toast('Dirección no encontrada.', 'warning');
              return;
            }
            const c = feats[0].geometry && feats[0].geometry.coordinates;
            const p = feats[0].properties || {};
            if (!c || c.length < 2) {
              toast('Dirección no encontrada.', 'warning');
              return;
            }
            // GeoJSON: [lng, lat]
            aplicarResultado(
              parseFloat(c[1]),
              parseFloat(c[0]),
              p.name || p.street || p.city || query
            );
          });
      })
      .catch(function (err) {
        console.warn('[RUPE GPS geocode]', err);
        // Fallback Photon si Nominatim falla (CORS/red)
        fetch('https://photon.komoot.io/api/?limit=3&lang=es&q=' + q)
          .then(function (r) {
            return r.json();
          })
          .then(function (data) {
            const feats = (data && data.features) || [];
            if (!feats.length) {
              if (msg) msg.textContent = 'Error al buscar. Intenta de nuevo.';
              toast('No se pudo buscar la dirección.', 'danger');
              return;
            }
            const c = feats[0].geometry.coordinates;
            const p = feats[0].properties || {};
            aplicarResultado(parseFloat(c[1]), parseFloat(c[0]), p.name || query);
          })
          .catch(function () {
            if (msg) msg.textContent = 'Error de red al buscar.';
            toast('Error de red al buscar la dirección.', 'danger');
          });
      });
  }
  global.buscarDireccionGPS = buscarDireccionGPS;

  
  function marcarGpsCampo(el, ok) {
    if (!el) return;
    if (ok === null) {
      el.classList.remove('is-valid', 'is-invalid');
      var okHint0 = document.getElementById(el.id + 'Ok');
      if (okHint0) {
        okHint0.style.display = 'none';
        okHint0.classList.add('d-none');
      }
      return;
    }
    el.classList.toggle('is-invalid', !ok);
    el.classList.toggle('is-valid', !!ok);
    var okHint = document.getElementById(el.id + 'Ok');
    if (okHint) {
      if (ok) {
        okHint.style.display = 'inline-block';
        okHint.classList.remove('d-none');
      } else {
        okHint.style.display = 'none';
        okHint.classList.add('d-none');
      }
    }
  }

  function validarCampoGpsLive(id) {
    var el = document.getElementById(id);
    if (!el) return false;
    var ok = false;
    if (id === 'gpsFolio') {
      ok = !!(el.value && String(el.value).trim());
    } else if (id === 'gpsCondicion') {
      ok = !!(el.value && String(el.value).trim());
    } else if (id === 'gpsReferencia') {
      var ref = String(el.value || '').trim();
      if (ref.length > 60) {
        el.value = ref.slice(0, 60);
        ref = el.value;
      }
      ok = ref.length >= 3 && ref.length <= 60;
    } else if (id === 'gpsReporteroNombre') {
      var nom = String(el.value || '').replace(/[^A-Za-zÁÉÍÓÚÜáéíóúüÑñ .'\-]/g, '');
      if (nom !== el.value) el.value = nom;
      if (nom.length > 20) {
        el.value = nom.slice(0, 20);
        nom = el.value;
      }
      ok = nom.length >= 2 && nom.length <= 20 && /^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ .'\-]{2,20}$/.test(nom);
    } else if (id === 'gpsReporteroTel') {
      var digits = String(el.value || '').replace(/\D/g, '').slice(0, 10);
      if (digits !== el.value) el.value = digits;
      ok = digits.length === 10;
    } else if (id === 'gpsReporteroEmail') {
      var mail = String(el.value || '').replace(/[^A-Za-z0-9._%+\-@]/g, '');
      if (mail.length > 30) mail = mail.slice(0, 30);
      if (mail !== el.value) el.value = mail;
      ok = mail.length >= 6 && mail.length <= 30 && /^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/.test(mail);
    } else if (id === 'gpsFotoArchivo') {
      var file = el.files && el.files[0];
      if (!file) {
        marcarGpsCampo(el, null);
        return true;
      }
      var tipo = String(file.type || '').toLowerCase();
      ok = (!tipo || tipo.indexOf('image/') === 0) && file.size <= 5 * 1024 * 1024 && file.size >= 1024;
      if (!ok) {
        toast('Foto: JPG/PNG/WEBP, máx. 5 MB.', 'warning');
        try { el.value = ''; } catch (_) {}
        marcarGpsCampo(el, false);
        return false;
      }
    } else if (id === 'gpsLatitud' || id === 'gpsLongitud') {
      var lat = parseFloat((document.getElementById('gpsLatitud') || document.getElementById('gpsLat') || {}).value || '');
      var lng = parseFloat((document.getElementById('gpsLongitud') || document.getElementById('gpsLng') || {}).value || '');
      ok = isFinite(lat) && isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180;
      marcarGpsCampo(document.getElementById('gpsLatitud'), ok);
      return ok;
    }
    if (String(el.value || '').trim() === '' && el.type !== 'file') {
      marcarGpsCampo(el, null);
      return false;
    }
    marcarGpsCampo(el, ok);
    return ok;
  }
  global.validarCampoGpsLive = validarCampoGpsLive;

  function validarCaracteresGps() {
    const folioEl = document.getElementById('gpsFolio');
    const folio = (folioEl && folioEl.value) || '';
    if (!folio) {
      marcarGpsCampo(folioEl, false);
      toast('Selecciona un folio antes de enviar.', 'warning');
      return false;
    }
    marcarGpsCampo(folioEl, true);

    const condEl = document.getElementById('gpsCondicion');
    const cond = (condEl && condEl.value || '').trim();
    if (!cond) {
      marcarGpsCampo(condEl, false);
      toast('Selecciona la condición del animal.', 'warning');
      return false;
    }
    marcarGpsCampo(condEl, true);

    const refEl = document.getElementById('gpsReferencia');
    let ref = (refEl && refEl.value || '').trim();
    if (!ref || ref.length < 3 || ref.length > 60) {
      marcarGpsCampo(refEl, false);
      toast('Referencia del lugar: obligatoria, 3 a 60 caracteres.', 'warning');
      return false;
    }
    marcarGpsCampo(refEl, true);

    const nomEl = document.getElementById('gpsReporteroNombre');
    const nom = (nomEl && nomEl.value || '').trim();
    if (!nom || nom.length < 2 || nom.length > 20 || !/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ .'\-]{2,20}$/.test(nom)) {
      marcarGpsCampo(nomEl, false);
      toast('Nombre: obligatorio, 2 a 20 letras.', 'warning');
      return false;
    }
    marcarGpsCampo(nomEl, true);

    const telEl = document.getElementById('gpsReporteroTel');
    const telRaw = (telEl && telEl.value || '').trim();
    const digits = telRaw.replace(/\D/g, '');
    if (digits.length !== 10) {
      marcarGpsCampo(telEl, false);
      toast('Teléfono: exactamente 10 dígitos.', 'warning');
      return false;
    }
    if (telEl) telEl.value = digits;
    marcarGpsCampo(telEl, true);

    const mailEl = document.getElementById('gpsReporteroEmail');
    const mail = (mailEl && mailEl.value || '').trim().toLowerCase();
    if (!mail || mail.length > 30 || !/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/.test(mail)) {
      marcarGpsCampo(mailEl, false);
      toast('Correo: obligatorio, válido y máximo 30 caracteres.', 'warning');
      return false;
    }
    if (mailEl) mailEl.value = mail;
    marcarGpsCampo(mailEl, true);

    const lat = parseFloat((document.getElementById('gpsLatitud') || document.getElementById('gpsLat') || {}).value || '');
    const lng = parseFloat((document.getElementById('gpsLongitud') || document.getElementById('gpsLng') || {}).value || '');
    if (!isFinite(lat) || !isFinite(lng) || Math.abs(lat) > 90 || Math.abs(lng) > 180) {
      toast('Captura una ubicación GPS válida antes de enviar.', 'warning');
      return false;
    }
    return true;
  }

  function bindFiltrosCaracteresGps() {
    ['gpsFolio', 'gpsCondicion', 'gpsReferencia', 'gpsReporteroNombre', 'gpsReporteroTel', 'gpsReporteroEmail', 'gpsFotoArchivo'].forEach(function (id) {
      var el = document.getElementById(id);
      if (!el || el._rupeGpsBound) return;
      el._rupeGpsBound = true;
      var ev = (el.tagName === 'SELECT' || el.type === 'file') ? 'change' : 'input';
      el.addEventListener(ev, function () {
        validarCampoGpsLive(id);
      });
    });
    var dir = document.getElementById('gpsBuscarDireccion');
    if (dir && !dir._rupeGpsBound) {
      dir._rupeGpsBound = true;
      dir.addEventListener('input', function () {
        if (dir.value.length > 120) dir.value = dir.value.slice(0, 120);
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(bindFiltrosCaracteresGps, 0);
  });
  global.validarCaracteresGps = validarCaracteresGps;
  global.bindFiltrosCaracteresGps = bindFiltrosCaracteresGps;


  async function enviarReporteGPS(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    if (global.__rupeGpsSending) {
      return false;
    }
    global.__rupeGpsSending = true;

    if (!validarCaracteresGps()) {
      global.__rupeGpsSending = false;
      return false;
    }

    const form = document.getElementById('formGpsAvistamiento');
    if (form && typeof form.reportValidity === 'function') {
      // no bloquear por HTML5 si solo falta referencia opcional
      const refEl = document.getElementById('gpsReferencia');
      if (refEl) refEl.removeAttribute('required');
    }

    const folio = getFolioSeleccionadoGPS();
    if (!folio) {
      toast('Selecciona el folio antes de enviar el avistamiento.', 'warning');
      return false;
    }
    let reg = findRegistroByFolio(folio);
    if (!reg) {
      // refrescar lista
      try {
        const rList = await fetch('/api/tutor/mascotas', {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });
        const dList = await rList.json().catch(function () { return {}; });
        const arr = (dList.data && dList.data.mascotas) || dList.mascotas || [];
        if (Array.isArray(arr) && arr.length) {
          const st = ensureState();
          st.registros = arr.slice();
          if (global.RUPE_INIT) global.RUPE_INIT.mascotas = arr.slice();
          llenarSelectFoliosGPS();
          reg = findRegistroByFolio(folio);
        }
      } catch (_) {}
    }
    if (!reg) {
      toast('Folio no encontrado. Recarga la página (F5).', 'danger');
      return false;
    }
    let idMascota = 0;
    try {
      const sel = document.getElementById('gpsFolio');
      if (sel && sel.selectedOptions && sel.selectedOptions[0]) {
        idMascota = parseInt(sel.selectedOptions[0].getAttribute('data-id') || '0', 10) || 0;
      }
    } catch (_) {}
    if (idMascota <= 0) {
      idMascota = parseInt(reg.id_mascota != null ? reg.id_mascota : reg.id, 10) || 0;
    }
    idMascota = parseInt(idMascota, 10) || 0;
    if (idMascota <= 0 || !Number.isFinite(idMascota)) {
      toast('El folio no tiene ID numérico. Recarga la página (Ctrl+Shift+R).', 'danger');
      return false;
    }

    const lat = parseFloat((document.getElementById('gpsLatitud') || document.getElementById('gpsLat') || {}).value || '');
    const lng = parseFloat((document.getElementById('gpsLongitud') || document.getElementById('gpsLng') || {}).value || '');
    if (!isFinite(lat) || !isFinite(lng)) {
      toast('Captura GPS o busca una dirección antes de enviar.', 'warning');
      return false;
    }

    const condicion =
      (document.getElementById('gpsCondicion') || {}).value || 'Visto en la calle';
    let referencia = (
      (document.getElementById('gpsReferencia') || {}).value ||
      (document.getElementById('gpsNotas') || {}).value ||
      ''
    ).trim();
    if (!referencia) {
      referencia = 'Punto GPS capturado en mapa';
      const re = document.getElementById('gpsReferencia');
      if (re) re.value = referencia;
    }
    const reportadoPor = (
      (document.getElementById('gpsReporteroNombre') || {}).value ||
      (document.getElementById('gpsReportadoPor') || {}).value ||
      (document.getElementById('gpsNombreCiudadano') || {}).value ||
      'Ciudadano'
    ).trim() || 'Ciudadano';
    const reporteroTel = ((document.getElementById('gpsReporteroTel') || {}).value || '').trim();
    const reporteroEmail = ((document.getElementById('gpsReporteroEmail') || {}).value || '').trim();

    const descripcion = [
      'Avistamiento: ' + condicion,
      'Ref: ' + referencia,
      reportadoPor ? 'Reportó: ' + reportadoPor : '',
      reporteroTel ? 'Tel: ' + reporteroTel : '',
      reporteroEmail ? 'Email: ' + reporteroEmail : '',
      'GPS: ' + lat.toFixed(6) + ', ' + lng.toFixed(6),
    ]
      .filter(Boolean)
      .join(' · ');

    const csrf = await csrfToken();
    if (!csrf) {
      toast('Sesión CSRF no disponible. Recarga la página e inicia sesión de nuevo.', 'danger');
      return false;
    }

    const btn =
      document.querySelector('#formGpsAvistamiento button[type="submit"]') ||
      document.getElementById('btnEnviarAvistamiento');
    if (btn) btn.disabled = true;

    try {
      // Un solo INSERT: avistamiento (el backend también actualiza GPS en mascotas)
      // Guardar avistamiento en BD (JSON prioritario + foto base64)
      let fotoBase64 = '';
      const fileEl = document.getElementById('gpsFotoArchivo');
      if (global.fotoAvistamientoCapturada && String(global.fotoAvistamientoCapturada).indexOf('data:image/') === 0) {
        fotoBase64 = global.fotoAvistamientoCapturada;
      } else if (fileEl && fileEl.files && fileEl.files[0]) {
        try {
          fotoBase64 = await new Promise(function (resolve, reject) {
            var reader = new FileReader();
            reader.onload = function () { resolve(String(reader.result || '')); };
            reader.onerror = reject;
            reader.readAsDataURL(fileEl.files[0]);
          });
        } catch (_) { fotoBase64 = ''; }
      }

      const payload = {
        _csrf: csrf,
        folio: folio,
        condicion: condicion,
        referencia: referencia,
        contacto: reportadoPor,
        reportado_por: reportadoPor,
        telefono: reporteroTel,
        correo: reporteroEmail,
        latitud: lat,
        longitud: lng,
        foto_base64: fotoBase64 || undefined
      };

      const res = await fetch(
        '/api/tutor/mascotas/' + encodeURIComponent(idMascota) + '/seguimiento',
        {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrf,
          },
          credentials: 'same-origin',
          body: JSON.stringify(payload),
        }
      );
      let data = await res.json().catch(function () {
        return {};
      });

      if (!res.ok || data.success === false) {
        const msg =
          (data && data.message) ||
          (data && data.errors && JSON.stringify(data.errors)) ||
          'No se pudo guardar el avistamiento (HTTP ' + res.status + ').';
        toast(msg, 'danger');
        return false;
      }

      // Evitar acumular duplicados locales: partir de vacío y un solo envío
      reg.avistamientos = [];
      const fotoPrev =
        (data.data && (data.data.foto_url || (data.data.seguimiento && data.data.seguimiento.foto_url))) ||
        (global.fotoAvistamientoCapturada) ||
        ((document.getElementById('gpsFotoPreview') || {}).src) ||
        reg.foto ||
        reg.foto_url ||
        '';
      const idSeg =
        (data.data && (data.data.id_seguimiento || (data.data.seguimiento && data.data.seguimiento.id_seguimiento))) ||
        0;
      const rowAvi = {
        id: idSeg,
        id_seguimiento: idSeg,
        tipo: 'avistamiento',
        fechaHora: new Date().toLocaleString('es-MX', {
          dateStyle: 'short',
          timeStyle: 'short',
        }),
        condicion: condicion,
        foto: fotoPrev,
        foto_url: fotoPrev,
        lat: lat,
        lng: lng,
        latitud: lat,
        longitud: lng,
        referencia: referencia,
        reporteroNombre: reportadoPor,
        reportadoPor: reportadoPor,
        descripcion: descripcion,
        created_at: new Date().toISOString(),
      };
      reg.latitud = lat;
      reg.longitud = lng;
      // No acumular avistamientos locales (evita duplicados al pintar)
      reg.avistamientos = [];
      const st = ensureState();
      st.folioSeleccionado = folio;
      if (st.historial_seguimiento) st.historial_seguimiento = [];
      try { sessionStorage.setItem('rupe_seg_id', String(idSeg || '')); } catch (_) {}
      try { sessionStorage.setItem('rupe_seg_folio', folio || ''); } catch (_) {}

      toast(data.message || '¡Avistamiento guardado!', 'success');

      var dest = '/tutor/seguimiento?folio=' + encodeURIComponent(folio || '');
      if (idSeg) dest += '&seg=' + encodeURIComponent(String(parseInt(idSeg, 10) || idSeg));
      // Mantener bloqueo hasta salir de la página (evita doble POST)
      setTimeout(function () {
        try {
          window.location.replace(dest);
        } catch (_) {
          window.location.href = dest;
        }
      }, 300);
      return true;
    } catch (err) {
      console.error('[RUPE avistamiento]', err);
      toast(err.message || 'Error de red al enviar el avistamiento.', 'danger');
      global.__rupeGpsSending = false;
      if (btn) btn.disabled = false;
      return false;
    }
  }

  global.enviarReporteGPS = enviarReporteGPS;

  function verSeguimientoDesdeGPS() {
    if (typeof global.activarModuloTutor === 'function') {
      global.activarModuloTutor('seguimiento');
    } else {
      const btn = document.querySelector('.nav-modulo[data-modulo="seguimiento"]');
      if (btn) btn.click();
    }
  }
  global.verSeguimientoDesdeGPS = verSeguimientoDesdeGPS;
  global.ensureMapaGPS = ensureMapaGPS;
  global.rupeSetGpsCoords = setCoords;

  function bindGpsModule() {
    llenarSelectFoliosGPS();
    ensureMapaGPS();

    const form = document.getElementById('formGpsAvistamiento');
    if (form) {
      form.setAttribute('onsubmit', 'return false;');
      if (!form._rupeGpsBound) {
        form._rupeGpsBound = true;
        form.addEventListener('submit', function (ev) {
          ev.preventDefault();
          ev.stopPropagation();
          if (global.__rupeGpsSending) return false;
          enviarReporteGPS(ev);
          return false;
        }, true);
      }
    }
    const sel = document.getElementById('gpsFolio');
    if (sel && !sel._rupeBound) {
      sel._rupeBound = true;
      sel.addEventListener('change', sincronizarGpsPorFolio);
    }
    const btnCap = document.getElementById('btnCapturarGps');
    if (btnCap && !btnCap._rupeBound) {
      btnCap._rupeBound = true;
      btnCap.addEventListener('click', function (ev) {
        ev.preventDefault();
        capturarUbicacionGPS();
      });
    }
    const btnBuscar = document.querySelector(
      'button[onclick*="buscarDireccionGPS"], #btnBuscarDireccionGps'
    );
    if (btnBuscar && !btnBuscar._rupeBound) {
      btnBuscar._rupeBound = true;
      btnBuscar.addEventListener('click', function (ev) {
        ev.preventDefault();
        buscarDireccionGPS();
      });
    }
    const fotoInp = document.getElementById('gpsFotoArchivo');
    if (fotoInp && !fotoInp._rupeBound) {
      fotoInp._rupeBound = true;
      fotoInp.addEventListener('change', function () {
        previsualizarFotoAvistamiento(fotoInp);
      });
    }
    const inputDir = document.getElementById('gpsBuscarDireccion');
    if (inputDir && !inputDir._rupeBound) {
      inputDir._rupeBound = true;
      inputDir.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter') {
          ev.preventDefault();
          buscarDireccionGPS();
        }
      });
    }
  }

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.nav-modulo[data-modulo="gps-qr"]');
    if (btn) {
      setTimeout(function () {
        bindGpsModule();
        llenarSelectFoliosGPS();
        ensureMapaGPS();
      }, 300);
    }
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindGpsModule);
  } else {
    bindGpsModule();
  }
  global.addEventListener('load', function () {
    setTimeout(bindGpsModule, 200);
  });
  } catch (errSafe) {
    try { console.warn('[RUPE GPS] init safe', errSafe); } catch (_) {}
  }
})(typeof window !== 'undefined' ? window : globalThis);
/* safety: no throw if GPS form fields missing (consulta mode) */

/* RUPE_GPS_INIT_GUARD */
(function () {
  window.addEventListener('error', function (ev) {
    if (ev && ev.filename && String(ev.filename).indexOf('tutor-gps') >= 0) {
      console.warn('[RUPE GPS] capturado', ev.message);
    }
  });
})();
