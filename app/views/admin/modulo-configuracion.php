<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RUPE - Configuración</title>
  
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

    <section id="modulo-configuracion" class="modulo-seccion activo">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="fw-bold text-dark m-0">Configuración</h3>
          <p class="text-muted small m-0">Seguridad, notificaciones y preferencias de tu cuenta administrativa.</p>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-shield-lock me-1"></i> Seguridad de la cuenta</div>
            <div class="card-body">
              <form id="formCambiarContrasenaAdmin" onsubmit="cambiarContrasenaAdmin(event)" novalidate>
                <div class="mb-3">
                  <label class="form-label fw-semibold small" for="cfgAdminPassActual">Contraseña actual *</label>
                  <div class="input-group">
                    <input type="password" id="cfgAdminPassActual" class="form-control" placeholder="••••••••" required
                           maxlength="8" autocomplete="current-password">
                    <button type="button" class="btn btn-outline-secondary btn-toggle-pass" data-target="cfgAdminPassActual" aria-label="Mostrar u ocultar contraseña" title="Mostrar / ocultar">
                      <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                    <span class="input-group-text d-none text-success" id="okCfgPassActual"><i class="bi bi-check-circle-fill"></i></span>
                  </div>
                  <div class="invalid-feedback">Escribe tu contraseña actual (8 caracteres).</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold small" for="cfgAdminPassNueva">Nueva contraseña *</label>
                  <div class="input-group">
                    <input type="password" id="cfgAdminPassNueva" class="form-control" placeholder="Ej. Ru2e202!"
                           minlength="8" maxlength="8" required autocomplete="new-password"
                           oninput="validarFuerzaPassAdmin(); if(window.rupeValidarPassAdmin) rupeValidarPassAdmin();">
                    <button type="button" class="btn btn-outline-secondary btn-toggle-pass" data-target="cfgAdminPassNueva" aria-label="Mostrar u ocultar contraseña" title="Mostrar / ocultar">
                      <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                    <span class="input-group-text d-none text-success" id="okCfgPassNueva"><i class="bi bi-check-circle-fill"></i></span>
                  </div>
                  <div class="form-text small" id="cfgAdminPassHint">Exactamente <strong>8 caracteres</strong>: mayúscula, minúscula, número y especial (!@#$…).</div>
                  <ul class="list-unstyled small mb-0 mt-1" id="cfgAdminPassRules">
                    <li data-rule="len"><i class="bi bi-circle me-1"></i>Exactamente 8 caracteres</li>
                    <li data-rule="upper"><i class="bi bi-circle me-1"></i>Una mayúscula</li>
                    <li data-rule="lower"><i class="bi bi-circle me-1"></i>Una minúscula</li>
                    <li data-rule="num"><i class="bi bi-circle me-1"></i>Un número</li>
                    <li data-rule="spec"><i class="bi bi-circle me-1"></i>Un carácter especial</li>
                  </ul>
                  <div class="form-text small text-danger d-none" id="cfgAdminPassError"></div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold small" for="cfgAdminPassConfirmar">Confirmar nueva contraseña *</label>
                  <div class="input-group">
                    <input type="password" id="cfgAdminPassConfirmar" class="form-control" placeholder="Repite la nueva (8 caracteres)"
                           minlength="8" maxlength="8" required autocomplete="new-password"
                           oninput="if(window.rupeValidarPassAdmin) rupeValidarPassAdmin();">
                    <button type="button" class="btn btn-outline-secondary btn-toggle-pass" data-target="cfgAdminPassConfirmar" aria-label="Mostrar u ocultar contraseña" title="Mostrar / ocultar">
                      <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                    <span class="input-group-text d-none text-success" id="okCfgPassConfirmar"><i class="bi bi-check-circle-fill"></i></span>
                  </div>
                  <div class="invalid-feedback">Debe coincidir con la nueva contraseña.</div>
                </div>
                <button type="submit" class="btn btn-sm text-white" id="btnAdminPassSave" style="background-color:#6B1D2F;">
                  <i class="bi bi-key me-1"></i> Actualizar contraseña
                </button>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-bell me-1"></i> Notificaciones</div>
            <div class="card-body">
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="cfgAdminNotifPendientes" onchange="guardarPreferenciasAdmin()">
                <label class="form-check-label small" for="cfgAdminNotifPendientes">Nuevo reporte en la bandeja de validación</label>
              </div>
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="cfgAdminNotifCoincidencia" onchange="guardarPreferenciasAdmin()">
                <label class="form-check-label small" for="cfgAdminNotifCoincidencia">Nueva coincidencia con alta afinidad (≥80%)</label>
              </div>
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="cfgAdminNotifRezago" onchange="guardarPreferenciasAdmin()">
                <label class="form-check-label small" for="cfgAdminNotifRezago">Caso rezagado (más de 15 días sin avance)</label>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="cfgAdminNotifResumen" onchange="guardarPreferenciasAdmin()">
                <label class="form-check-label small" for="cfgAdminNotifResumen">Resumen semanal por correo</label>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-sliders me-1"></i> Preferencias del panel</div>
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label fw-semibold small">Alcaldía predeterminada al filtrar expedientes *</label>
                <div class="input-group">
                  <select id="cfgAdminAlcaldiaDefault" class="form-select" onchange="if(window.rupeValidarPrefsAdmin) rupeValidarPrefsAdmin();">
                    <option value="">Todas</option>
                  </select>
                  <span class="input-group-text d-none text-success" id="okCfgAlcaldia"><i class="bi bi-check-circle-fill"></i></span>
                </div>
                <div class="form-text small">Elige una alcaldía o deja «Todas».</div>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold small">Umbral por defecto de coincidencias *</label>
                <div class="input-group">
                  <select id="cfgAdminUmbralDefault" class="form-select" onchange="if(window.rupeValidarPrefsAdmin) rupeValidarPrefsAdmin();">
                    <option value="25">25% o más</option>
                    <option value="50">50% o más</option>
                    <option value="75">75% o más</option>
                  </select>
                  <span class="input-group-text d-none text-success" id="okCfgUmbral"><i class="bi bi-check-circle-fill"></i></span>
                </div>
              </div>
              <button type="button" class="btn btn-sm text-white w-100" id="btnGuardarPrefsPanel"
                      style="background-color:#6B1D2F;"
                      onclick="if(window.rupeValidarPrefsAdmin && !rupeValidarPrefsAdmin(true)){mostrarToast('Revisa las preferencias del panel.','warning');return;} guardarPreferenciasAdmin(true);">
                <i class="bi bi-save me-1"></i> Guardar cambios
              </button>
              <div class="form-text small mt-2 text-muted" id="cfgPrefsStatus"></div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-shield-check me-1"></i> Privacidad y mis datos</div>
            <div class="card-body">
              <p class="small text-muted mb-2">Tus datos de administrador se usan solo para operar el panel RUPE CDMX.</p>
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="cfgAdminPrivMostrarTel" onchange="guardarPreferenciasAdmin()">
                <label class="form-check-label small" for="cfgAdminPrivMostrarTel">Mostrar mi teléfono en el perfil del panel</label>
              </div>
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="cfgAdminPrivAuditoria" onchange="guardarPreferenciasAdmin()" checked>
                <label class="form-check-label small" for="cfgAdminPrivAuditoria">Registrar mis acciones en la bitácora de auditoría</label>
              </div>
              <p class="small text-muted mb-2">Cierra tu sesión en este equipo si vas a compartirlo con otro operador.</p>
              <button type="button" class="btn btn-sm btn-outline-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalCerrarSesion">
                <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
              </button>
              <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="guardarPreferenciasAdmin(true)">
                <i class="bi bi-save me-1"></i> Guardar preferencias generales
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <div class="toast-container-custom" id="toastContainer"></div>

  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const PREFS_KEY = 'rupe_admin_prefs_v1';
    let preferenciasAdmin = {
      notifPendientes: true,
      notifCoincidencia: true,
      notifRezago: true,
      notifResumen: false,
      alcaldiaDefault: '',
      umbralDefault: '50',
      privMostrarTel: false,
      privAuditoria: true
    };

    function mostrarToast(mensaje, tipo) {
      tipo = tipo || 'success';
      if (typeof window.rupeToast === 'function') return window.rupeToast(mensaje, tipo);
      const container = document.getElementById('toastContainer');
      if (!container) { try { alert(mensaje); } catch (_) {} return; }
      const iconMap = { success: 'bi-check-circle-fill', danger: 'bi-x-circle-fill', info: 'bi-info-circle-fill', warning: 'bi-exclamation-triangle-fill' };
      const bgMap = { success: 'bg-success', danger: 'bg-danger', info: 'bg-info', warning: 'bg-warning' };
      const toast = document.createElement('div');
      toast.className = 'toast-custom alert ' + (bgMap[tipo] || 'bg-success') + ' text-white d-flex align-items-center shadow-lg';
      toast.innerHTML = '<i class="bi ' + (iconMap[tipo] || 'bi-info-circle-fill') + ' me-2 fs-5"></i><span>' + mensaje + '</span>';
      container.appendChild(toast);
      setTimeout(function () {
        toast.style.transition = 'opacity 0.3s';
        toast.style.opacity = '0';
        setTimeout(function () { try { toast.remove(); } catch (_) {} }, 300);
      }, 3200);
    }
    window.mostrarToast = mostrarToast;

    function validarPasswordFuerteAdmin(pwd) {
      const p = String(pwd || '');
      const rules = {
        len: p.length === 8,
        upper: /[A-Z]/.test(p),
        lower: /[a-z]/.test(p),
        num: /\d/.test(p),
        spec: /[^A-Za-z0-9]/.test(p)
      };
      const fails = [];
      if (!rules.len) fails.push('Exactamente 8 caracteres');
      if (!rules.upper) fails.push('Una mayúscula');
      if (!rules.lower) fails.push('Una minúscula');
      if (!rules.num) fails.push('Un número');
      if (!rules.spec) fails.push('Un carácter especial');
      return { ok: fails.length === 0, rules: rules, message: fails.join(' · ') };
    }
    window.validarPasswordFuerteAdmin = validarPasswordFuerteAdmin;

    function validarFuerzaPassAdmin() {
      const el = document.getElementById('cfgAdminPassNueva');
      const ul = document.getElementById('cfgAdminPassRules');
      if (!el || !ul) return;
      // limitar a 8 caracteres
      if (el.value.length > 8) el.value = el.value.slice(0, 8);
      const r = validarPasswordFuerteAdmin(el.value);
      ul.querySelectorAll('[data-rule]').forEach(function (li) {
        const key = li.getAttribute('data-rule');
        const ok = !!(r.rules && r.rules[key]);
        const icon = li.querySelector('i');
        if (icon) icon.className = ok ? 'bi bi-check-circle-fill text-success me-1' : 'bi bi-circle me-1 text-muted';
        li.classList.toggle('text-success', ok);
        li.classList.toggle('text-muted', !ok);
      });
      el.classList.toggle('is-valid', r.ok && el.value.length > 0);
      el.classList.toggle('is-invalid', !r.ok && el.value.length > 0);
      const okN = document.getElementById('okCfgPassNueva');
      if (okN) okN.classList.toggle('d-none', !(r.ok && el.value.length > 0));
    }
    window.validarFuerzaPassAdmin = validarFuerzaPassAdmin;

    function rupeValidarPassAdmin() {
      var act = document.getElementById('cfgAdminPassActual');
      var neu = document.getElementById('cfgAdminPassNueva');
      var conf = document.getElementById('cfgAdminPassConfirmar');
      var okA = document.getElementById('okCfgPassActual');
      var okN = document.getElementById('okCfgPassNueva');
      var okC = document.getElementById('okCfgPassConfirmar');
      if (act && act.value.length > 8) act.value = act.value.slice(0, 8);
      if (neu && neu.value.length > 8) neu.value = neu.value.slice(0, 8);
      if (conf && conf.value.length > 8) conf.value = conf.value.slice(0, 8);

      var okAct = !!(act && act.value.length === 8);
      if (act) {
        act.classList.toggle('is-valid', okAct);
        act.classList.toggle('is-invalid', !okAct && act.value.length > 0);
      }
      if (okA) okA.classList.toggle('d-none', !okAct);

      var check = validarPasswordFuerteAdmin(neu ? neu.value : '');
      var okNeu = !!(check.ok);
      if (neu) {
        neu.classList.toggle('is-valid', okNeu);
        neu.classList.toggle('is-invalid', !okNeu && (neu.value || '').length > 0);
      }
      if (okN) okN.classList.toggle('d-none', !okNeu);
      validarFuerzaPassAdmin();

      var okConf = !!(conf && neu && conf.value.length === 8 && conf.value === neu.value && okNeu);
      if (conf) {
        conf.classList.toggle('is-valid', okConf);
        conf.classList.toggle('is-invalid', !okConf && (conf.value || '').length > 0);
      }
      if (okC) okC.classList.toggle('d-none', !okConf);
      return okAct && okNeu && okConf;
    }
    window.rupeValidarPassAdmin = rupeValidarPassAdmin;

    function rupeValidarPrefsAdmin(force) {
      var alc = document.getElementById('cfgAdminAlcaldiaDefault');
      var umb = document.getElementById('cfgAdminUmbralDefault');
      var okAlc = document.getElementById('okCfgAlcaldia');
      var okUmb = document.getElementById('okCfgUmbral');
      // «Todas» (vacío) es válido; umbral debe tener valor
      var aOk = !!alc; // select siempre válido
      var uOk = !!(umb && String(umb.value || '').trim() !== '');
      if (alc) {
        alc.classList.toggle('is-valid', aOk);
        alc.classList.remove('is-invalid');
      }
      if (okAlc) okAlc.classList.toggle('d-none', !aOk);
      if (umb) {
        umb.classList.toggle('is-valid', uOk);
        umb.classList.toggle('is-invalid', !uOk && force);
      }
      if (okUmb) okUmb.classList.toggle('d-none', !uOk);
      return aOk && uOk;
    }
    window.rupeValidarPrefsAdmin = rupeValidarPrefsAdmin;

    function loadPrefsLocal() {
      try {
        const raw = localStorage.getItem(PREFS_KEY);
        if (raw) {
          const o = JSON.parse(raw);
          preferenciasAdmin = Object.assign({}, preferenciasAdmin, o || {});
        }
      } catch (_) {}
    }

    function savePrefsLocal() {
      try {
        localStorage.setItem(PREFS_KEY, JSON.stringify(preferenciasAdmin));
      } catch (_) {}
    }

    async function cargarAlcaldiasConfig() {
      const sel = document.getElementById('cfgAdminAlcaldiaDefault');
      if (!sel) return;
      let list = [];
      try {
        if (window.RUPE_ADMIN_INIT && Array.isArray(window.RUPE_ADMIN_INIT.alcaldias) && window.RUPE_ADMIN_INIT.alcaldias.length) {
          list = window.RUPE_ADMIN_INIT.alcaldias;
        } else if (Array.isArray(window.__RUPE_ALCALDIAS__) && window.__RUPE_ALCALDIAS__.length) {
          list = window.__RUPE_ALCALDIAS__;
        } else if (window.RUPEAdmin && window.RUPEAdmin.api) {
          const res = await window.RUPEAdmin.api.get('/api/admin/alcaldias');
          const raw = res && res.data !== undefined ? res.data : res;
          if (Array.isArray(raw)) list = raw;
        } else {
          const r = await fetch('/api/admin/alcaldias', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
          const j = await r.json().catch(function () { return {}; });
          const raw = j.data !== undefined ? j.data : j;
          if (Array.isArray(raw)) list = raw;
        }
      } catch (e) {
        console.warn('[config] alcaldias', e);
      }
      const prev = preferenciasAdmin.alcaldiaDefault || sel.value || '';
      sel.innerHTML = '<option value="">Todas</option>';
      list.forEach(function (a) {
        if (!a) return;
        const nombre = a.nombre || '';
        if (!nombre) return;
        const opt = document.createElement('option');
        opt.value = nombre;
        opt.textContent = nombre;
        sel.appendChild(opt);
      });
      if (prev) {
        try { sel.value = prev; } catch (_) {}
      }
    }

    function renderizarConfiguracionAdmin() {
      loadPrefsLocal();
      const setChk = function (id, val) {
        const el = document.getElementById(id);
        if (el) el.checked = !!val;
      };
      setChk('cfgAdminNotifPendientes', preferenciasAdmin.notifPendientes);
      setChk('cfgAdminNotifCoincidencia', preferenciasAdmin.notifCoincidencia);
      setChk('cfgAdminNotifRezago', preferenciasAdmin.notifRezago);
      setChk('cfgAdminNotifResumen', preferenciasAdmin.notifResumen);
      setChk('cfgAdminPrivMostrarTel', preferenciasAdmin.privMostrarTel);
      setChk('cfgAdminPrivAuditoria', preferenciasAdmin.privAuditoria !== false);
      const umbral = document.getElementById('cfgAdminUmbralDefault');
      if (umbral) umbral.value = String(preferenciasAdmin.umbralDefault || '50');
      cargarAlcaldiasConfig().catch(function () {});
    }
    window.renderizarConfiguracionAdmin = renderizarConfiguracionAdmin;

    async function guardarPreferenciasAdmin(showToast) {
      try {
        preferenciasAdmin.notifPendientes = !!(document.getElementById('cfgAdminNotifPendientes') || {}).checked;
        preferenciasAdmin.notifCoincidencia = !!(document.getElementById('cfgAdminNotifCoincidencia') || {}).checked;
        preferenciasAdmin.notifRezago = !!(document.getElementById('cfgAdminNotifRezago') || {}).checked;
        preferenciasAdmin.notifResumen = !!(document.getElementById('cfgAdminNotifResumen') || {}).checked;
        preferenciasAdmin.alcaldiaDefault = (document.getElementById('cfgAdminAlcaldiaDefault') || {}).value || '';
        preferenciasAdmin.umbralDefault = (document.getElementById('cfgAdminUmbralDefault') || {}).value || '50';
        preferenciasAdmin.privMostrarTel = !!(document.getElementById('cfgAdminPrivMostrarTel') || {}).checked;
        preferenciasAdmin.privAuditoria = !!(document.getElementById('cfgAdminPrivAuditoria') || {}).checked;

        savePrefsLocal();

        // Aplicar umbral si el control existe en reportes
        const umbralInput = document.getElementById('umbralMatch');
        if (umbralInput) umbralInput.value = preferenciasAdmin.umbralDefault;

        // Intentar persistir en API de config (no bloquear si falla)
        try {
          const csrf = window.__CSRF__ || (window.RUPE_ADMIN_INIT && window.RUPE_ADMIN_INIT.csrf) || '';
          const pairs = [
            ['admin_notif_pendientes', preferenciasAdmin.notifPendientes ? '1' : '0'],
            ['admin_notif_coincidencia', preferenciasAdmin.notifCoincidencia ? '1' : '0'],
            ['admin_notif_rezago', preferenciasAdmin.notifRezago ? '1' : '0'],
            ['admin_notif_resumen', preferenciasAdmin.notifResumen ? '1' : '0'],
            ['admin_alcaldia_default', preferenciasAdmin.alcaldiaDefault],
            ['admin_umbral_default', preferenciasAdmin.umbralDefault],
            ['admin_priv_mostrar_tel', preferenciasAdmin.privMostrarTel ? '1' : '0'],
            ['admin_priv_auditoria', preferenciasAdmin.privAuditoria ? '1' : '0']
          ];
          for (let i = 0; i < pairs.length; i++) {
            const body = { clave: pairs[i][0], valor: pairs[i][1], tipo: 'string', _csrf: csrf };
            await fetch('/api/admin/config', {
              method: 'POST',
              credentials: 'same-origin',
              headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrf
              },
              body: JSON.stringify(body)
            }).catch(function () { return null; });
          }
        } catch (_) {}

        if (showToast !== false) {
          mostrarToast('Preferencias guardadas.', 'success');
        }
        var st = document.getElementById('cfgPrefsStatus');
        if (st) {
          st.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Cambios guardados</span>';
        }
        if (typeof window.rupeValidarPrefsAdmin === 'function') window.rupeValidarPrefsAdmin();
      } catch (err) {
        console.warn('[config] prefs', err);
        mostrarToast('No se pudieron guardar las preferencias.', 'warning');
      }
    }
    window.guardarPreferenciasAdmin = guardarPreferenciasAdmin;

    async function cambiarContrasenaAdmin(e) {
      if (e && e.preventDefault) e.preventDefault();
      const actual = (document.getElementById('cfgAdminPassActual') || {}).value || '';
      const nueva = (document.getElementById('cfgAdminPassNueva') || {}).value || '';
      const confirmar = (document.getElementById('cfgAdminPassConfirmar') || {}).value || '';
      const errEl = document.getElementById('cfgAdminPassError');
      if (errEl) { errEl.textContent = ''; errEl.classList.add('d-none'); }

      if (typeof window.rupeValidarPassAdmin === 'function' && !window.rupeValidarPassAdmin()) {
        mostrarToast('Completa contraseña actual y nueva de exactamente 8 caracteres (mayúscula, minúscula, número y especial).', 'warning');
        return false;
      }
      if (!actual || actual.length !== 8) {
        mostrarToast('Contraseña actual: exactamente 8 caracteres.', 'warning');
        return false;
      }
      const check = validarPasswordFuerteAdmin(nueva);
      if (!check.ok) {
        if (errEl) { errEl.textContent = check.message; errEl.classList.remove('d-none'); }
        mostrarToast('Contraseña débil: ' + check.message, 'warning');
        return false;
      }
      if (nueva !== confirmar) {
        mostrarToast('La confirmación no coincide con la nueva contraseña.', 'warning');
        return false;
      }

      const btn = document.getElementById('btnAdminPassSave');
      if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando…'; }
      try {
        const csrf = window.__CSRF__ || (window.RUPE_ADMIN_INIT && window.RUPE_ADMIN_INIT.csrf) || '';
        const res = await fetch('/api/admin/password', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrf
          },
          body: JSON.stringify({
            password_actual: actual,
            password: nueva,
            password_confirmation: confirmar,
            _csrf: csrf
          })
        });
        const data = await res.json().catch(function () { return {}; });
        if (!res.ok || data.success === false) {
          const msg = (data && data.message) || 'No se pudo actualizar la contraseña.';
          if (errEl) { errEl.textContent = msg; errEl.classList.remove('d-none'); }
          mostrarToast(msg, 'danger');
          return false;
        }
        const form = document.getElementById('formCambiarContrasenaAdmin');
        if (form) form.reset();
        validarFuerzaPassAdmin();
        mostrarToast(data.message || 'Contraseña actualizada correctamente.', 'success');
      } catch (err) {
        console.warn('[config] password', err);
        mostrarToast('Error de red al actualizar la contraseña.', 'danger');
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-key me-1"></i> Actualizar contraseña';
        }
      }
      return false;
    }
    window.cambiarContrasenaAdmin = cambiarContrasenaAdmin;

    document.addEventListener('DOMContentLoaded', function () {
      ['cfgAdminPassActual', 'cfgAdminPassNueva', 'cfgAdminPassConfirmar'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
          el.addEventListener('input', function () { if (window.rupeValidarPassAdmin) rupeValidarPassAdmin(); });
          el.addEventListener('blur', function () { if (window.rupeValidarPassAdmin) rupeValidarPassAdmin(); });
        }
      });
      setTimeout(function () {
        if (window.rupeValidarPrefsAdmin) rupeValidarPrefsAdmin();
      }, 300);

      renderizarConfiguracionAdmin();
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

