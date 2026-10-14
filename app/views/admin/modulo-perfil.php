<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RUPE - Mi Perfil</title>
  
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root {
      --color-primary: #6B1D2F;        /* Guinda institucional */
      --color-primary-dark: #4A1320;
      --color-bg: #F8F9FA;
    }

    body {
      background-color: var(--color-bg);
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    .top-navbar {
      background-color: var(--color-primary);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .nav-pills .nav-link {
      color: rgba(255, 255, 255, 0.85);
      font-weight: 500;
      border-radius: 8px;
      padding: 8px 12px;
      font-size: 0.85rem;
      transition: all 0.2s ease;
      cursor: pointer;
    }

    .nav-pills .nav-link:hover {
      color: #fff;
      background-color: rgba(255, 255, 255, 0.15);
    }

    .nav-pills .nav-link.active {
      color: var(--color-primary);
      background-color: #fff;
      font-weight: 600;
    }

    .modulo-seccion {
      display: none;
    }

    .modulo-seccion.activo {
      display: block;
      animation: fadeIn 0.25s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(6px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .card {
      border: none;
      border-radius: 12px;
    }

    .table th {
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.5px;
      color: #6c757d;
    }

    .pdf-preview {
      background: #fdfdfd;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 25px;
      box-shadow: inset 0 0 10px rgba(0,0,0,0.02);
    }

    /* === NUEVOS ESTILOS PARA CATÁLOGOS MEJORADOS === */
    .catalogo-search {
      max-width: 320px;
    }

    .catalogo-counter {
      font-size: 0.85rem;
      color: #6c757d;
    }

    .badge-activo { background-color: #198754; }
    .badge-inactivo { background-color: #6c757d; }

    /* Tabs internos dentro de Reportes */
    .sub-tab-nav .nav-link {
      color: #6c757d;
      font-weight: 500;
      border: none;
      border-bottom: 3px solid transparent;
      border-radius: 0;
      padding: 10px 20px;
      font-size: 0.9rem;
      transition: all 0.2s;
    }
    .sub-tab-nav .nav-link:hover {
      color: var(--color-primary);
      border-bottom-color: rgba(107,29,47,0.3);
    }
    .sub-tab-nav .nav-link.active {
      color: var(--color-primary);
      border-bottom-color: var(--color-primary);
      font-weight: 600;
      background: transparent;
    }

    .sub-tab-content {
      display: none;
    }
    .sub-tab-content.activo {
      display: block;
      animation: fadeIn 0.2s ease-in-out;
    }

    /* Selector de catálogo */
    .catalogo-selector {
      border: 1px solid #dee2e6;
      border-radius: 10px;
      padding: 12px 16px;
      background: #fff;
      transition: border-color 0.2s;
    }
    .catalogo-selector:hover {
      border-color: var(--color-primary);
    }
    .catalogo-selector label {
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #6c757d;
      margin-bottom: 4px;
    }

    /* Toast de notificación */
    .toast-container-custom {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 9999;
    }
    .toast-custom {
      min-width: 300px;
      animation: slideInRight 0.3s ease-out;
    }
    @keyframes slideInRight {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    /* Highlight de fila */
    .table-hover tr.highlight-row {
      animation: highlightPulse 1.5s ease-out;
    }
    @keyframes highlightPulse {
      0% { background-color: rgba(107,29,47,0.15); }
      100% { background-color: transparent; }
    }
    /* === MÓDULO 3: PANEL DE REPORTES === */
    .kpi-card {
      border-radius: 12px;
      background: #fff;
      padding: 16px 18px;
      border-left: 4px solid var(--color-primary);
      box-shadow: 0 2px 8px rgba(0,0,0,.05);
      height: 100%;
    }
    .kpi-card .kpi-label {
      font-size: .72rem;
      text-transform: uppercase;
      letter-spacing: .6px;
      color: #6c757d;
      font-weight: 700;
    }
    .kpi-card .kpi-value { font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
    .kpi-card.kpi-danger { border-left-color: #dc3545; }
    .kpi-card.kpi-success { border-left-color: #198754; }
    .kpi-card.kpi-warning { border-left-color: #ffc107; }
    .kpi-card.kpi-info { border-left-color: #0dcaf0; }

    .barra-dist {
      height: 10px;
      border-radius: 6px;
      background: #e9ecef;
      overflow: hidden;
    }
    .barra-dist span {
      display: block;
      height: 100%;
      background: var(--color-primary);
      border-radius: 6px;
      transition: width .5s ease;
    }

    /* Fotografías miniatura de mascotas (tabla de expedientes, validación, coincidencias) */
    .foto-miniatura {
      width: 44px;
      height: 44px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #eceff1;
      box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }
    .foto-miniatura-lg {
      width: 72px;
      height: 72px;
      object-fit: cover;
      border-radius: 10px;
      border: 2px solid #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }

    /* Semáforo de antigüedad / prioridad */
    .semaforo {
      display: inline-block;
      width: 10px; height: 10px;
      border-radius: 50%;
      margin-right: 6px;
    }
    .semaforo-verde { background: #198754; }
    .semaforo-amarillo { background: #ffc107; }
    .semaforo-rojo { background: #dc3545; }

    /* Tarjetas de la bandeja de validación */
    .validacion-card {
      border: 1px solid #dee2e6;
      border-left: 4px solid #ffc107;
      border-radius: 10px;
      background: #fff;
      padding: 14px 16px;
      transition: box-shadow .2s;
    }
    .validacion-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); }

    /* Coincidencias */
    .match-card {
      border: 1px solid #dee2e6;
      border-radius: 10px;
      background: #fff;
      padding: 14px 16px;
    }
    .match-score {
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--color-primary);
    }

    /* Bitácora / línea de tiempo */
    .timeline { list-style: none; padding-left: 0; margin: 0; }
    .timeline li {
      position: relative;
      padding-left: 26px;
      padding-bottom: 16px;
      border-left: 2px solid #e9ecef;
      margin-left: 6px;
    }
    .timeline li:last-child { border-left-color: transparent; padding-bottom: 0; }
    .timeline li::before {
      content: '';
      position: absolute;
      left: -7px; top: 2px;
      width: 12px; height: 12px;
      border-radius: 50%;
      background: var(--color-primary);
      border: 2px solid #fff;
      box-shadow: 0 0 0 2px #e9ecef;
    }
  
    /* RUPE_ADMIN_NAV_CLICK_FIX */
    .top-navbar { z-index: 1040; position: sticky; top: 0; }
    .nav-pills .nav-link {
      cursor: pointer !important;
      pointer-events: auto !important;
      min-height: 40px;
      display: inline-flex !important;
      align-items: center;
      white-space: nowrap;
      -webkit-tap-highlight-color: rgba(255,255,255,0.2);
    }
    .nav-pills .nav-link:active {
      transform: scale(0.98);
    }
    .navbar-collapse { z-index: 1045; }
    .dropdown-menu { z-index: 1080 !important; }
    #navbarAdminContent .nav-item { list-style: none; }

  </style>
  <link rel="stylesheet" href="/assets/css/rupe-responsive-nav.css?v=20260926adminnav1">
</head>
<body>

  <!-- NAVBAR SUPERIOR -->
  <nav class="navbar navbar-expand-xl navbar-dark top-navbar sticky-top">
    <div class="container-fluid px-4">

      <a class="navbar-brand d-flex align-items-center fw-bold me-3 text-white text-decoration-none" href="/admin/reportes" style="gap:0.5rem;">
        <span class="d-inline-flex align-items-center justify-content-center bg-white rounded-2 px-2 py-1" style="height:40px;">
          <img src="/assets/img/logo-unadm.png" alt="UnADM" style="height:28px;width:auto;object-fit:contain;" onerror="this.style.display='none'">
        </span>
        <span class="d-none d-sm-inline">RUPE Admin</span>
      </a>

      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdminContent" aria-controls="navbarAdminContent" aria-expanded="false" aria-label="Abrir menú">
        <span class="navbar-toggler-icon"></span>
      </button>

      
      <!-- Botón Opciones siempre visible -->
      <div class="dropdown admin-user-dropdown ms-1 ms-xl-2">
        <button class="btn btn-outline-light dropdown-toggle btn-sm py-2 px-3 rounded-pill" type="button" id="btnAdminCentral" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false" aria-label="Opciones de cuenta">
          <i class="bi bi-person-circle me-1"></i>
          <span class="admin-user-label" id="adminUserName">Administrador</span>
          <span class="label-opciones">Opciones</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow" style="z-index:2100;">
          <li>
            <a class="dropdown-item" href="/admin/perfil" id="btnAdminPerfil"
               onclick="window.location.href='/admin/perfil'; return true;">
              <i class="bi bi-person-circle me-2"></i>Mi Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="/admin/configuracion" id="btnAdminConfig"
               onclick="window.location.href='/admin/configuracion'; return true;">
              <i class="bi bi-gear me-2"></i>Configuración
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="/admin/ayuda" id="btnAdminAyuda"
               onclick="window.location.href='/admin/ayuda'; return true;">
              <i class="bi bi-question-circle me-2"></i>Ayuda
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-danger" href="#" id="btnAdminLogout"
               onclick="event.preventDefault(); if(window.confirmarCerrarSesionAdmin){confirmarCerrarSesionAdmin(event);}else if(window.rupeAdminLogout){rupeAdminLogout(event);}else{window.location.href='/auth/logout';} return false;">
              <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
            </a>
          </li>
        </ul>
      </div>

      <div class="collapse navbar-collapse mt-2 mt-xl-0" id="navbarAdminContent">
        <ul class="nav nav-pills me-auto d-flex flex-column flex-xl-row gap-1" id="pills-tab">
          <li class="nav-item">
            <a class="nav-link nav-modulo" href="/admin/reportes">
              <i class="bi bi-bar-chart-line-fill me-1"></i>1. Reportes y Análisis
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-modulo" href="/admin/usuarios">
              <i class="bi bi-people-fill me-1"></i>2. Usuarios
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-modulo" href="/admin/razas">
              <i class="bi bi-tags-fill me-1"></i>3. Cat. Razas
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-modulo" href="/admin/alcaldias">
              <i class="bi bi-geo-alt-fill me-1"></i>4. Cat. Alcaldías
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-modulo" href="/admin/auditoria">
              <i class="bi bi-journal-check me-1"></i>5. Auditoría
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-modulo" href="/admin/calculos">
              <i class="bi bi-percent me-1"></i>6. Porcentajes
            </a>
          </li>
        </ul>
      </div>

    </div>
  </nav>

  <!-- CONTENEDOR PRINCIPAL -->
  <main class="container-fluid px-4 py-4">

    <section id="modulo-perfil" class="modulo-seccion activo">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="fw-bold text-dark m-0">Mi Perfil</h3>
          <p class="text-muted small m-0">Tus datos de cuenta y tu actividad dentro del panel RUPE.</p>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-4">
          <div class="card shadow-sm h-100">
            <div class="card-body text-center p-4">
              <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                   id="perfilAdminAvatar"
                   style="width:84px;height:84px;border-radius:50%;background:#E8A33D;color:#4A1521;font-weight:800;font-size:1.6rem;">MR</div>
              <h5 class="fw-bold mb-1" id="perfilAdminNombre">Cargando…</h5>
              <span class="badge bg-primary mb-3" id="perfilAdminRol">Administrador</span>
              <div class="text-start small border-top pt-3 mt-1">
                <p class="mb-2"><i class="bi bi-envelope me-2 text-muted"></i><span id="perfilAdminCorreo">—</span></p>
                <p class="mb-2"><i class="bi bi-telephone me-2 text-muted"></i><span id="perfilAdminTelefono">—</span></p>
                <p class="mb-0"><i class="bi bi-calendar-check me-2 text-muted"></i>En el equipo desde <span id="perfilAdminFecha">—</span></p>
              </div>
              <button class="btn btn-sm w-100 mt-3 text-white" style="background-color:#6B1D2F;" onclick="abrirModalEditarPerfilAdmin()">
                <i class="bi bi-pencil me-1"></i> Editar mis datos
              </button>
            </div>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="row g-3">
            <div class="col-md-4">
              <div class="card shadow-sm h-100 text-center p-3">
                <div class="fs-3 fw-bold" style="color:#6B1D2F;" id="perfilAdminStatValidados">0</div>
                <div class="small text-muted">Expedientes validados</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card shadow-sm h-100 text-center p-3">
                <div class="fs-3 fw-bold text-success" id="perfilAdminStatResueltos">0</div>
                <div class="small text-muted">Casos reunificados</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card shadow-sm h-100 text-center p-3">
                <div class="fs-3 fw-bold text-warning" id="perfilAdminStatPendientes">0</div>
                <div class="small text-muted">Pendientes en bandeja</div>
              </div>
            </div>
            <div class="col-12">
              <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold small">
                  <i class="bi bi-clock-history me-1"></i> Mi actividad reciente
                </div>
                <div class="card-body p-0">
                  <ul class="list-group list-group-flush" id="perfilAdminActividad"></ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- MODAL EDITAR PERFIL DEL ADMINISTRADOR -->
  <div class="modal fade" id="modalEditarPerfilAdmin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header text-white" style="background-color:#6B1D2F;">
          <h5 class="modal-title fw-bold"><i class="bi bi-person-circle me-2"></i>Editar mis datos</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formEditarPerfilAdmin" onsubmit="guardarPerfilAdmin(event)" novalidate>
            <div class="mb-3">
              <label class="form-label fw-semibold small">Nombre completo *</label>
              <div class="input-group">
                <input type="text" id="perfilAdminInputNombre" class="form-control" required maxlength="80"
                       placeholder="Nombre y apellidos" autocomplete="name">
                <span class="input-group-text d-none text-success" id="okPerfilNombre"><i class="bi bi-check-circle-fill"></i></span>
              </div>
              <div class="form-text">Nombre y apellidos (mín. 2 palabras, solo letras).</div>
              <div class="invalid-feedback">Incluye nombre y al menos un apellido (sin números).</div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold small">Teléfono *</label>
              <div class="input-group">
                <input type="tel" id="perfilAdminInputTelefono" class="form-control" required maxlength="10"
                       placeholder="5512345678" inputmode="numeric" pattern="[0-9]{10}" autocomplete="tel">
                <span class="input-group-text d-none text-success" id="okPerfilTelefono"><i class="bi bi-check-circle-fill"></i></span>
              </div>
              <div class="form-text">Exactamente 10 dígitos.</div>
              <div class="invalid-feedback">El teléfono debe tener 10 dígitos.</div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold small">Correo electrónico *</label>
              <div class="input-group">
                <input type="email" id="perfilAdminInputCorreo" class="form-control" required maxlength="80"
                       placeholder="admin@ejemplo.com" autocomplete="email">
                <span class="input-group-text d-none text-success" id="okPerfilCorreo"><i class="bi bi-check-circle-fill"></i></span>
              </div>
              <div class="form-text">Correo válido.</div>
              <div class="invalid-feedback">Correo electrónico no válido.</div>
            </div>
            <div class="text-end mt-3">
              <button type="button" class="btn btn-secondary me-1" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container-custom" id="toastContainer"></div>

  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const FOTO_PLACEHOLDER_ADMIN = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='140'%3E%3Crect fill='%23f0f0f0' width='200' height='140'/%3E%3Ctext fill='%23999' x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-size='12'%3ESin foto%3C/text%3E%3C/svg%3E";
        let adminActual = {
      nombre: '',
      correo: '',
      telefono: '',
      rol: 'Administrador',
      fechaRegistro: ''
    };
    window.adminActual = adminActual;

    async function ensureCsrfAdmin() {
      if (window.__CSRF__) return window.__CSRF__;
      if (window.RUPE_ADMIN_INIT && window.RUPE_ADMIN_INIT.csrf) {
        window.__CSRF__ = window.RUPE_ADMIN_INIT.csrf;
        return window.__CSRF__;
      }
      try {
        const r = await fetch('/api/csrf', { credentials: 'same-origin' });
        const d = await r.json();
        window.__CSRF__ = (d.data && d.data.csrf) || d.csrf || '';
      } catch (_) {}
      return window.__CSRF__ || '';
    }

    async function cargarPerfilAdminDesdeBD() {
      // Prefer PHP inject
      const init = window.RUPE_ADMIN_INIT || {};
      const u0 = init.usuario || init.user || null;
      if (u0) {
        adminActual.nombre = u0.nombre || '';
        adminActual.correo = u0.correo || '';
        adminActual.telefono = u0.telefono || '';
        adminActual.rol = (u0.rol === 'admin' || u0.rol === 'Administrador') ? 'Administrador' : (u0.rol || 'Administrador');
        adminActual.fechaRegistro = u0.created_at || u0.fechaRegistro || '';
        window.adminActual = adminActual;
      }
      try {
        const res = await fetch('/api/admin/perfil', {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json().catch(() => ({}));
        const payload = data.data || data;
        const u = payload.usuario || payload;
        if (u && (u.nombre || u.correo)) {
          adminActual.nombre = u.nombre || adminActual.nombre;
          adminActual.correo = u.correo || adminActual.correo;
          adminActual.telefono = u.telefono || adminActual.telefono || '';
          adminActual.rol = (u.rol === 'admin') ? 'Administrador' : (u.rol || adminActual.rol);
          adminActual.fechaRegistro = u.created_at || adminActual.fechaRegistro;
          window.adminActual = adminActual;
        }
        if (payload.stats) {
          const s = payload.stats;
          const elV = document.getElementById('perfilAdminStatValidados');
          const elR = document.getElementById('perfilAdminStatResueltos');
          const elP = document.getElementById('perfilAdminStatPendientes');
          if (elV) elV.textContent = String(s.validados != null ? s.validados : 0);
          if (elR) elR.textContent = String(s.resueltos != null ? s.resueltos : 0);
          if (elP) elP.textContent = String(s.pendientes != null ? s.pendientes : 0);
        }
        if (Array.isArray(payload.actividad)) {
          window.__RUPE_ADMIN_ACTIVIDAD__ = payload.actividad;
        }
      } catch (err) {
        console.warn('[RUPE Admin] perfil API', err);
      }
      renderizarPerfilAdmin();
    }

    // load reportes for filters if needed
    async function filtrarStatsPerfilAdmin() {
      try {
        const res = await fetch('/api/admin/reportes', {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json().catch(() => ({}));
        const d = data.data || data;
        const list = Array.isArray(d.mascotas) ? d.mascotas : (Array.isArray(d) ? d : []);
        window.reportesRUPE = list;
        let validados = 0, resueltos = 0, pendientes = 0;
        list.forEach(function (r) {
          const e = String(r.estatus || '').toLowerCase();
          if (/localiz|resuelt|reunif/.test(e)) resueltos++;
          else if (/pendiente/.test(e)) pendientes++;
          else validados++;
        });
        const elV = document.getElementById('perfilAdminStatValidados');
        const elR = document.getElementById('perfilAdminStatResueltos');
        const elP = document.getElementById('perfilAdminStatPendientes');
        if (elV) elV.textContent = String(validados);
        if (elR) elR.textContent = String(resueltos);
        if (elP) elP.textContent = String(pendientes);
      } catch (_) {}
    }
    let modalEditarPerfilAdminBs = null;
    function inicialesAdmin(nombre) {
      return (nombre || '').split(/\s+/).filter(Boolean).slice(0, 2).map(p => p[0].toUpperCase()).join('') || 'AD';
    }
    function fechaLarga(f) {
      if (!f) return '—';
      try {
        const d = new Date(f);
        if (!isNaN(d.getTime())) {
          return d.toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
        }
      } catch (_) {}
      return String(f);
    }
    function renderizarPerfilAdmin() {
      const avatar = document.getElementById('perfilAdminAvatar');
      if (avatar) avatar.textContent = inicialesAdmin(adminActual.nombre);
      const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '—'; };
      set('perfilAdminNombre', adminActual.nombre);
      set('perfilAdminRol', adminActual.rol);
      set('perfilAdminCorreo', adminActual.correo);
      set('perfilAdminTelefono', adminActual.telefono);
      set('perfilAdminFecha', fechaLarga(adminActual.fechaRegistro));
      const btnAdmin = document.getElementById('btnAdminCentral');
      if (btnAdmin && adminActual.nombre) {
        const first = adminActual.nombre.split(/\s+/)[0];
        btnAdmin.innerHTML = '<i class="bi bi-person-circle me-1"></i> ' + first;
      }
      // Actividad
      const cont = document.getElementById('perfilAdminActividad');
      if (cont) {
        const eventos = window.__RUPE_ADMIN_ACTIVIDAD__ || [];
        if (eventos.length) {
          cont.innerHTML = eventos.map(function (ev) {
            const fecha = ev.created_at || ev.fecha || '';
            const accion = ev.accion || '';
            const det = ev.detalle_texto || ev.detalle || '';
            return '<li class="list-group-item small"><strong>' + fecha + '</strong> · ' + accion +
              (det ? '<br><span class="text-muted">' + String(det).replace(/</g,'&lt;') + '</span>' : '') + '</li>';
          }).join('');
        } else {
          cont.innerHTML = '<li class="list-group-item small text-muted">Sin actividad registrada todavía.</li>';
        }
      }
    }
    function abrirModalEditarPerfilAdmin() {
      document.getElementById('perfilAdminInputNombre').value = adminActual.nombre || '';
      document.getElementById('perfilAdminInputTelefono').value = String(adminActual.telefono || '').replace(/\D/g, '').slice(0, 10);
      document.getElementById('perfilAdminInputCorreo').value = adminActual.correo || '';
      const modalEl = document.getElementById('modalEditarPerfilAdmin');
      if (!modalEditarPerfilAdminBs && modalEl && window.bootstrap) {
        modalEditarPerfilAdminBs = bootstrap.Modal.getOrCreateInstance(modalEl);
      }
      if (modalEditarPerfilAdminBs) modalEditarPerfilAdminBs.show();
      setTimeout(function () {
        if (typeof window.rupeValidarPerfilAdmin === 'function') window.rupeValidarPerfilAdmin(false);
      }, 50);
    }
    async function guardarPerfilAdmin(e) {
      e.preventDefault();
      if (typeof window.rupeValidarPerfilAdmin === 'function' && !window.rupeValidarPerfilAdmin(true)) {
        mostrarToast('Completa nombre con apellidos, teléfono de 10 dígitos y correo válido.', 'warning');
        return;
      }
      const nombre = (document.getElementById('perfilAdminInputNombre').value || '').trim().replace(/\s+/g, ' ');
      const telefono = String(document.getElementById('perfilAdminInputTelefono').value || '').replace(/\D/g, '');
      const correo = (document.getElementById('perfilAdminInputCorreo').value || '').trim();
      const reNom = /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+(?:\s+[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+){1,4}$/;
      if (!reNom.test(nombre)) {
        mostrarToast('Nombre completo: incluye nombre y apellidos (solo letras).', 'warning');
        return;
      }
      if (!/^\d{10}$/.test(telefono)) {
        mostrarToast('Teléfono: exactamente 10 dígitos.', 'warning');
        return;
      }
      if (!correo || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        mostrarToast('Correo no válido.', 'warning');
        return;
      }
      const btn = e.target.querySelector('button[type="submit"]');
      if (btn) { btn.disabled = true; btn.dataset._old = btn.innerHTML; btn.innerHTML = 'Guardando…'; }
      try {
        const csrf = await ensureCsrfAdmin();
        const res = await fetch('/api/admin/perfil', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrf || ''
          },
          body: JSON.stringify({ nombre, telefono, correo, _csrf: csrf || '' })
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || data.success === false) {
          let msg = data.message || 'No se pudo guardar el perfil.';
          if (data.errors) {
            try { msg = Object.values(data.errors).flat().join(' '); } catch (_) {}
          }
          mostrarToast(msg, 'danger');
          return;
        }
        const u = (data.data && data.data.usuario) || data.usuario || {};
        adminActual.nombre = u.nombre || nombre;
        adminActual.correo = u.correo || correo;
        adminActual.telefono = u.telefono || telefono;
        window.adminActual = adminActual;
        if (modalEditarPerfilAdminBs) modalEditarPerfilAdminBs.hide();
        renderizarPerfilAdmin();
        mostrarToast(data.message || 'Perfil actualizado en la base de datos.', 'success');
      } catch (err) {
        console.warn(err);
        mostrarToast('Error de red al guardar el perfil.', 'danger');
      } finally {
        if (btn) { btn.disabled = false; if (btn.dataset._old) btn.innerHTML = btn.dataset._old; }
      }
    }
    window.guardarPerfilAdmin = guardarPerfilAdmin;
    window.abrirModalEditarPerfilAdmin = abrirModalEditarPerfilAdmin;
    window.renderizarPerfilAdmin = renderizarPerfilAdmin;
    document.addEventListener('DOMContentLoaded', function () {
      cargarPerfilAdminDesdeBD().then(function () { filtrarStatsPerfilAdmin(); });
    });
    function mostrarToast(mensaje, tipo = 'success') {
      const container = document.getElementById('toastContainer');
      const iconMap = {
        success: 'bi-check-circle-fill',
        danger: 'bi-x-circle-fill',
        info: 'bi-info-circle-fill',
        warning: 'bi-exclamation-triangle-fill'
      };
      const bgMap = {
        success: 'bg-success',
        danger: 'bg-danger',
        info: 'bg-info',
        warning: 'bg-warning'
      };
      const toast = document.createElement('div');
      toast.className = `toast-custom alert ${bgMap[tipo]} text-white d-flex align-items-center shadow-lg`;
      toast.innerHTML = `<i class="bi ${iconMap[tipo]} me-2 fs-5"></i><span>${mensaje}</span>`;
      container.appendChild(toast);
      setTimeout(() => {
        toast.style.transition = 'opacity 0.3s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    document.addEventListener('DOMContentLoaded', () => {
      renderizarPerfilAdmin();
    });
  </script>


<script>
// RUPE Admin — navegación de menú + logout
function navegarAModulo(nombreModulo) {
  const mapa = {
    reportes: '/admin/reportes', usuarios: '/admin/usuarios',
    razas: '/admin/razas', alcaldias: '/admin/alcaldias',
    auditoria: '/admin/auditoria', calculos: '/admin/calculos',
    perfil: '/admin/perfil', configuracion: '/admin/configuracion',
    ayuda: '/admin/ayuda'
  };
  if (mapa[nombreModulo]) window.location.href = mapa[nombreModulo];
}
(function () {
  // Marcar activo según URL
  const path = window.location.pathname.replace(/\/$/, '') || '/admin';
  document.querySelectorAll('.nav-modulo, .dropdown-item').forEach((a) => {
    const href = (a.getAttribute('href') || '').replace(/\/$/, '');
    if (!href || href === '#') return;
    if (path === href || path.endsWith(href) || (href === '/admin/reportes' && (path === '/admin' || path === '/admin/'))) {
      if (a.classList.contains('nav-modulo')) {
        document.querySelectorAll('.nav-modulo').forEach(n => n.classList.remove('active'));
        a.classList.add('active');
      }
    }
  });

  // Logout por API (POST) y redirigir a home
  async function adminLogout(e) {
    if (window.confirmarCerrarSesionAdmin) {
      return window.confirmarCerrarSesionAdmin(e);
    }
    if (e) { e.preventDefault(); e.stopPropagation(); }
    if (!confirm('¿Cerrar sesión de administrador?')) return;
    try {
      await fetch('/api/auth/logout', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: '{}'
      });
    } catch (_) {}
    window.location.href = '/auth/logout';
  }

  document.querySelectorAll('#btnAdminLogout').forEach((el) => {
    el.addEventListener('click', adminLogout);
  });
  // Delegación por si el dropdown se re-renderiza
  document.addEventListener('click', function (ev) {
    const t = ev.target.closest('#btnAdminLogout, a.dropdown-item.text-danger');
    if (!t) return;
    if (t.id === 'btnAdminLogout' || (t.classList.contains('text-danger') && /cerrar sesi/i.test(t.textContent || ''))) {
      adminLogout(ev);
    }
  });
})();
</script>


<script>
  window.__CSRF__ = <?= json_encode(function_exists('csrf_token') ? csrf_token() : '', JSON_UNESCAPED_UNICODE) ?>;
  window.__RUPE_USER__ = <?= json_encode([
    'id' => $_SESSION['user_id'] ?? null,
    'nombre' => $_SESSION['user_name'] ?? '',
    'rol' => $_SESSION['user_role'] ?? '',
    'email' => $_SESSION['user_email'] ?? '',
  ], JSON_UNESCAPED_UNICODE) ?>;
  document.addEventListener('DOMContentLoaded', function () {
    var u = window.__RUPE_USER__ || {};
    if (u.nombre) {
      var n = document.getElementById('tutorUserName');
      if (n) n.textContent = u.nombre;
      var h = document.getElementById('tutorUserHeader');
      if (h) h.textContent = u.nombre;
      var a = document.getElementById('adminUserName');
      if (a) a.textContent = u.nombre;
    }
  });
</script>

<script>
(function () {
  function rupeApplyUserName(nombre, rol) {
    if (!nombre || !String(nombre).trim()) return;
    nombre = String(nombre).trim();
    var elT = document.getElementById('tutorUserName');
    if (elT) elT.textContent = nombre;
    var elH = document.getElementById('tutorUserHeader');
    if (elH) elH.textContent = nombre;
    var elA = document.getElementById('adminUserName');
    if (elA) elA.textContent = nombre;
    // Fallback: cualquier texto visible del botón de opciones
    document.querySelectorAll('#btnUserDropdown .d-none.d-md-inline, #btnUserDropdown span:not(.avatar-mini)').forEach(function (n) {
      if (n.id === 'tutorUserName' || !n.id) n.textContent = nombre;
    });
    document.querySelectorAll('#btnAdminCentral #adminUserName').forEach(function (n) {
      n.textContent = nombre;
    });
    var header = document.querySelector('.user-dropdown .dropdown-header');
    if (header) {
      var span = header.querySelector('#tutorUserHeader');
      if (span) span.textContent = nombre;
      else header.innerHTML = nombre + ' · Cuenta gratuita';
    }
    // Avatar iniciales
    var ini = nombre.split(/\s+/).map(function (p) { return p.charAt(0); }).join('').slice(0, 2).toUpperCase();
    document.querySelectorAll('.user-dropdown .avatar-mini, #btnUserDropdown .avatar-mini').forEach(function (a) {
      a.textContent = ini || 'U';
    });
    window.__RUPE_USER__ = window.__RUPE_USER__ || {};
    window.__RUPE_USER__.nombre = nombre;
    if (rol) window.__RUPE_USER__.rol = rol;
  }
  window.rupeApplyUserName = rupeApplyUserName;

  function fromSession() {
    var u = window.__RUPE_USER__ || {};
    if (u.nombre) rupeApplyUserName(u.nombre, u.rol);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fromSession);
  } else {
    fromSession();
  }
  // Re-aplicar por si scripts del panel pisan el nombre
  window.addEventListener('load', function () {
    setTimeout(fromSession, 200);
    setTimeout(fromSession, 800);
    setTimeout(fromSession, 1500);
  });
})();
</script>

<script src="/assets/js/admin-app.js?v=20260919r" defer></script>


<script>
/* RUPE_ADMIN_DB_BOOTSTRAP — sincroniza módulos admin con BD */
(function () {
  window.addEventListener('load', function () {
    setTimeout(function () {
      if (!window.RUPEAdmin) return;
      var path = (location.pathname || '').replace(/\/$/, '');
      var A = window.RUPEAdmin;
      var tasks = [];
      if (A.loadAdminSesion) tasks.push(A.loadAdminSesion());
      if (path.includes('usuarios') && A.loadUsuarios) tasks.push(A.loadUsuarios());
      else if (path.includes('razas') && A.loadRazas) tasks.push(A.loadRazas());
      else if (path.includes('alcaldias') && A.loadAlcaldias) tasks.push(A.loadAlcaldias());
      else if (path.includes('auditoria') && A.loadAuditoria) tasks.push(A.loadAuditoria());
      else if (path.includes('configuracion') && A.loadConfig) tasks.push(A.loadConfig());
      else if (path.includes('ayuda') && A.loadAyuda) tasks.push(A.loadAyuda());
      else if (path.includes('calculos') && A.loadReportes) tasks.push(A.loadReportes());
      else if (A.loadReportes) tasks.push(A.loadReportes());
      Promise.all(tasks.map(function (p) { return Promise.resolve(p).catch(function () {}); })).then(function () {
        if (typeof window.rupeAdminRefreshCalculos === 'function') {
          try { window.rupeAdminRefreshCalculos(); } catch (e) {}
        }
      });
    }, 500);
  });
})();
</script>


<script>
/* Datos reales desde PHP/BD — sin mocks */
window.RUPE_ADMIN_INIT = {
  usuario: <?= json_encode($usuario ?? ($user ?? []), JSON_UNESCAPED_UNICODE) ?>,
  csrf: <?= json_encode($csrf ?? '', JSON_UNESCAPED_UNICODE) ?>,
  app_url: <?= json_encode($app_url ?? '', JSON_UNESCAPED_UNICODE) ?>,
  user: <?= json_encode($user ?? [], JSON_UNESCAPED_UNICODE) ?>,
  usuarios: <?= json_encode($usuarios ?? [], JSON_UNESCAPED_UNICODE) ?>,
  alcaldias: <?= json_encode($alcaldias ?? [], JSON_UNESCAPED_UNICODE) ?>,
  razas: <?= json_encode($razas ?? [], JSON_UNESCAPED_UNICODE) ?>,
  mascotas: <?= json_encode($mascotas ?? [], JSON_UNESCAPED_UNICODE) ?>,
  stats: <?= json_encode($stats ?? [], JSON_UNESCAPED_UNICODE) ?>,
  auditoria: <?= json_encode($auditoria ?? [], JSON_UNESCAPED_UNICODE) ?>,
  config: <?= json_encode($config ?? [], JSON_UNESCAPED_UNICODE) ?>,
  faqs: <?= json_encode($faqs ?? [], JSON_UNESCAPED_UNICODE) ?>
};
window.__CSRF__ = window.RUPE_ADMIN_INIT.csrf || window.__CSRF__ || '';
window.__RUPE_USER__ = window.RUPE_ADMIN_INIT.user || {};
window.__RUPE_USUARIOS__ = window.RUPE_ADMIN_INIT.usuarios || [];
window.__RUPE_ALCALDIAS__ = window.RUPE_ADMIN_INIT.alcaldias || [];
window.__RUPE_RAZAS__ = window.RUPE_ADMIN_INIT.razas || [];
window.__RUPE_REPORTES__ = window.RUPE_ADMIN_INIT.mascotas || [];
window.__RUPE_STATS__ = window.RUPE_ADMIN_INIT.stats || {};
window.__RUPE_AUDITORIA__ = window.RUPE_ADMIN_INIT.auditoria || [];
window.__RUPE_CONFIG__ = window.RUPE_ADMIN_INIT.config || {};
window.__RUPE_AYUDA__ = window.RUPE_ADMIN_INIT.faqs || [];
</script>


<script>
/* RUPE_ADMIN_NAV_FORCE — menú admin clicable (PHP 8.3 / ES6) */
(function () {
  function go(href) {
    if (!href || href === '#') return;
    window.location.href = href;
  }
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('a.nav-modulo[href]').forEach(function (a) {
      a.style.cursor = 'pointer';
      a.style.pointerEvents = 'auto';
      // Asegura navegación aunque algo capture el evento
      a.addEventListener('click', function (e) {
        var href = a.getAttribute('href');
        if (!href || href === '#') { e.preventDefault(); return; }
        // Permitir Ctrl/Cmd+click abrir pestaña
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;
        e.preventDefault();
        go(href);
      });
    });
    document.querySelectorAll('#btnAdminPerfil, #btnAdminConfig, #btnAdminAyuda, a.dropdown-item[href^="/admin/"]').forEach(function (a) {
      a.style.cursor = 'pointer';
      a.addEventListener('click', function (e) {
        var href = a.getAttribute('href');
        if (!href || href === '#') return;
        if (e.metaKey || e.ctrlKey) return;
        e.preventDefault();
        go(href);
      });
    });
  });
})();
</script>

  <script defer src="/assets/js/rupe-sanitize.js"></script>
  <script defer src="/assets/js/rupe-alerts.js"></script>
  <script defer src="/assets/js/rupe-panels.js"></script>
  <script src="/assets/js/rupe-responsive-nav.js?v=20260925nav" defer></script>
</body>
</html>
