/**
 * RUPE CDMX — Mi Perfil: select de mascotas, ver ficha correspondiente, PDF sin errores
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
      global.state = { registros: [], tutorActual: {}, folioSeleccionado: '' };
    }
    if (!global.state.tutorActual || typeof global.state.tutorActual !== 'object') {
      global.state.tutorActual = {};
    }
    if (!Array.isArray(global.state.registros) || !global.state.registros.length) {
      global.state.registros =
        global.RUPE_INIT && Array.isArray(global.RUPE_INIT.mascotas)
          ? global.RUPE_INIT.mascotas.slice()
          : [];
    }
    return global.state;
  }

  function listMascotas() {
    var st = ensureState();
    if (Array.isArray(st.registros) && st.registros.length) return st.registros;
    if (global.RUPE_INIT && Array.isArray(global.RUPE_INIT.mascotas) && global.RUPE_INIT.mascotas.length) {
      st.registros = global.RUPE_INIT.mascotas.slice();
      return st.registros;
    }
    if (global.__RUPE_MASCOTAS__ && Array.isArray(global.__RUPE_MASCOTAS__) && global.__RUPE_MASCOTAS__.length) {
      st.registros = global.__RUPE_MASCOTAS__.slice();
      return st.registros;
    }
    return Array.isArray(st.registros) ? st.registros : [];
  }

  function normalizarMascota(m) {
    if (!m || typeof m !== 'object') return null;
    var id = parseInt(m.id_mascota != null ? m.id_mascota : m.id, 10) || 0;
    return {
      id: id,
      id_mascota: id,
      folio: String(m.folio || ''),
      nombre: String(m.nombre || m.mascota || ''),
      raza: String(m.raza || m.raza_nombre || ''),
      raza_nombre: String(m.raza_nombre || m.raza || ''),
      alcaldia: String(m.alcaldia || m.alcaldia_nombre || ''),
      alcaldia_nombre: String(m.alcaldia_nombre || m.alcaldia || ''),
      estatus: String(m.estatus || 'En Búsqueda'),
      foto: String(m.foto || m.foto_url || ''),
      foto_url: String(m.foto_url || m.foto || ''),
      fecha_extravio: String(m.fecha_extravio || m.fecha || ''),
      fecha: String(m.fecha || m.fecha_extravio || ''),
    };
  }

  async function cargarMascotasDesdeApi() {
    try {
      var res = await fetch('/api/tutor/mascotas', {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });
      var data = await res.json().catch(function () { return {}; });
      if (!res.ok || data.success === false) {
        return listMascotas();
      }
      var arr =
        (data.data && (data.data.mascotas || data.data.list || data.data.items)) ||
        data.mascotas ||
        [];
      if (!Array.isArray(arr)) arr = [];
      var st = ensureState();
      st.registros = arr.map(normalizarMascota).filter(function (x) {
        return x && (x.id_mascota > 0 || x.folio);
      });
      if (global.RUPE_INIT) global.RUPE_INIT.mascotas = st.registros.slice();
      return st.registros;
    } catch (err) {
      console.warn('[RUPE PERFIL] mascotas API', err);
      return listMascotas();
    }
  }
  global.cargarMascotasDesdeApi = cargarMascotasDesdeApi;

  function findByFolio(folio) {
    folio = String(folio || '');
    return listMascotas().find(function (r) {
      return String(r.folio) === folio;
    }) || null;
  }

  function loadTutorFromInit() {
    var st = ensureState();
    var u = (global.RUPE_INIT && global.RUPE_INIT.user) || {};
    st.tutorActual = {
      id: u.id || u.id_usuario || '',
      nombre: u.nombre || st.tutorActual.nombre || '',
      telefono: u.telefono || st.tutorActual.telefono || '',
      correo: u.correo || u.email || st.tutorActual.correo || '',
      aceptaPrivacidad:
        u.acepta_privacidad != null
          ? !!Number(u.acepta_privacidad)
          : st.tutorActual.aceptaPrivacidad !== false,
      created_at: u.created_at || u.fecha_registro || '',
    };
    return st.tutorActual;
  }

  async function cargarPerfilDesdeApi() {
    try {
      var res = await fetch('/api/tutor/perfil', {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });
      var data = await res.json().catch(function () {
        return {};
      });
      var user = (data.data && data.data.user) || data.user || null;
      if (user) {
        var st = ensureState();
        st.tutorActual = {
          id: user.id || user.id_usuario || st.tutorActual.id,
          nombre: user.nombre || '',
          telefono: user.telefono || '',
          correo: user.correo || user.email || '',
          aceptaPrivacidad:
            user.acepta_privacidad != null ? !!Number(user.acepta_privacidad) : true,
          created_at: user.created_at || '',
        };
        if (global.RUPE_INIT) global.RUPE_INIT.user = user;
      }
    } catch (err) {
      console.warn('[RUPE PERFIL] API', err);
    }
  }

  function initials(name) {
    var parts = String(name || 'T').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return 'TU';
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }

  function setText(id, val) {
    var el = document.getElementById(id);
    if (el) el.textContent = val != null && String(val).trim() !== '' ? String(val) : '—';
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function llenarSelectPerfil() {
    var sel = document.getElementById('perfilSelectMascota');
    if (!sel) return;
    var regs = listMascotas();
    var prev = sel.value || (ensureState().folioSeleccionado || '');
    sel.innerHTML = '<option value="">— Selecciona un folio —</option>';
    regs.forEach(function (r) {
      var o = document.createElement('option');
      o.value = String(r.folio || '');
      o.textContent =
        '#' +
        (r.folio || '') +
        ' · ' +
        (r.nombre || 'Mascota') +
        ' · ' +
        (r.estatus || '');
      sel.appendChild(o);
    });
    if (prev) {
      try {
        sel.value = String(prev);
      } catch (_) {}
    }
  }

  function pintarPreviewPerfil(reg) {
    var box = document.getElementById('perfilFichaPreview');
    if (!box) return;
    if (!reg) {
      box.innerHTML =
        '<p class="text-muted small mb-0">Selecciona un folio para ver la ficha correspondiente.</p>';
      return;
    }
    var foto =
      reg.foto ||
      reg.foto_url ||
      '/assets/img/placeholder-perro.svg';
    if (foto && foto.charAt(0) !== '/' && foto.indexOf('http') !== 0 && foto.indexOf('data:') !== 0) {
      foto = '/assets/uploads/' + foto.replace(/^\/+/, '');
    }
    box.innerHTML =
      '<div class="d-flex gap-3 align-items-start flex-wrap">' +
      '<img src="' +
      esc(foto) +
      '" alt="Foto" width="88" height="88" class="rounded border" style="object-fit:cover;background:#f0f0f0" onerror="this.onerror=null;this.src=\'/assets/img/placeholder-mascota.svg\'">' +
      '<div class="flex-grow-1">' +
      '<div class="fw-bold text-danger">#' +
      esc(reg.folio) +
      ' · ' +
      esc(reg.nombre || '') +
      '</div>' +
      '<div class="small text-muted">Raza: ' +
      esc(reg.raza || reg.raza_nombre || '—') +
      ' · ' +
      esc(reg.estatus || '') +
      '</div>' +
      '<div class="small">Alcaldía: ' +
      esc(reg.alcaldia || reg.alcaldia_nombre || '—') +
      '</div>' +
      '<div class="small">Fecha extravío: ' +
      esc(String(reg.fecha || reg.fecha_extravio || '').slice(0, 10) || '—') +
      '</div>' +
      '</div></div>';
  }

  function renderizarPerfilTutor() {
    var st = ensureState();
    var t = st.tutorActual || loadTutorFromInit();
    setText('perfilNombreDisplay', t.nombre || 'Tutor');
    setText('perfilTelefonoDisplay', t.telefono || '—');
    setText('perfilCorreoDisplay', t.correo || '—');
    setText(
      'perfilPrivacidadDisplay',
      t.aceptaPrivacidad ? 'Aceptó Aviso de Privacidad' : 'Pendiente de aceptar privacidad'
    );
    setText('perfilFechaRegistro', t.created_at ? String(t.created_at).slice(0, 10) : 'ago 2026');

    var av = document.getElementById('perfilAvatarGrande');
    if (av) av.textContent = initials(t.nombre);

    var un = document.getElementById('tutorUserName');
    if (un && t.nombre) un.textContent = t.nombre.split(/\s+/)[0] || t.nombre;
    var uh = document.getElementById('tutorUserHeader');
    if (uh && t.nombre) uh.textContent = t.nombre;

    var regs = listMascotas();
    setText('perfilStatTotal', regs.length);
    setText(
      'perfilStatActivos',
      regs.filter(function (r) {
        return /búsqueda|busqueda/i.test(String(r.estatus || ''));
      }).length
    );
    setText(
      'perfilStatResueltos',
      regs.filter(function (r) {
        return /localiz|resguardo/i.test(String(r.estatus || ''));
      }).length
    );

        llenarSelectPerfil();

    var sel = document.getElementById('perfilSelectMascota');
    var folioF = sel && sel.value ? sel.value : '';
    // Listado completo siempre (el select solo controla la vista previa)
    var list = regs.slice();

    pintarPreviewPerfil(folioF ? findByFolio(folioF) : null);

    var tbody = document.getElementById('tbodyPerfilMascotas');
    if (tbody) {
      tbody.innerHTML = '';
      if (!list.length) {
        tbody.innerHTML =
          '<tr><td colspan="6" class="text-center text-muted py-3">Aún no tienes mascotas registradas. Registra una desde el módulo Registro.</td></tr>';
      } else {
        list.forEach(function (r) {
          var idM = parseInt(r.id_mascota != null ? r.id_mascota : r.id, 10) || 0;
          var tr = document.createElement('tr');
          tr.dataset.id = String(idM);
          tr.dataset.folio = String(r.folio || '');
          var folioSafe = String(r.folio || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
          var badge =
            /localiz/i.test(String(r.estatus || ''))
              ? 'bg-success'
              : /resguardo/i.test(String(r.estatus || ''))
                ? 'bg-warning text-dark'
                : 'bg-danger';
          tr.innerHTML =
            '<td class="ps-3 fw-bold text-danger text-nowrap">#' +
            esc(r.folio || '—') +
            '</td>' +
            '<td>' +
            esc(r.nombre || '—') +
            (r.raza || r.raza_nombre
              ? '<div class="small text-muted">' + esc(r.raza || r.raza_nombre) + '</div>'
              : '') +
            '</td>' +
            '<td class="small">' +
            esc(r.alcaldia || r.alcaldia_nombre || '—') +
            '</td>' +
            '<td><span class="badge ' +
            badge +
            '">' +
            esc(r.estatus || 'En Búsqueda') +
            '</span></td>' +
            '<td class="small text-nowrap">' +
            esc(r.fecha_extravio || r.fecha || '—') +
            '</td>' +
            '<td class="text-end pe-3 text-nowrap">' +
            '<button type="button" class="btn btn-sm btn-outline-primary me-1" title="Ver ficha" data-perfil-ver="' +
            esc(r.folio || '') +
            '" data-perfil-id="' +
            idM +
            '"><i class="bi bi-eye"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger" title="Descargar PDF" data-perfil-pdf="' +
            esc(r.folio || '') +
            '" data-perfil-id="' +
            idM +
            '"><i class="bi bi-file-earmark-pdf"></i></button>' +
            '</td>';
          tbody.appendChild(tr);
        });
      }
    }
  }

  global.renderizarPerfilTutor = renderizarPerfilTutor;

  /** Ir al módulo ficha y pintar el folio elegido */
  function verFichaDesdePerfil(folio) {
    folio = String(folio || '').trim();
    if (!folio) {
      var sel = document.getElementById('perfilSelectMascota');
      folio = sel ? String(sel.value || '') : '';
    }
    if (!folio) {
      toast('Selecciona un folio en el listado.', 'warning');
      return;
    }
    var reg = findByFolio(folio);
    if (!reg) {
      toast('No se encontró ese folio.', 'warning');
      return;
    }
    var st = ensureState();
    st.folioSeleccionado = folio;

    // Sincronizar selects de ficha
    ['tutorSelectFolioFicha', 'tutorSelectFolioRapido', 'selectFolioSeguimiento', 'gpsFolio'].forEach(
      function (id) {
        var el = document.getElementById(id);
        if (el) {
          try {
            el.value = folio;
          } catch (_) {}
        }
      }
    );

    var btn = document.querySelector('.nav-modulo[data-modulo="ficha-pdf"]');
    if (btn) btn.click();

    function paint() {
      st.folioSeleccionado = folio;
      var selF = document.getElementById('tutorSelectFolioFicha');
      if (selF) {
        try {
          selF.value = folio;
        } catch (_) {}
      }
      if (typeof global.renderizarFichaTutor === 'function') {
        try {
          global.renderizarFichaTutor(reg);
        } catch (e) {
          console.warn(e);
        }
      }
      // QR
      var token = reg.qr_token || reg.folio;
      var base = (
        (global.RUPE_INIT && global.RUPE_INIT.app_url) ||
        (global.location && global.location.origin) ||
        ''
      ).replace(/\/$/, '');
      var url = base + '/mascota/' + encodeURIComponent(token);
      if (typeof global.rupeGenerarQR === 'function') {
        global.rupeGenerarQR(url, reg.folio);
      } else {
        var img = document.getElementById('tutorFichaQR');
        if (img) {
          img.src =
            'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' +
            encodeURIComponent(url);
        }
      }
    }

    // Cargar datos completos desde API si hay id
    var idR = reg.id || reg.id_mascota;
    if (idR) {
      fetch('/api/tutor/mascotas/' + encodeURIComponent(idR), {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (full) {
          var fm = (full.data && full.data.mascota) || full.mascota || {};
          Object.keys(fm).forEach(function (k) {
            if (fm[k] !== undefined && fm[k] !== null && fm[k] !== '') reg[k] = fm[k];
          });
          paint();
        })
        .catch(function () {
          paint();
        });
    }

    setTimeout(paint, 40);
    toast('Ficha de #' + folio + ' lista.', 'success');
  }
  global.verFichaDesdePerfil = verFichaDesdePerfil;
  global.seleccionarYVerPDF = verFichaDesdePerfil;

  /** Abrir ficha y lanzar impresión/PDF carta */
  function descargarPdfDesdePerfil(folio) {
    folio = String(folio || '').trim();
    if (!folio) {
      var sel = document.getElementById('perfilSelectMascota');
      folio = sel ? String(sel.value || '') : '';
    }
    if (!folio) {
      toast('Selecciona un folio para descargar el PDF.', 'warning');
      return;
    }
    verFichaDesdePerfil(folio);
    // Esperar a que la ficha se pinte y luego print (Guardar como PDF)
    setTimeout(function () {
      document.body.classList.add('print-ficha-oficial');
      try {
        // Activar módulo ficha si no está
        var sec = document.getElementById('modulo-ficha-pdf');
        if (sec && !sec.classList.contains('activo')) {
          var b = document.querySelector('.nav-modulo[data-modulo="ficha-pdf"]');
          if (b) b.click();
        }
        setTimeout(function () {
          try {
            window.print();
          } finally {
            setTimeout(function () {
              document.body.classList.remove('print-ficha-oficial');
            }, 400);
          }
        }, 450);
      } catch (err) {
        document.body.classList.remove('print-ficha-oficial');
        toast('No se pudo abrir el diálogo de impresión.', 'danger');
      }
    }, 350);
    toast('En el diálogo elige «Guardar como PDF».', 'info');
  }
  global.descargarPdfDesdePerfil = descargarPdfDesdePerfil;

  function abrirModalEditarPerfil() {
    loadTutorFromInit();
    var t = ensureState().tutorActual;
    var n = document.getElementById('perfilInputNombre');
    var tel = document.getElementById('perfilInputTelefono');
    var cor = document.getElementById('perfilInputCorreo');
    var priv = document.getElementById('perfilInputPrivacidad');
    if (n) n.value = t.nombre || '';
    if (tel) tel.value = t.telefono || '';
    if (cor) cor.value = t.correo || '';
    if (priv) priv.checked = !!t.aceptaPrivacidad;

    var modalEl = document.getElementById('modalEditarPerfil');
    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
      try {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
      } catch (err) {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
      }
    } else if (global.modalEditarPerfilBs) {
      global.modalEditarPerfilBs.show();
    } else if (modalEl) {
      modalEl.classList.add('show');
      modalEl.style.display = 'block';
    } else {
      toast('Modal de edición no encontrado. Recarga la página.', 'danger');
    }
  }
  global.abrirModalEditarPerfil = abrirModalEditarPerfil;

  async function guardarPerfilTutor(e) {
    if (e && e.preventDefault) e.preventDefault();
    var nombre = ((document.getElementById('perfilInputNombre') || {}).value || '').trim();
    var telefono = ((document.getElementById('perfilInputTelefono') || {}).value || '').trim();
    var correo = ((document.getElementById('perfilInputCorreo') || {}).value || '').trim();
    var priv = !!(document.getElementById('perfilInputPrivacidad') || {}).checked;

    if (nombre.length < 2) {
      toast('El nombre debe tener al menos 2 caracteres.', 'warning');
      return false;
    }
    if (!correo || correo.indexOf('@') < 0) {
      toast('Escribe un correo válido.', 'warning');
      return false;
    }

    var token = csrf();
    var fd = new FormData();
    fd.append('nombre', nombre);
    fd.append('telefono', telefono);
    fd.append('correo', correo);
    fd.append('acepta_privacidad', priv ? '1' : '0');
    if (token) {
      fd.append('_csrf', token);
      fd.append('_token', token);
    }

    var btn = document.querySelector('#formEditarPerfil button[type="submit"]');
    if (btn) btn.disabled = true;

    try {
      var res = await fetch('/api/tutor/perfil', {
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
        toast(data.message || 'No se pudo guardar el perfil.', 'danger');
        return false;
      }
      var user = (data.data && data.data.user) || data.user || {
        nombre: nombre,
        telefono: telefono,
        correo: correo,
        acepta_privacidad: priv ? 1 : 0,
      };
      var st = ensureState();
      st.tutorActual.nombre = user.nombre || nombre;
      st.tutorActual.telefono = user.telefono || telefono;
      st.tutorActual.correo = user.correo || correo;
      st.tutorActual.aceptaPrivacidad =
        user.acepta_privacidad != null ? !!Number(user.acepta_privacidad) : priv;
      if (global.RUPE_INIT) {
        global.RUPE_INIT.user = Object.assign({}, global.RUPE_INIT.user || {}, user);
      }

      var modalEl = document.getElementById('modalEditarPerfil');
      if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        try {
          bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        } catch (_) {
          modalEl.classList.remove('show');
          modalEl.style.display = 'none';
        }
      } else if (global.modalEditarPerfilBs) {
        global.modalEditarPerfilBs.hide();
      }

      renderizarPerfilTutor();
      toast('Perfil actualizado correctamente.', 'success');
      return true;
    } catch (err) {
      console.error(err);
      var st2 = ensureState();
      st2.tutorActual.nombre = nombre;
      st2.tutorActual.telefono = telefono;
      st2.tutorActual.correo = correo;
      st2.tutorActual.aceptaPrivacidad = priv;
      renderizarPerfilTutor();
      toast('Guardado local. Verifica conexión para sincronizar.', 'warning');
      return false;
    } finally {
      if (btn) btn.disabled = false;
    }
  }
  global.guardarPerfilTutor = guardarPerfilTutor;

  function bindHandlers() {
    var form = document.getElementById('formEditarPerfil');
    if (form && !form._rupePerfilBound) {
      form._rupePerfilBound = true;
      form.addEventListener('submit', function (ev) {
        guardarPerfilTutor(ev);
      });
    }
    var sel = document.getElementById('perfilSelectMascota');
    if (sel && !sel._rupePerfilBound) {
      sel._rupePerfilBound = true;
      sel.addEventListener('change', function () {
        var folio = sel.value;
        ensureState().folioSeleccionado = folio || '';
        // Solo actualiza preview; el listado completo permanece
        pintarPreviewPerfil(folio ? findByFolio(folio) : null);
      });
    }
    var btnVer = document.getElementById('btnPerfilVerFicha');
    if (btnVer && !btnVer._bound) {
      btnVer._bound = true;
      btnVer.addEventListener('click', function () {
        verFichaDesdePerfil();
      });
    }
    var btnPdf = document.getElementById('btnPerfilDescargarPdf');
    if (btnPdf && !btnPdf._bound) {
      btnPdf._bound = true;
      btnPdf.addEventListener('click', function () {
        var sel = document.getElementById('perfilSelectMascota');
        var folio = sel ? String(sel.value || '') : '';
        descargarPdfDesdePerfil(folio);
      });
    }

    // Clics en la tabla Mis mascotas registradas (Ver ficha / Descargar PDF)
    var tbody = document.getElementById('tbodyPerfilMascotas');
    if (tbody && !tbody._rupePerfilClickBound) {
      tbody._rupePerfilClickBound = true;
      tbody.addEventListener('click', function (ev) {
        var btnPdfRow = ev.target.closest('[data-perfil-pdf]');
        var btnVerRow = ev.target.closest('[data-perfil-ver]');
        if (btnPdfRow) {
          ev.preventDefault();
          ev.stopPropagation();
          var folio = btnPdfRow.getAttribute('data-perfil-pdf') || '';
          var sel = document.getElementById('perfilSelectMascota');
          if (sel && folio) {
            try { sel.value = folio; } catch (_) {}
          }
          ensureState().folioSeleccionado = folio;
          descargarPdfDesdePerfil(folio);
          return;
        }
        if (btnVerRow) {
          ev.preventDefault();
          ev.stopPropagation();
          var folioV = btnVerRow.getAttribute('data-perfil-ver') || '';
          var selV = document.getElementById('perfilSelectMascota');
          if (selV && folioV) {
            try { selV.value = folioV; } catch (_) {}
          }
          ensureState().folioSeleccionado = folioV;
          verFichaDesdePerfil(folioV);
        }
      });
    }
  }

  async function bind() {
    loadTutorFromInit();
    bindHandlers();
    try {
      // Semilla PHP inmediata si existe
      if (global.__RUPE_MASCOTAS__ && Array.isArray(global.__RUPE_MASCOTAS__) && global.__RUPE_MASCOTAS__.length) {
        var st0 = ensureState();
        st0.registros = global.__RUPE_MASCOTAS__.map(normalizarMascota).filter(Boolean);
      }
      renderizarPerfilTutor();
    } catch (e) {
      console.warn('[RUPE PERFIL] render inicial', e);
    }
    try {
      await cargarMascotasDesdeApi();
      await cargarPerfilDesdeApi();
      renderizarPerfilTutor();
    } catch (err) {
      console.warn('[RUPE PERFIL] reload API', err);
      try { renderizarPerfilTutor(); } catch (_) {}
    }
  }
  global.bindPerfilTutor = bind;

  document.addEventListener('click', function (e) {
    if (
      e.target.closest('.nav-modulo[data-modulo="perfil"]') ||
      e.target.closest('[onclick*="mostrarModuloPerfil"]') ||
      e.target.closest('a[href="/tutor/perfil"]') ||
      e.target.closest('a[href="/tutor/miperfil"]')
    ) {
      setTimeout(function () {
        bind();
      }, 200);
    }
    // PDF / Ver ficha desde tabla Mis mascotas (backup global)
    var pdfBtn = e.target.closest('[data-perfil-pdf]');
    if (pdfBtn) {
      e.preventDefault();
      var folio = pdfBtn.getAttribute('data-perfil-pdf') || '';
      if (folio && typeof global.descargarPdfDesdePerfil === 'function') {
        global.descargarPdfDesdePerfil(folio);
      }
      return;
    }
    var verBtn = e.target.closest('[data-perfil-ver]');
    if (verBtn) {
      e.preventDefault();
      var folioV = verBtn.getAttribute('data-perfil-ver') || '';
      if (folioV && typeof global.verFichaDesdePerfil === 'function') {
        global.verFichaDesdePerfil(folioV);
      }
    }
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      setTimeout(bind, 100);
    });
  } else {
    setTimeout(bind, 100);
  }
  global.addEventListener('load', function () {
    setTimeout(bind, 50);
  });
})(typeof window !== 'undefined' ? window : globalThis);