<script>
(function () {
  function rupeTogglePasswordVisibility(btn) {
    if (!btn) return;
    var id = btn.getAttribute('data-target');
    var input = id ? document.getElementById(id) : null;
    if (!input || input.tagName !== 'INPUT') return;
    // Solo cambia type; no altera value ni validación de 8 caracteres
    var show = input.getAttribute('type') === 'password';
    input.setAttribute('type', show ? 'text' : 'password');
    var icon = btn.querySelector('i');
    if (icon) {
      icon.classList.remove('bi-eye', 'bi-eye-slash');
      icon.classList.add(show ? 'bi-eye-slash' : 'bi-eye');
    }
    btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
    btn.setAttribute('title', show ? 'Ocultar' : 'Mostrar');
    try { input.focus({ preventScroll: true }); } catch (e) {
      try { input.focus(); } catch (e2) {}
    }
  }
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('.btn-toggle-pass');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    rupeTogglePasswordVisibility(btn);
  }, true);
  function bindButtons() {
    document.querySelectorAll('.btn-toggle-pass[data-target]').forEach(function (btn) {
      if (btn._rupePassBound) return;
      btn._rupePassBound = true;
      btn.type = 'button';
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        rupeTogglePasswordVisibility(btn);
      });
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindButtons);
  } else {
    bindButtons();
  }
  window.rupeTogglePasswordVisibility = rupeTogglePasswordVisibility;
})();
</script>

</body>
</html>
