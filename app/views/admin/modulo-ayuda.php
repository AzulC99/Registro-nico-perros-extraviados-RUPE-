<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RUPE - Centro de Ayuda</title>
  
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

    <section id="modulo-ayuda" class="modulo-seccion activo">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="fw-bold text-dark m-0">Centro de Ayuda</h3>
          <p class="text-muted small m-0">Guías rápidas del panel administrativo y soporte directo.</p>
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-body py-3">
          <label class="form-label small fw-semibold mb-1">¿En qué necesitas ayuda?</label>
          <input type="text" id="ayudaAdminBuscador" class="form-control" placeholder="Ej. validar, coincidencias, constancia, contraseña..." oninput="filtrarAyudaAdmin()">
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-7">
          <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-signpost-split me-1"></i> Guía rápida por módulo</div>
            <div class="list-group list-group-flush">
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="navegarAModulo('reportes')">
                <i class="bi bi-bar-chart-line-fill text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Reportes y Análisis</strong><br><span class="small text-muted">Panel de control, expedientes, validación, coincidencias y estadísticas.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="navegarAModulo('usuarios')">
                <i class="bi bi-people-fill text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Usuarios</strong><br><span class="small text-muted">Altas, bajas y roles de las cuentas del sistema.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="navegarAModulo('razas')">
                <i class="bi bi-tags-fill text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Catálogo de Razas</strong><br><span class="small text-muted">Administra las razas disponibles en los formularios.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="navegarAModulo('alcaldias')">
                <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Catálogo de Alcaldías</strong><br><span class="small text-muted">Administra las 16 alcaldías de la CDMX.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="navegarAModulo('auditoria')">
                <i class="bi bi-journal-check text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Auditoría</strong><br><span class="small text-muted">Bitácora de acciones administrativas del sistema.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="navegarAModulo('calculos')">
                <i class="bi bi-percent text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Porcentajes</strong><br><span class="small text-muted">Métricas de efectividad y tiempos de resolución.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
            </div>
          </div>

          <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-patch-question me-1"></i> Preguntas frecuentes</div>
            <div class="card-body p-0">
              <div class="accordion accordion-flush" id="acordionAyudaAdminFAQ">

                <div class="accordion-item" data-ayuda-admin-item data-ayuda-texto="validar bandeja pendiente aprobar">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqA1">¿Cómo valido un reporte pendiente?</button>
                  </h2>
                  <div id="faqA1" class="accordion-collapse collapse" data-bs-parent="#acordionAyudaAdminFAQ">
                    <div class="accordion-body small text-muted">Entra a <strong>Reportes y Análisis → Bandeja de Validación</strong>. Cada tarjeta muestra la foto, los datos capturados y las alertas de campos faltantes (microchip, contacto, etc.). Usa "Validar" o "Rechazar".</div>
                  </div>
                </div>

                <div class="accordion-item" data-ayuda-admin-item data-ayuda-texto="coincidencias afinidad cruce reunificar">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqA2">¿Cómo funcionan las coincidencias?</button>
                  </h2>
                  <div id="faqA2" class="accordion-collapse collapse" data-bs-parent="#acordionAyudaAdminFAQ">
                    <div class="accordion-body small text-muted">El sistema cruza automáticamente los reportes de "Extraviado" contra "Encontrado / En Resguardo" y calcula un % de afinidad (raza, tamaño, color, zona, fechas). Ajusta el umbral y usa "Confirmar reunificación" cuando coincida.</div>
                  </div>
                </div>

                <div class="accordion-item" data-ayuda-admin-item data-ayuda-texto="pdf constancia qr gps foto">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqA3">¿Cómo genero la constancia en PDF con QR?</button>
                  </h2>
                  <div id="faqA3" class="accordion-collapse collapse" data-bs-parent="#acordionAyudaAdminFAQ">
                    <div class="accordion-body small text-muted">Desde el listado de Expedientes, botón <i class="bi bi-file-earmark-pdf"></i>. El QR solo incluye la ubicación GPS si ya la validaste con el botón "Marcar GPS como validado" dentro del mismo modal.</div>
                  </div>
                </div>

                <div class="accordion-item" data-ayuda-admin-item data-ayuda-texto="contrasena password cambiar seguridad">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqA4">¿Cómo cambio mi contraseña?</button>
                  </h2>
                  <div id="faqA4" class="accordion-collapse collapse" data-bs-parent="#acordionAyudaAdminFAQ">
                    <div class="accordion-body small text-muted">En <strong>Configuración → Seguridad de la cuenta</strong>, ingresa tu contraseña actual y la nueva (mínimo 8 caracteres).</div>
                  </div>
                </div>

                <div class="accordion-item" data-ayuda-admin-item data-ayuda-texto="auditoria bitacora registro accion">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqA5">¿Qué queda registrado en Auditoría?</button>
                  </h2>
                  <div id="faqA5" class="accordion-collapse collapse" data-bs-parent="#acordionAyudaAdminFAQ">
                    <div class="accordion-body small text-muted">Cada validación, rechazo, reunificación confirmada y cambio de estatus genera un evento con usuario, fecha/hora y detalle, visible en el módulo de Auditoría.</div>
                  </div>
                </div>

              </div>
              <p class="text-center text-muted small py-3 d-none" id="ayudaAdminSinResultados">
                <i class="bi bi-search me-1"></i> No encontramos preguntas relacionadas con tu búsqueda.
              </p>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-headset me-1"></i> Contacto directo</div>
            <div class="card-body small">
              <p class="mb-2"><i class="bi bi-telephone me-2 text-muted"></i>55 5658 1111 (Línea RUPE CDMX)</p>
              <p class="mb-2"><i class="bi bi-envelope me-2 text-muted"></i>soporte-admin@rupe.cdmx.gob.mx</p>
              <p class="mb-0"><i class="bi bi-clock me-2 text-muted"></i>Lunes a viernes, 9:00–18:00 hrs</p>
            </div>
          </div>
          <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-envelope-paper me-1"></i> Enviar un mensaje</div>
            <div class="card-body">
              <form id="formContactoAyudaAdmin" onsubmit="enviarMensajeAyudaAdmin(event)" novalidate>
                <div class="mb-3">
                  <label class="form-label fw-semibold small">Asunto *</label>
                  <div class="input-group">
                    <select id="ayudaAdminAsunto" class="form-select" required>
                      <option value="">Selecciona un tema</option>
                      <option value="Error en el panel">Error en el panel</option>
                      <option value="Duda sobre un expediente">Duda sobre un expediente</option>
                      <option value="Solicitud de nuevo usuario">Solicitud de nuevo usuario</option>
                      <option value="Otro">Otro</option>
                    </select>
                    <span class="input-group-text d-none text-success" id="okAyudaAsunto"><i class="bi bi-check-circle-fill"></i></span>
                  </div>
                  <div class="invalid-feedback">Selecciona un asunto.</div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold small">Mensaje *</label>
                  <div class="input-group">
                    <textarea id="ayudaAdminMensaje" class="form-control" rows="4" maxlength="500"
                              placeholder="Cuéntanos en qué podemos ayudarte (mín. 10 caracteres)..." required></textarea>
                    <span class="input-group-text d-none text-success align-items-start pt-2" id="okAyudaMensaje"><i class="bi bi-check-circle-fill"></i></span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <div class="form-text">Mínimo 10 caracteres (máx. 500).</div>
                    <div class="form-text" id="ayudaAdminMensajeCount">0 / 500</div>
                  </div>
                  <div class="invalid-feedback">El mensaje debe tener entre 10 y 500 caracteres.</div>
                </div>
                <button type="submit" class="btn btn-sm w-100 text-white" id="btnEnviarAyudaAdmin" style="background-color:#6B1D2F;">
                  <i class="bi bi-send me-1"></i> Enviar mensaje
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <div class="toast-container-custom" id="toastContainer"></div>

  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navegación entre archivos independientes de cada módulo
    function navegarAModulo(nombreModulo) {
      const mapaArchivos = {
        reportes: '/admin/reportes', usuarios: '/admin/usuarios',
        razas: '/admin/razas', alcaldias: '/admin/alcaldias',
        auditoria: '/admin/auditoria', calculos: '/admin/calculos',
        perfil: '/admin/perfil', configuracion: '/admin/configuracion',
        ayuda: '/admin/ayuda'
      };
      if (mapaArchivos[nombreModulo]) window.location.href = mapaArchivos[nombreModulo];
    }
    function filtrarAyudaAdmin() {
      const texto = document.getElementById('ayudaAdminBuscador').value.trim().toLowerCase();
      const items = document.querySelectorAll('#acordionAyudaAdminFAQ [data-ayuda-admin-item]');
      let visibles = 0;
      items.forEach(item => {
        const pregunta = item.querySelector('.accordion-button').textContent.toLowerCase();
        const claves = (item.dataset.ayudaTexto || '').toLowerCase();
        const coincide = !texto || pregunta.includes(texto) || claves.includes(texto);
        item.classList.toggle('d-none', !coincide);
        if (coincide) visibles++;
      });
      document.getElementById('ayudaAdminSinResultados').classList.toggle('d-none', visibles !== 0);
    }
    async function ensureCsrfAyudaAdmin() {
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

    function rupeValidarAyudaAdmin(force) {
      var asuntoEl = document.getElementById('ayudaAdminAsunto');
      var msgEl = document.getElementById('ayudaAdminMensaje');
      var okA = document.getElementById('okAyudaAsunto');
      var okM = document.getElementById('okAyudaMensaje');
      var cnt = document.getElementById('ayudaAdminMensajeCount');
      var asunto = (asuntoEl && asuntoEl.value ? asuntoEl.value : '').trim();
      var mensaje = (msgEl && msgEl.value ? msgEl.value : '').trim();
      if (cnt) cnt.textContent = mensaje.length + ' / 500';
      var aOk = asunto.length > 0;
      var mOk = mensaje.length >= 10 && mensaje.length <= 500;
      if (asuntoEl) {
        asuntoEl.classList.toggle('is-valid', aOk);
        asuntoEl.classList.toggle('is-invalid', !aOk && (force || asuntoEl.dataset.touched === '1'));
      }
      if (okA) okA.classList.toggle('d-none', !aOk);
      if (msgEl) {
        msgEl.classList.toggle('is-valid', mOk);
        msgEl.classList.toggle('is-invalid', !mOk && (force || mensaje.length > 0));
      }
      if (okM) okM.classList.toggle('d-none', !mOk);
      return aOk && mOk;
    }
    window.rupeValidarAyudaAdmin = rupeValidarAyudaAdmin;

    document.addEventListener('DOMContentLoaded', function () {
      var a = document.getElementById('ayudaAdminAsunto');
      var m = document.getElementById('ayudaAdminMensaje');
      if (a) {
        a.addEventListener('change', function () { a.dataset.touched = '1'; rupeValidarAyudaAdmin(false); });
        a.addEventListener('blur', function () { a.dataset.touched = '1'; rupeValidarAyudaAdmin(false); });
      }
      if (m) {
        m.addEventListener('input', function () { rupeValidarAyudaAdmin(false); });
        m.addEventListener('blur', function () { rupeValidarAyudaAdmin(false); });
      }
    });

    async function enviarMensajeAyudaAdmin(e) {
      e.preventDefault();
      const form = document.getElementById('formContactoAyudaAdmin');
      const asuntoEl = document.getElementById('ayudaAdminAsunto');
      const msgEl = document.getElementById('ayudaAdminMensaje');
      if (typeof window.rupeValidarAyudaAdmin === 'function' && !window.rupeValidarAyudaAdmin(true)) {
        mostrarToast('Selecciona un asunto y escribe un mensaje de 10 a 500 caracteres.', 'warning');
        return;
      }
      const asunto = (asuntoEl && asuntoEl.value ? asuntoEl.value : '').trim();
      const mensaje = (msgEl && msgEl.value ? msgEl.value : '').trim();
      if (!asunto) {
        mostrarToast('Selecciona un asunto.', 'warning');
        return;
      }
      if (mensaje.length < 10 || mensaje.length > 500) {
        mostrarToast('El mensaje debe tener entre 10 y 500 caracteres.', 'warning');
        return;
      }
      const btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.dataset._old = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando…';
      }
      try {
        const csrf = await ensureCsrfAyudaAdmin();
        const res = await fetch('/api/admin/ayuda-contacto', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrf || ''
          },
          body: JSON.stringify({ asunto, mensaje, _csrf: csrf || '' })
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || data.success === false) {
          let msg = data.message || 'No se pudo enviar el mensaje.';
          if (data.errors) {
            try { msg = Object.values(data.errors).flat().join(' '); } catch (_) {}
          }
          mostrarToast(msg, 'danger');
          return;
        }
        if (form) form.reset();
        if (typeof window.rupeValidarAyudaAdmin === 'function') {
          // limpiar estados
          [asuntoEl, msgEl].forEach(function (el) {
            if (el) { el.classList.remove('is-valid', 'is-invalid'); el.dataset.touched = ''; }
          });
          var okA = document.getElementById('okAyudaAsunto');
          var okM = document.getElementById('okAyudaMensaje');
          if (okA) okA.classList.add('d-none');
          if (okM) okM.classList.add('d-none');
          var cnt = document.getElementById('ayudaAdminMensajeCount');
          if (cnt) cnt.textContent = '0 / 500';
        }
        const dest = (data.data && data.data.destino) || 'admin@buscandoahuellitasrupe.com.mx';
        mostrarToast(data.message || ('Mensaje enviado a ' + dest), 'success');
      } catch (err) {
        console.warn('[RUPE Admin] ayuda', err);
        mostrarToast('Error de red al enviar. Intenta de nuevo.', 'danger');
      } finally {
        if (btn) {
          btn.disabled = false;
          if (btn.dataset._old) btn.innerHTML = btn.dataset._old;
        }
      }
    }
    window.enviarMensajeAyudaAdmin = enviarMensajeAyudaAdmin;
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
</body>
</html>
