/**
 * RUPE CDMX — Módulo Estatus (filtros, consultar, tabla bitácora, actualizar)
 */
'use strict';

(function (global) {
  function toast(msg, type) {
    if (typeof global.rupeToast === 'function') return global.rupeToast(msg, type || 'info');
    if (typeof global.mostrarToastTutor === 'function') return global.mostrarToastTutor(msg, type || 'success');
    try { console.log('[RUPE EST]', msg); } catch (_) {}
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

  function findByFolio(folio) {
    if (!folio) return null;
    var f = String(folio).replace(/^#/, '').trim().toUpperCase();
    var list = listRegistros();
    return (
      list.find(function (r) {
        return r && String(r.folio || '').replace(/^#/, '').toUpperCase() === f;
      }) ||
      list.find(function (r) {
        return r && String(r.folio || '').toUpperCase().indexOf(f) >= 0;
      }) ||
      null
    );
  }


  function marcarEstatusCampo(el, ok) {
    if (!el) return;
    if (ok === null) {
      el.classList.remove('is-valid', 'is-invalid');
      var h0 = document.getElementById(el.id + 'Ok');
      if (h0) {
        h0.style.display = 'none';
        h0.classList.add('d-none');
      }
      return;
    }
    el.classList.toggle('is-valid', !!ok);
    el.classList.toggle('is-invalid', !ok);
    var h = document.getElementById(el.id + 'Ok');
    if (h) {
      if (ok) {
        h.style.display = 'inline-block';
        h.classList.remove('d-none');
      } else {
        h.style.display = 'none';
        h.classList.add('d-none');
      }
    }
  }

  function validarCampoEstatusLive(id) {
    var el = document.getElementById(id);
    if (!el) return false;
    var ok = false;
    if (id === 'tutorInputBuscarFolio' || id === 'tutorSelectFolioRapido') {
      var v = String(el.value || '').trim();
      ok = v.length >= 3;
      // sync both
      if (id === 'tutorSelectFolioRapido' && ok) {
        var inp = document.getElementById('tutorInputBuscarFolio');
        if (inp && !inp.value) inp.value = v;
        marcarEstatusCampo(inp, ok);
      }
      if (id === 'tutorInputBuscarFolio') {
        marcarEstatusCampo(document.getElementById('tutorSelectFolioRapido'), ok && !!document.getElementById('tutorSelectFolioRapido') && !!document.getElementById('tutorSelectFolioRapido').value);
      }
    } else if (id === 'tutorNuevoEstatus') {
      ok = !!(el.value && String(el.value).trim());
    } else if (id === 'tutorEstatusComentario') {
      var c = String(el.value || '').trim();
      if (c.length > 220) {
        el.value = c.slice(0, 220);
        c = el.value.trim();
      }
      var cnt = document.getElementById('tutorEstatusComentarioContador');
      if (cnt) cnt.textContent = String(c.length);
      ok = c.length >= 5 && c.length <= 220;
    }
    if (String(el.value || '').trim() === '') {
      marcarEstatusCampo(el, null);
      return false;
    }
    marcarEstatusCampo(el, ok);
    return ok;
  }
  global.validarCampoEstatusLive = validarCampoEstatusLive;


  function badgeClass(est) {
    var e = String(est || 'En Búsqueda');
    if (e.indexOf('Localizado') >= 0) return 'bg-success';
    if (e.indexOf('Resguardo') >= 0) return 'bg-warning text-dark';
    return 'bg-danger';
  }


  function esAvistamientoEstatus(s) {
    if (!s) return false;
    if (typeof s === 'string') return /Avistamiento\s*:/i.test(s);
    var tipo = String(s.tipo || '').toLowerCase();
    var d = String(s.descripcion || s.bitacora || '');
    if (tipo === 'cambio_estatus' || tipo === 'nota') return false;
    if (tipo === 'avistamiento') return true;
    if (tipo === 'ubicacion' || tipo === 'ubicación') return /Avistamiento\s*:/i.test(d);
    return /Avistamiento\s*:/i.test(d);
  }

  function parseAvistamientoEst(s) {
    var d = typeof s === 'string' ? s : String((s && (s.descripcion || s.bitacora)) || '');
    var out = {
      condicion: 'Observado',
      referencia: '',
      reportadoPor: 'Ciudadano',
      fechaHora: '',
      lat: '',
      lng: '',
      foto: '',
      id: 0,
    };
    if (s && typeof s === 'object') {
      out.id = s.id_seguimiento || s.id || 0;
      out.fechaHora = s.created_at || s.fechaHora || s.fecha || '';
      out.lat = s.latitud != null && s.latitud !== '' ? String(s.latitud) : (s.lat != null ? String(s.lat) : '');
      out.lng = s.longitud != null && s.longitud !== '' ? String(s.longitud) : (s.lng != null ? String(s.lng) : '');
      out.foto = s.foto_url || s.foto || '';
      if (s.usuario_nombre) out.reportadoPor = s.usuario_nombre;
      if (s.reporteroNombre) out.reportadoPor = s.reporteroNombre;
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
    if (/herido/i.test(out.condicion) || /herido/i.test(d)) out.condicion = 'Herido';
    else if (/albergue/i.test(out.condicion) || /albergue/i.test(d)) out.condicion = 'Resguardado en albergue';
    else if (/veterinar/i.test(out.condicion)) out.condicion = 'Resguardado en veterinaria';
    else if (/domicilio/i.test(out.condicion)) out.condicion = 'Resguardado en domicilio';
    else if (/resguard/i.test(out.condicion) || /resguard/i.test(d)) out.condicion = 'Resguardado';
    else if (/observ|visto|calle|libre/i.test(out.condicion) || /observ|visto|calle/i.test(d)) out.condicion = 'Observado';
    else if (String((s && s.tipo) || '').toLowerCase() === 'avistamiento') out.condicion = 'Observado';
    if (!out.referencia && d) out.referencia = d.slice(0, 100);
    return out;
  }

  function normalizarListaAvistamientos(rawList) {
    var seen = {};
    var out = [];
    (Array.isArray(rawList) ? rawList : []).forEach(function (s) {
      if (!esAvistamientoEstatus(s)) return;
      var p = parseAvistamientoEst(s);
      var id = p.id || (s && (s.id_seguimiento || s.id)) || 0;
      var key = id
        ? 'id:' + id
        : 'g:' + [String(p.fechaHora).slice(0, 10), p.lat, p.lng, p.condicion].join('|');
      if (seen[key]) return;
      seen[key] = true;
      out.push({
        id: id,
        id_seguimiento: id,
        tipo: 'avistamiento',
        condicion: p.condicion,
        referencia: p.referencia,
        reporteroNombre: p.reportadoPor,
        fechaHora: p.fechaHora,
        created_at: p.fechaHora,
        lat: p.lat,
        lng: p.lng,
        latitud: p.lat,
        longitud: p.lng,
        foto: p.foto,
        foto_url: p.foto,
        descripcion: (s && s.descripcion) || p.referencia,
      });
    });
    return out;
  }

  function contarAvistamientosTutor() {
    var total = 0;
    listRegistros().forEach(function (r) {
      if (!r) return;
      var avis = normalizarListaAvistamientos(r.avistamientos || r.seguimientos || []);
      r.avistamientos = avis;
      total += avis.length;
    });
    return total;
  }


  function llenarSelectEstatus(forceList) {
    var sel = document.getElementById('tutorSelectFolioRapido');
    var datalist = document.getElementById('tutorListaFolios');
    var list = Array.isArray(forceList) ? forceList : listRegistros();
    var prev =
      (sel && sel.value) ||
      (global.state && global.state.folioSeleccionado) ||
      '';

    // Si no hay datos en memoria, conservar opciones PHP
    if ((!list || !list.length) && sel && sel.options.length > 1) {
      if (prev) {
        try { sel.value = String(prev); } catch (_) {}
      }
      return;
    }

    if (sel) {
      sel.innerHTML = '';
      var o0 = document.createElement('option');
      o0.value = '';
      o0.textContent = list.length ? '— Folios recientes —' : '— Sin registros (recarga) —';
      sel.appendChild(o0);
      var seen = {};
      list.forEach(function (r) {
        if (!r) return;
        var folio = String(r.folio || '').trim();
        if (!folio || seen[folio]) return;
        seen[folio] = true;
        var opt = document.createElement('option');
        opt.value = folio;
        opt.dataset.id = String(r.id || r.id_mascota || '');
        opt.textContent =
          '#' +
          folio +
          ' · ' +
          (r.nombre || r.mascota || 'Mascota') +
          (r.estatus ? ' · ' + r.estatus : '');
        sel.appendChild(opt);
      });
      if (prev) {
        try { sel.value = String(prev); } catch (_) {}
      }
    }

    if (datalist) {
      datalist.innerHTML = '';
      list.forEach(function (r) {
        if (!r || !r.folio) return;
        var o = document.createElement('option');
        o.value = String(r.folio);
        o.label = '#' + r.folio + ' · ' + (r.nombre || '');
        datalist.appendChild(o);
      });
    }
  }
  global.llenarSelectEstatus = llenarSelectEstatus;

  async function refrescarFiltrosEstatus() {
    try {
      var res = await fetch('/api/tutor/mascotas', {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });
      var data = await res.json().catch(function () { return {}; });
      var arr = (data.data && data.data.mascotas) || data.mascotas || [];
      var hist = (data.data && data.data.historial_seguimiento) || data.historial_seguimiento || [];
      if (Array.isArray(arr)) {
        if (!global.state) global.state = { registros: [] };
        global.state.registros = arr.slice();
        if (!global.RUPE_INIT) global.RUPE_INIT = {};
        global.RUPE_INIT.mascotas = arr.slice();
        if (Array.isArray(hist) && hist.length) {
          global.RUPE_INIT.historial_seguimiento = hist;
          global.state.historial_seguimiento = hist;
        }
        llenarSelectEstatus(arr);
        renderizarModuloEstatus5();
      } else {
        llenarSelectEstatus();
        renderizarModuloEstatus5();
      }
      return arr;
    } catch (err) {
      console.warn('[RUPE EST] filtros', err);
      llenarSelectEstatus();
      renderizarModuloEstatus5();
      return [];
    }
  }
  global.refrescarFiltrosEstatus = refrescarFiltrosEstatus;

  function renderizarModuloEstatus5() {
    var tbody = document.getElementById('tbodyControlEstatus5');
    if (!tbody) return;
    var regs = listRegistros();
    tbody.innerHTML = '';
    if (!regs.length) {
      tbody.innerHTML =
        '<tr><td colspan="4" class="text-center text-muted py-3">Sin registros de seguimiento.</td></tr>';
      return;
    }
    regs.forEach(function (reg) {
      var est = String(reg.estatus || 'En Búsqueda');
      var folio = String(reg.folio || '');
      var nombre = String(reg.nombre || '—');
      var raza = String(reg.raza || reg.raza_nombre || '');
      var bit = String(reg.bitacora || reg.notas || '');
      if (!bit || bit === 'null' || bit === 'undefined') bit = 'Registro en RUPE CDMX';
      var tr = document.createElement('tr');
      tr.style.cursor = 'pointer';
      tr.title = 'Consultar este folio';
      tr.setAttribute('data-folio', folio);
      tr.onclick = function () {
        var inp = document.getElementById('tutorInputBuscarFolio');
        if (inp) inp.value = folio;
        buscarPorFolioTutor();
      };
      tr.innerHTML =
        '<td class="ps-2 fw-bold text-danger">#' +
        folio.replace(/</g, '&lt;') +
        '</td>' +
        '<td><strong>' +
        nombre.replace(/</g, '&lt;') +
        '</strong>' +
        (raza
          ? ' <span class="text-muted">(' + raza.replace(/</g, '&lt;') + ')</span>'
          : '') +
        '</td>' +
        '<td><span class="badge ' +
        badgeClass(est) +
        '">' +
        est.replace(/</g, '&lt;') +
        '</span></td>' +
        '<td class="small text-muted">' +
        bit.replace(/</g, '&lt;') +
        '</td>';
      tbody.appendChild(tr);
    });
  }
  global.renderizarModuloEstatus5 = renderizarModuloEstatus5;
  global.contarAvistamientosTutor = contarAvistamientosTutor;
  global.normalizarListaAvistamientos = normalizarListaAvistamientos;

  function renderizarSeguimientoTutor(reg) {
    if (!reg) return;
    // Solo avistamientos reales (no notas ni cambios de estatus)
    reg.avistamientos = normalizarListaAvistamientos(reg.avistamientos || reg.seguimientos || []);

    var vacio = document.getElementById('tutorSeguimientoVacio');
    var cont = document.getElementById('tutorSeguimientoContenido');
    if (vacio) vacio.classList.add('d-none');
    if (cont) cont.classList.remove('d-none');

    var setT = function (id, val) {
      var el = document.getElementById(id);
      if (el) el.textContent = val != null && val !== '' ? String(val) : '—';
    };

    setT('tutorSegFolio', '#' + (reg.folio || ''));
    setT(
      'tutorSegNombre',
      [reg.nombre, reg.raza || reg.raza_nombre].filter(Boolean).join(' · ')
    );

    var badge = document.getElementById('tutorSegEstatusBadge');
    if (badge) {
      badge.textContent = reg.estatus || 'En Búsqueda';
      badge.className = 'badge ' + badgeClass(reg.estatus);
    }

    setT(
      'tutorSegUltimaAct',
      reg.bitacora || reg.notas || 'Registro en RUPE CDMX'
    );
    setT('tutorSegNumAvist', String(reg.avistamientos.length));

    var contacto = document.getElementById('tutorSegContactoTutor');
    if (contacto) {
      if (reg.tutorNombre) {
        contacto.innerHTML =
          '<strong>' +
          reg.tutorNombre +
          '</strong><br>' +
          (reg.tutorTel || '') +
          '<br><span class="text-muted">' +
          (reg.tutorEmail || '') +
          '</span>';
      } else if (global.RUPE_INIT && global.RUPE_INIT.user) {
        var u = global.RUPE_INIT.user;
        contacto.innerHTML =
          '<strong>' +
          (u.nombre || '') +
          '</strong><br>' +
          (u.telefono || '') +
          '<br><span class="text-muted">' +
          (u.correo || '') +
          '</span>';
      } else {
        contacto.innerHTML =
          '<span class="text-muted">Datos del tutor de la sesión.</span>';
      }
    }

    var selEst = document.getElementById('tutorNuevoEstatus');
    if (selEst) {
      var e = String(reg.estatus || 'En Búsqueda');
      if (e.indexOf('Localizado') >= 0) selEst.value = 'Localizado';
      else if (e.indexOf('Resguardo') >= 0) selEst.value = 'En Resguardo';
      else selEst.value = 'En Búsqueda';
    }

    var lista = document.getElementById('tutorListaAvistamientos');
    if (lista) {
      var nAvi = reg.avistamientos.length;
      setT('tutorSegNumAvist', String(nAvi));
      var headerExtra = document.getElementById('tutorListaAvistHeaderCount');
      if (headerExtra) headerExtra.textContent = nAvi ? ('(' + nAvi + ')') : '';

      if (!nAvi) {
        lista.innerHTML =
          '<div class="text-center text-muted py-4 px-3">' +
          '<i class="bi bi-geo-alt display-6 d-block mb-2 opacity-50"></i>' +
          '<p class="mb-1">Aún no hay avistamientos reportados para este folio.</p>' +
          '<p class="small mb-0">Los reportes llegan desde <strong>GPS / QR</strong> cuando un ciudadano envía ubicación.</p>' +
          '</div>';
      } else {
        lista.innerHTML = reg.avistamientos
          .map(function (a, idx) {
            var foto =
              a.foto ||
              a.foto_url ||
              reg.foto ||
              reg.foto_url ||
              '/assets/img/placeholder-perro.svg';
            var cond = a.condicion || 'Observado';
            var fecha = a.fechaHora || a.created_at || '—';
            try {
              var dt = new Date(fecha);
              if (!isNaN(dt.getTime())) {
                fecha = dt.toLocaleString('es-MX', {
                  day: '2-digit', month: '2-digit', year: 'numeric',
                  hour: '2-digit', minute: '2-digit',
                });
              }
            } catch (_) {}
            var ref = a.referencia || '';
            var lat = a.lat || a.latitud || '';
            var lng = a.lng || a.longitud || '';
            var gpsTxt = lat && lng ? (Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5)) : 'Sin GPS';
            var badge =
              /herido/i.test(cond) ? 'bg-danger' :
              /resguard/i.test(cond) ? 'bg-success' :
              'bg-warning text-dark';
            var reporto = a.reporteroNombre || a.usuario_nombre || 'Ciudadano';
            return (
              '<div class="border-bottom p-3 avistamiento-card" data-avi-idx="' + idx + '">' +
              '<div class="row g-3 align-items-center">' +
              '<div class="col-auto">' +
              '<img src="' + String(foto).replace(/"/g, '&quot;') + '" alt="Foto reportada" ' +
              'style="width:80px;height:80px;object-fit:cover;border-radius:10px;background:#f0f0f0" class="border" ' +
              'onerror="this.onerror=null;this.src=\'/assets/img/placeholder-mascota.svg\'">' +
              '</div>' +
              '<div class="col">' +
              '<div class="d-flex justify-content-between flex-wrap gap-2 mb-1">' +
              '<span class="badge ' + badge + '">' + String(cond).replace(/</g, '&lt;') + '</span>' +
              '<small class="text-muted"><i class="bi bi-clock me-1"></i>' + String(fecha).replace(/</g, '&lt;') + '</small>' +
              '</div>' +
              '<p class="mb-1 small"><i class="bi bi-geo-alt-fill text-danger me-1"></i><strong>GPS:</strong> <code>' +
              gpsTxt + '</code></p>' +
              '<p class="mb-1 small"><i class="bi bi-signpost-2 me-1"></i><strong>Referencia:</strong> ' +
              String(ref || '—').replace(/</g, '&lt;') + '</p>' +
              '<p class="mb-0 small"><i class="bi bi-person me-1"></i><strong>Reportado por:</strong> ' +
              String(reporto).replace(/</g, '&lt;') + '</p>' +
              '</div></div></div>'
            );
          })
          .join('');
      }
    }
  }
  global.renderizarSeguimientoTutor = renderizarSeguimientoTutor;

  async function buscarPorFolioTutor() {
    var inp = document.getElementById('tutorInputBuscarFolio');
    var folioRaw = inp ? String(inp.value || '').trim() : '';
    var folio = folioRaw.replace(/^#/, '');

    if (!folio) {
      // Si hay select, usar valor del select
      var sel = document.getElementById('tutorSelectFolioRapido');
      if (sel && sel.value) {
        folio = sel.value;
        if (inp) inp.value = folio;
      }
    }

    if (!folio) {
      toast('Escribe o selecciona un folio para consultar.', 'warning');
      return;
    }

    var reg = findByFolio(folio);
    if (!reg) {
      // Intentar recargar desde API
      try {
        var res = await fetch('/api/tutor/mascotas', {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });
        var data = await res.json().catch(function () {
          return {};
        });
        var arr =
          (data.data && data.data.mascotas) || data.mascotas || [];
        if (Array.isArray(arr) && arr.length) {
          if (global.state) global.state.registros = arr.slice();
          if (global.RUPE_INIT) global.RUPE_INIT.mascotas = arr.slice();
          llenarSelectEstatus();
          renderizarModuloEstatus5();
          reg = findByFolio(folio);
        }
      } catch (_) {}
    }

    if (!reg) {
      var vacio = document.getElementById('tutorSeguimientoVacio');
      var cont = document.getElementById('tutorSeguimientoContenido');
      if (vacio) vacio.classList.remove('d-none');
      if (cont) cont.classList.add('d-none');
      toast('No se encontró el folio: ' + folio, 'warning');
      return;
    }

    if (global.state) global.state.folioSeleccionado = reg.folio;
    if (inp) inp.value = reg.folio;
    var sel2 = document.getElementById('tutorSelectFolioRapido');
    if (sel2) {
      try {
        sel2.value = String(reg.folio);
      } catch (_) {}
    }

    renderizarSeguimientoTutor(reg);

    // Cargar seguimientos frescos
    var id = reg.id || reg.id_mascota;
    if (id) {
      try {
        var r2 = await fetch('/api/tutor/mascotas/' + encodeURIComponent(id), {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });
        var d2 = await r2.json().catch(function () {
          return {};
        });
        var m = (d2.data && d2.data.mascota) || {};
        var segs = (d2.data && d2.data.seguimientos) || [];
        if (m.estatus) reg.estatus = m.estatus;
        if (m.notas) reg.notas = m.notas;
        if (m.bitacora) reg.bitacora = m.bitacora;
        else if (m.notas) reg.bitacora = m.notas;
        reg.avistamientos = normalizarListaAvistamientos(Array.isArray(segs) ? segs : []);
        renderizarSeguimientoTutor(reg);
        renderizarModuloEstatus5();
      } catch (_) {}
    }

    marcarEstatusCampo(document.getElementById('tutorInputBuscarFolio'), true);
    marcarEstatusCampo(document.getElementById('tutorSelectFolioRapido'), true);
    toast('Folio #' + reg.folio + ' cargado.', 'success');
  }
  global.buscarPorFolioTutor = buscarPorFolioTutor;

  async function csrfToken() {
    var csrf =
      global.__CSRF__ || (global.RUPE_INIT && global.RUPE_INIT.csrf) || '';
    if (!csrf) {
      try {
        var r = await fetch('/api/csrf', { credentials: 'same-origin' });
        var d = await r.json();
        csrf = (d.data && d.data.csrf) || d.csrf || '';
        global.__CSRF__ = csrf;
      } catch (_) {}
    }
    return csrf;
  }

  async function cambiarEstatusTutor(e) {
    if (e && e.preventDefault) e.preventDefault();
    var folio =
      (global.state && global.state.folioSeleccionado) ||
      ((document.getElementById('tutorInputBuscarFolio') || {}).value || '').replace(/^#/, '');
    var reg = findByFolio(folio);
    if (!reg) {
      toast('Selecciona un folio primero (Consultar).', 'warning');
      return;
    }
    var estOk = validarCampoEstatusLive('tutorNuevoEstatus');
    var comOk = validarCampoEstatusLive('tutorEstatusComentario');
    var folioOk = validarCampoEstatusLive('tutorInputBuscarFolio') || validarCampoEstatusLive('tutorSelectFolioRapido');
    var nuevo = ((document.getElementById('tutorNuevoEstatus') || {}).value || '').trim();
    var comentario = (
      (document.getElementById('tutorEstatusComentario') || {}).value || ''
    ).trim();
    if (!folioOk && !folio) {
      toast('Selecciona o escribe un folio primero.', 'warning');
      return;
    }
    if (!nuevo || !estOk) {
      toast('Elige el nuevo estatus.', 'warning');
      return;
    }
    if (!comentario || comentario.length < 5) {
      toast('Bitácora / comentario: mínimo 5 caracteres.', 'warning');
      return;
    }
    var bit =
      (comentario || 'Cambio de estatus a ' + nuevo) +
      ' · ' +
      new Date().toLocaleString('es-MX');
    var id = reg.id || reg.id_mascota;
    if (!id) {
      toast('El folio no tiene ID. Recarga la página.', 'danger');
      return;
    }

    var btn =
      document.querySelector('#tutorFormCambiarEstatus button[type="submit"]') ||
      null;
    if (btn) btn.disabled = true;

    try {
      var csrf = await csrfToken();
      var fd = new FormData();
      fd.append('_method', 'PUT');
      fd.append('estatus', nuevo);
      fd.append('notas', bit);
      if (csrf) fd.append('_csrf', csrf);

      var res = await fetch('/api/tutor/mascotas/' + encodeURIComponent(id), {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf,
        },
      });
      var data = await res.json().catch(function () {
        return {};
      });
      if (!res.ok || data.success === false) {
        toast((data && data.message) || 'No se pudo actualizar el estatus.', 'danger');
        return;
      }

      // Bitácora
      try {
        await fetch(
          '/api/tutor/mascotas/' + encodeURIComponent(id) + '/seguimiento',
          {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
              'Content-Type': 'application/json',
              Accept: 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-Token': csrf,
            },
            body: JSON.stringify({
              tipo: 'cambio_estatus',
              descripcion: bit,
              _csrf: csrf,
            }),
          }
        );
      } catch (_) {}

      reg.estatus = nuevo;
      reg.bitacora = bit;
      reg.notas = bit;

      // sync lists
      listRegistros().forEach(function (r) {
        if ((r.id || r.id_mascota) == id || String(r.folio) === String(reg.folio)) {
          r.estatus = nuevo;
          r.bitacora = bit;
          r.notas = bit;
        }
      });

      var ce = document.getElementById('tutorEstatusComentario');
      if (ce) ce.value = '';
      renderizarSeguimientoTutor(reg);
      renderizarModuloEstatus5();
      llenarSelectEstatus();
      toast('Estatus actualizado a: ' + nuevo, 'success');
    } catch (err) {
      console.error('[RUPE estatus]', err);
      toast('No se pudo guardar el estatus.', 'danger');
    } finally {
      if (btn) btn.disabled = false;
    }
  }
  global.cambiarEstatusTutor = cambiarEstatusTutor;

  function bind() {
    // folio desde URL
    try {
      var params = new URLSearchParams(window.location.search || '');
      var folioQ = params.get('folio') || '';
      if (folioQ) {
        if (global.state) global.state.folioSeleccionado = folioQ;
        var inp0 = document.getElementById('tutorInputBuscarFolio');
        if (inp0) inp0.value = folioQ;
      }
    } catch (_) {}

    var form = document.getElementById('tutorFormCambiarEstatus');
    if (form && !form._rupeEstBound) {
      form._rupeEstBound = true;
      form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        cambiarEstatusTutor(ev);
      });
      ['tutorNuevoEstatus', 'tutorEstatusComentario'].forEach(function (id) {
        var el = document.getElementById(id);
        if (!el || el._rupeEstValBound) return;
        el._rupeEstValBound = true;
        var evn = el.tagName === 'SELECT' ? 'change' : 'input';
        el.addEventListener(evn, function () { validarCampoEstatusLive(id); });
      });
    }
    var folioInp = document.getElementById('tutorInputBuscarFolio');
    if (folioInp && !folioInp._rupeEstValBound) {
      folioInp._rupeEstValBound = true;
      folioInp.addEventListener('input', function () { validarCampoEstatusLive('tutorInputBuscarFolio'); });
    }

    var btn = document.getElementById('btnConsultarFolioEstatus');
    if (btn && !btn._rupeEstBound) {
      btn._rupeEstBound = true;
      btn.addEventListener('click', function (ev) {
        ev.preventDefault();
        buscarPorFolioTutor();
      });
    }

    var sel = document.getElementById('tutorSelectFolioRapido');
    if (sel && !sel._rupeEstBound) {
      sel._rupeEstBound = true;
      sel.addEventListener('change', function () {
        var inp = document.getElementById('tutorInputBuscarFolio');
        if (inp) inp.value = sel.value;
        if (sel.value) buscarPorFolioTutor();
      });
    }

    var inp = document.getElementById('tutorInputBuscarFolio');
    if (inp && !inp._rupeEstBound) {
      inp._rupeEstBound = true;
      inp.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter') {
          ev.preventDefault();
          buscarPorFolioTutor();
        }
      });
    }

    // Cargar filtros y datos reales desde BD
    refrescarFiltrosEstatus().then(function () {
      var folio =
        (global.state && global.state.folioSeleccionado) ||
        (document.getElementById('tutorInputBuscarFolio') || {}).value ||
        (document.getElementById('tutorSelectFolioRapido') || {}).value ||
        '';
      if (folio) {
        buscarPorFolioTutor();
      }
    });
  }
  global.bindEstatus = bind;

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.nav-modulo[data-modulo="estatus"]');
    if (btn) {
      setTimeout(function () {
        bind();
      }, 50);
    }
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bind);
  } else {
    bind();
  }
})(typeof window !== 'undefined' ? window : globalThis);
