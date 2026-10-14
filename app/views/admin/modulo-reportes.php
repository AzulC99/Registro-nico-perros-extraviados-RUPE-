<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RUPE - Reportes y Análisis</title>
  
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
            <a class="nav-link nav-modulo active" href="/admin/reportes">
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

    <section id="modulo-reportes" class="modulo-seccion activo">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
          <h3 class="fw-bold text-dark m-0">Reportes y Análisis Estadístico</h3>
          <p class="text-muted small m-0">Panel de análisis, control, validación, cruce de coincidencias y estadísticas de los casos capturados por los tutores.</p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" onclick="refrescarModuloReportes()">
            <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
          </button>
          <button class="btn btn-success btn-sm" onclick="exportarReportesExcel()">
            <i class="bi bi-file-earmark-excel me-1"></i> Exportar
          </button>
        </div>
      </div>

      <!-- Sub-tabs del módulo de reportes -->
      <ul class="nav sub-tab-nav mb-4 flex-wrap" id="subTabReportes">
        <li class="nav-item">
          <a class="nav-link active" data-subtab="reportes-panel">
            <i class="bi bi-speedometer2 me-1"></i> Panel de Control
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-subtab="reportes-listado">
            <i class="bi bi-folder2-open me-1"></i> Expedientes
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-subtab="reportes-validacion">
            <i class="bi bi-shield-check me-1"></i> Bandeja de Validación
            <span class="badge bg-danger ms-1" id="badgePendientes">0</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-subtab="reportes-coincidencias">
            <i class="bi bi-arrow-left-right me-1"></i> Coincidencias
            <span class="badge bg-warning text-dark ms-1" id="badgeMatches">0</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-subtab="reportes-catalogos">
            <i class="bi bi-book me-1"></i> Catálogos del Sistema
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-subtab="reportes-estadisticos">
            <i class="bi bi-file-earmark-bar-graph me-1"></i> Reportes Estadísticos
          </a>
        </li>
      </ul>

      <!-- ============ SUB-TAB 1: PANEL DE CONTROL ============ -->
      <div id="subtab-reportes-panel" class="sub-tab-content activo">
        <div class="row g-3 mb-4">
          <div class="col-6 col-lg-3">
            <div class="kpi-card">
              <div class="kpi-label"><i class="bi bi-collection me-1"></i>Total de reportes</div>
              <div class="kpi-value text-dark" id="kpiTotal">0</div>
              <small class="text-muted">Reportes de tutores en BD</small>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-danger">
              <div class="kpi-label"><i class="bi bi-exclamation-triangle me-1"></i>Casos abiertos</div>
              <div class="kpi-value text-danger" id="kpiAbiertos">0</div>
              <small class="text-muted">Requieren seguimiento</small>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-success">
              <div class="kpi-label"><i class="bi bi-check2-circle me-1"></i>Tasa de resolución</div>
              <div class="kpi-value text-success" id="kpiTasa">0%</div>
              <small class="text-muted">Casos reunidos con su tutor</small>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-info">
              <div class="kpi-label"><i class="bi bi-clock-history me-1"></i>Días promedio</div>
              <div class="kpi-value text-info" id="kpiDias">0</div>
              <small class="text-muted">Tiempo de resolución</small>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-lg-5">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-white py-3">
                <h6 class="fw-bold m-0"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Distribución por tipo de reporte</h6>
              </div>
              <div class="card-body">
                <div style="max-width: 260px; margin: 0 auto;">
                  <canvas id="graficaTipos" height="260"></canvas>
                </div>
                <div id="distTipos" class="mt-3"></div>
              </div>
            </div>
          </div>
          <div class="col-lg-7">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Incidencia por alcaldía</h6>
                <span class="badge bg-light text-dark border">Top 6</span>
              </div>
              <div class="card-body">
                <canvas id="graficaAlcaldias" height="220"></canvas>
                <div id="distAlcaldias" class="mt-3"></div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-white py-3">
                <h6 class="fw-bold m-0"><i class="bi bi-tags-fill me-2 text-primary"></i>Razas más reportadas</h6>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="bg-light">
                      <tr><th class="ps-3">Raza</th><th>Tamaño</th><th class="text-center">Casos</th><th class="text-end pe-3">% del total</th></tr>
                    </thead>
                    <tbody id="tbodyTopRazas"></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-white py-3">
                <h6 class="fw-bold m-0"><i class="bi bi-lightbulb-fill me-2 text-warning"></i>Alertas operativas (datos reales)</h6>
              </div>
              <div class="card-body" id="alertasPanel">
                <div id="listaAlertas"></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ============ SUB-TAB 2: EXPEDIENTES ============ -->
      <div id="subtab-reportes-listado" class="sub-tab-content">
        <div class="card shadow-sm mb-3">
          <div class="card-body py-3">
            <div class="row g-2 align-items-end">
              <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Buscar</label>
                <input type="text" class="form-control form-control-sm" id="repBuscar" placeholder="Folio, mascota o tutor..." oninput="renderizarTablaReportes()">
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Tipo</label>
                <select class="form-select form-select-sm" id="repTipo" onchange="renderizarTablaReportes()">
                  <option value="">Todos</option>
                  <option value="Extraviado">Extraviado</option>
                  <option value="Encontrado">Encontrado</option>
                  <option value="En Resguardo">En Resguardo</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Estatus</label>
                <select class="form-select form-select-sm" id="repEstatus" onchange="renderizarTablaReportes()">
                  <option value="">Todos</option>
                  <option value="Pendiente">Pendiente de validación</option>
                  <option value="Validado">Validado</option>
                  <option value="En Seguimiento">En seguimiento</option>
                  <option value="Resuelto">Resuelto</option>
                  <option value="Rechazado">Rechazado</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Alcaldía</label>
                <select class="form-select form-select-sm" id="repAlcaldia" onchange="renderizarTablaReportes()">
                  <option value="">Todas</option>
                </select>
              </div>
              <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary w-100" onclick="limpiarFiltrosReportes()">
                  <i class="bi bi-x-circle me-1"></i> Limpiar
                </button>
                <button class="btn btn-sm btn-outline-primary w-100" onclick="alternarPrioridad()" id="btnPrioridad">
                  <i class="bi bi-sort-down me-1"></i> Prioridad
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
          <span class="catalogo-counter">
            <i class="bi bi-funnel me-1"></i>Mostrando <strong id="repContador">0</strong> de <strong id="repTotal">0</strong> expedientes
          </span>
          <div class="small text-muted">
            <span class="semaforo semaforo-verde"></span>0-7 días
            <span class="semaforo semaforo-amarillo ms-3"></span>8-15 días
            <span class="semaforo semaforo-rojo ms-3"></span>Más de 15 días
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0" id="tablaReportes">
                <thead class="bg-light">
                  <tr>
                    <th class="ps-4">Foto</th>
                    <th>Folio</th>
                    <th>Mascota / Raza</th>
                    <th>Tutor</th>
                    <th>Alcaldía</th>
                    <th>Tipo</th>
                    <th>Estatus</th>
                    <th>Antigüedad</th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td colspan="9" class="text-center text-muted py-4">Cargando expedientes desde la base de datos…</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- ============ SUB-TAB 3: BANDEJA DE VALIDACIÓN ============ -->
      <div id="subtab-reportes-validacion" class="sub-tab-content">
        <div class="alert alert-warning py-2 small">
          <i class="bi bi-shield-exclamation me-1"></i>
          Los reportes de tutores con estatus <strong>En Búsqueda</strong> (pendientes de validación) se cargan desde la base de datos. Revisa la información y <strong>valida</strong> cada caso. Los datos se cargan desde la base de datos.
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <span class="catalogo-counter"><i class="bi bi-inbox me-1"></i><strong id="valContador">0</strong> reportes esperando revisión</span>
          <button type="button" id="btnValidarTodosPendientes" class="btn btn-sm btn-outline-success"
                  onclick="event.preventDefault(); if(window.validarTodosPendientes){validarTodosPendientes();} return false;">
            <i class="bi bi-check2-all me-1"></i> Validar todos
          </button>
        </div>
        <div class="row g-3" id="contenedorValidacion"><div class="col-12 text-center text-muted py-4 small">Cargando bandeja desde la base de datos…</div></div>
      </div>

      <!-- ============ SUB-TAB 4: COINCIDENCIAS ============ -->
      <div id="subtab-reportes-coincidencias" class="sub-tab-content">
        <div class="alert alert-info py-2 small">
          <i class="bi bi-arrow-left-right me-1"></i>
          El sistema cruza <strong>reportes reales de tutores</strong> (tabla mascotas) comparando raza, tamaño, alcaldía, color y especie. Sin datos inventados.
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="small fw-bold text-muted mb-0">Coincidencia mínima</label>
            <select class="form-select form-select-sm" style="width:110px;" id="umbralMatch" onchange="if(window.renderizarCoincidencias)renderizarCoincidencias(false)">
              <option value="25">25%</option>
              <option value="50" selected>50%</option>
              <option value="75">75%</option>
            </select>
          </div>
          <button type="button" id="btnEjecutarCruce" class="btn btn-sm text-white" style="background-color: var(--color-primary); border:none;"
                  data-action="ejecutar-cruce"
                  onclick="event.preventDefault(); if(window.ejecutarCruceCoincidencias){ejecutarCruceCoincidencias(true);}else if(window.renderizarCoincidencias){renderizarCoincidencias(true);} return false;">
            <i class="bi bi-cpu me-1"></i> Ejecutar cruce
          </button>
        </div>
        <div class="row g-3" id="contenedorMatches"><div class="col-12 text-center text-muted py-4 small">Cargando coincidencias desde la base de datos…</div></div>
      </div>

      <!-- ============ SUB-TAB 5: CATÁLOGOS DEL SISTEMA ============ -->
      <div id="subtab-reportes-catalogos" class="sub-tab-content">
        <div class="alert alert-info py-2 small mb-4">
          <i class="bi bi-info-circle me-1"></i> Catálogos leídos desde la base de datos (<code>razas</code>, <code>alcaldias</code>). Sin datos inventados. Gestiona altas/edición en los módulos Cat. Razas y Cat. Alcaldías.
        </div>

        <div class="row g-4">
          <!-- Catálogo de Razas -->
          <div class="col-lg-6">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0"><i class="bi bi-tags-fill me-2 text-primary"></i>Catálogo de Razas</h6>
                <span class="badge bg-primary" id="contadorRazasCatalogo">0 registros</span>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                  <table class="table table-hover table-sm align-middle mb-0" id="catRazasInline">
                    <thead class="bg-light sticky-top">
                      <tr><th class="ps-3">Código</th><th>Raza</th><th>Tamaño</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                      <!-- Se llena dinámicamente desde JS -->
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="card-footer bg-white text-end">
                <a class="btn btn-sm btn-outline-primary" onclick="navegarAModulo('razas')">
                  <i class="bi bi-arrow-right-circle me-1"></i> Ir a Catálogo de Razas
                </a>
              </div>
            </div>
          </div>

          <!-- Catálogo de Alcaldías -->
          <div class="col-lg-6">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0"><i class="bi bi-geo-alt-fill me-2 text-success"></i>Catálogo de Alcaldías</h6>
                <span class="badge bg-success" id="contadorAlcaldiasCatalogo">0 registros</span>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                  <table class="table table-hover table-sm align-middle mb-0" id="catAlcaldiasInline">
                    <thead class="bg-light sticky-top">
                      <tr><th class="ps-3">ID</th><th>Alcaldía</th><th>Zona</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                      <!-- Se llena dinámicamente desde JS -->
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="card-footer bg-white text-end">
                <a class="btn btn-sm btn-outline-success" onclick="navegarAModulo('alcaldias')">
                  <i class="bi bi-arrow-right-circle me-1"></i> Ir a Catálogo de Alcaldías
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Catálogo de Estatus y Tipos -->
        <div class="row g-4 mt-1">
          <div class="col-lg-6">
            <div class="card shadow-sm">
              <div class="card-header bg-white py-3">
                <h6 class="fw-bold m-0"><i class="bi bi-flag-fill me-2 text-warning"></i>Catálogo de Tipos / Estatus de Reporte</h6>
              </div>
              <div class="card-body p-0">
                <table class="table table-hover table-sm align-middle mb-0">
                  <thead class="bg-light">
                    <tr><th class="ps-3">Clave</th><th>Descripción</th><th>Color Badge</th></tr>
                  </thead>
                  <tbody>
                    <tr><td class="ps-3 fw-bold">BUS</td><td>En Búsqueda</td><td><span class="badge bg-warning text-dark">warning</span></td></tr>
                    <tr><td class="ps-3 fw-bold">RSG</td><td>En Resguardo</td><td><span class="badge bg-info text-dark">info</span></td></tr>
                    <tr><td class="ps-3 fw-bold">LOC</td><td>Localizado</td><td><span class="badge bg-success">success</span></td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card shadow-sm">
              <div class="card-header bg-white py-3">
                <h6 class="fw-bold m-0"><i class="bi bi-ruler me-2 text-info"></i>Catálogo de Tamaños</h6>
              </div>
              <div class="card-body p-0">
                <table class="table table-hover table-sm align-middle mb-0">
                  <thead class="bg-light">
                    <tr><th class="ps-3">Clave</th><th>Descripción</th><th>Rango (kg)</th></tr>
                  </thead>
                  <tbody>
                    <tr><td class="ps-3 fw-bold">MIN</td><td>Miniatura</td><td>1 – 5 kg</td></tr>
                    <tr><td class="ps-3 fw-bold">PEQ</td><td>Pequeño</td><td>5 – 12 kg</td></tr>
                    <tr><td class="ps-3 fw-bold">MED</td><td>Mediano</td><td>12 – 25 kg</td></tr>
                    <tr><td class="ps-3 fw-bold">GRA</td><td>Grande</td><td>25 – 45 kg</td></tr>
                    <tr><td class="ps-3 fw-bold">GIG</td><td>Gigante</td><td>45+ kg</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>


      <!-- ============ SUB-TAB 6: REPORTES ESTADÍSTICOS ============ -->
      <div id="subtab-reportes-estadisticos" class="sub-tab-content">
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-white py-3">
            <h6 class="fw-bold m-0"><i class="bi bi-sliders me-2 text-primary"></i>Generador de reportes institucionales</h6>
          </div>
          <div class="card-body">
            <div class="row g-3 align-items-end">
              <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Tipo de reporte</label>
                <select class="form-select form-select-sm" id="estTipoReporte">
                  <option value="alcaldia">Concentrado por alcaldía</option>
                  <option value="raza">Concentrado por raza</option>
                  <option value="estatus">Concentrado por estatus</option>
                  <option value="mes">Comportamiento mensual</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Desde</label>
                <input type="date" class="form-control form-control-sm" id="estDesde" value="">
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Hasta</label>
                <input type="date" class="form-control form-control-sm" id="estHasta" value="">
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-sm w-100 text-white" style="background-color: var(--color-primary); border:none;" onclick="if(window.generarReporteEstadistico){generarReporteEstadistico();}return false;">
                  <i class="bi bi-play-fill me-1"></i> Generar
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm mb-4">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold m-0"><i class="bi bi-table me-2 text-primary"></i><span id="tituloReporteEst">Concentrado por alcaldía</span></h6>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-success" onclick="exportarReporteEstadistico()">
                <i class="bi bi-file-earmark-excel me-1"></i> CSV
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="descargarPDFEstadistico()">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="imprimirReporteEstadistico()">
                <i class="bi bi-printer me-1"></i> Imprimir
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0" id="tablaReporteEst">
                <thead class="bg-light"><tr></tr></thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
          <div class="card-footer bg-white small text-muted" id="pieReporteEst"></div>
        </div>

        <div class="card shadow-sm">
          <div class="card-header bg-white py-3">
            <h6 class="fw-bold m-0"><i class="bi bi-journal-text me-2 text-secondary"></i>Bitácora de acciones administrativas</h6>
          </div>
          <div class="card-body">
            <ul class="timeline" id="bitacoraGlobal"></ul>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- MODAL DE VISTA PREVIA DE EXPEDIENTE PDF -->
  <div class="modal fade" id="modalPDF" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-dark text-white py-2">
          <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Vista Previa de Documento Oficial (PDF)</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-4">
            <div class="col-md-7">
              <div class="pdf-preview" id="contenidoPdfModal"></div>
            </div>
            <div class="col-md-2 text-center border-start">
              <p class="small fw-semibold text-muted mb-2">Fotografía</p>
              <img id="pdfFotoMascota" src="" alt="Foto de la mascota" class="img-fluid rounded border mb-3" style="max-height:140px; object-fit:cover;">
              <p class="small fw-semibold text-muted mb-2">Código QR</p>
              <div id="qrContenedorPdf" class="d-flex flex-column justify-content-center align-items-center border rounded p-3 bg-white" style="min-height:200px;">
                <canvas id="qrCanvasPdf" width="180" height="180" class="bg-white"></canvas>
                <img id="qrImgPdf" alt="QR" width="180" height="180" class="border rounded bg-white" style="display:inline-block;">
                <div id="qrLabelPdf" class="small text-muted mt-2 text-center"></div>
              </div>
            </div>
            <div class="col-md-3 border-start">
              <p class="small fw-semibold text-muted mb-2"><i class="bi bi-geo-alt-fill me-1"></i>Ubicación GPS</p>
              <div id="gpsEstatusPdf" class="small mb-3"></div>
              <button type="button" class="btn btn-sm btn-outline-success w-100" id="btnValidarGpsPdf" onclick="validarGpsDesdeModal()">
                <i class="bi bi-patch-check me-1"></i> Marcar GPS como validado
              </button>
              <p class="text-muted mt-2 mb-0" style="font-size:.72rem;">El QR solo expone coordenadas validadas por un administrador.</p>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-danger btn-sm" id="btnDescargarPdfReal" onclick="descargarPDFReal()">
            <i class="bi bi-download me-1"></i> Descargar PDF
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast de Notificación -->
  <!-- ==================== MODAL: EXPEDIENTE ADMINISTRATIVO ==================== -->
  <div class="modal fade" id="modalExpediente" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header text-white" style="background-color: var(--color-primary);">
          <h5 class="modal-title fw-bold"><i class="bi bi-folder2-open me-2"></i>Expediente del reporte</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="cuerpoExpediente"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-outline-danger" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir expediente</button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container-custom" id="toastContainer"></div>

  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
  <script>
    let catalogoRazas = [];
    let catalogoAlcaldias = [];

    /** Carga catálogos reales desde API/BD (razas + alcaldías) — siempre refresca */
    async function cargarCatalogosDesdeBD(force) {
      let razas = [];
      let alcs = [];

      async function fetchJson(url) {
        if (window.RUPEAdmin && window.RUPEAdmin.api && typeof window.RUPEAdmin.api.get === 'function') {
          try {
            const res = await window.RUPEAdmin.api.get(url);
            return res && res.data !== undefined ? res.data : res;
          } catch (e) {
            console.warn('[catalogos] api', url, e);
          }
        }
        const r = await fetch(url, {
          credentials: 'same-origin',
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          cache: 'no-store'
        });
        const j = await r.json().catch(function () { return {}; });
        return j.data !== undefined ? j.data : j;
      }

      // 1) Siempre intentar API (recarga real)
      try {
        const rawR = await fetchJson('/api/admin/razas');
        if (Array.isArray(rawR)) razas = rawR;
        else if (rawR && Array.isArray(rawR.razas)) razas = rawR.razas;
        else if (rawR && Array.isArray(rawR.items)) razas = rawR.items;
      } catch (e) { console.warn('[catalogos] razas', e); }

      try {
        const rawA = await fetchJson('/api/admin/alcaldias');
        if (Array.isArray(rawA)) alcs = rawA;
        else if (rawA && Array.isArray(rawA.alcaldias)) alcs = rawA.alcaldias;
        else if (rawA && Array.isArray(rawA.items)) alcs = rawA.items;
      } catch (e) { console.warn('[catalogos] alcaldias', e); }

      // 2) Fallback PHP inject
      if (!razas.length) {
        if (Array.isArray(window.__RUPE_RAZAS__) && window.__RUPE_RAZAS__.length) razas = window.__RUPE_RAZAS__;
        else if (window.RUPE_ADMIN_INIT && Array.isArray(window.RUPE_ADMIN_INIT.razas)) razas = window.RUPE_ADMIN_INIT.razas;
      }
      if (!alcs.length) {
        if (Array.isArray(window.__RUPE_ALCALDIAS__) && window.__RUPE_ALCALDIAS__.length) alcs = window.__RUPE_ALCALDIAS__;
        else if (window.RUPE_ADMIN_INIT && Array.isArray(window.RUPE_ADMIN_INIT.alcaldias)) alcs = window.RUPE_ADMIN_INIT.alcaldias;
      }

      catalogoRazas = (razas || []).map(function (r) {
        const id = r.id_raza != null ? r.id_raza : r.id;
        return {
          codigo: r.codigo || ('RZ-' + String(id).padStart(2, '0')),
          nombre: r.nombre || '',
          especie: r.especie_label || r.especie || 'perro',
          tamano: (r.tamano && r.tamano !== '—' ? r.tamano : (r.mascota_tamano || r.tamaño || '—')),
          estado: (r.activo == 1 || r.activo === true || r.activo === '1' || r.estado === 'Activo') ? 'Activo' : 'Inactivo',
          id_raza: id,
        };
      });
      catalogoAlcaldias = (alcs || []).map(function (a) {
        const id = a.id_alcaldia != null ? a.id_alcaldia : a.id;
        const activo = (a.activo == 1 || a.activo === true || a.activo === '1' || a.estado === 'Activo');
        return {
          id: a.clave || a.codigo || ('CDMX-' + String(id).padStart(2, '0')),
          nombre: a.nombre || '',
          zona: a.zona || '—',
          cp: a.cp_base || a.cp || '—',
          casos: a.casos_registrados != null ? a.casos_registrados : (a.casos != null ? a.casos : 0),
          estado: activo ? 'Activo' : 'Inactivo',
          id_alcaldia: id,
        };
      });

      window.__RUPE_RAZAS__ = razas;
      window.__RUPE_ALCALDIAS__ = alcs;
      if (window.RUPE_ADMIN_INIT) {
        window.RUPE_ADMIN_INIT.razas = razas;
        window.RUPE_ADMIN_INIT.alcaldias = alcs;
      }

      poblarSelectoresCatalogo();
      return { razas: catalogoRazas, alcaldias: catalogoAlcaldias };
    }
    window.cargarCatalogosDesdeBD = cargarCatalogosDesdeBD;
    window.recargarCatalogoAlcaldias = function () {
      return cargarCatalogosDesdeBD(true);
    };


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
    function poblarSelectoresCatalogo() {
      // Preferir datos ya cargados de BD
      if ((!catalogoRazas || !catalogoRazas.length) && Array.isArray(window.__RUPE_RAZAS__)) {
        catalogoRazas = window.__RUPE_RAZAS__.map(function (r) {
          const id = r.id_raza != null ? r.id_raza : r.id;
          return {
            codigo: 'RZ-' + String(id).padStart(2, '0'),
            nombre: r.nombre || '',
            especie: r.especie_label || 'Canino',
            tamano: (r.tamano && r.tamano !== '—' ? r.tamano : (r.mascota_tamano || r.tamaño || '—')),
            estado: (r.activo == 1 || r.activo === true) ? 'Activo' : 'Inactivo',
          };
        });
      }
      if ((!catalogoAlcaldias || !catalogoAlcaldias.length) && Array.isArray(window.__RUPE_ALCALDIAS__)) {
        catalogoAlcaldias = window.__RUPE_ALCALDIAS__.map(function (a) {
          const id = a.id_alcaldia != null ? a.id_alcaldia : a.id;
          return {
            id: a.clave || ('CDMX-' + String(id).padStart(2, '0')),
            nombre: a.nombre || '',
            zona: a.zona || '—',
            estado: (a.activo == 1 || a.activo === true) ? 'Activo' : 'Inactivo',
          };
        });
      }

      const selAlcFiltro = document.getElementById('repAlcaldia');
      if (selAlcFiltro) {
        const valorPrevio = selAlcFiltro.value;
        selAlcFiltro.innerHTML = '<option value="">Todas</option>';
        (catalogoAlcaldias || []).filter(function (a) { return a.estado === 'Activo'; }).forEach(function (a) {
          selAlcFiltro.innerHTML += '<option value="' + a.nombre + '">' + a.nombre + '</option>';
        });
        selAlcFiltro.value = valorPrevio;
      }

      const tbodyRazas = document.querySelector('#catRazasInline tbody');
      if (tbodyRazas) {
        if (!(catalogoRazas && catalogoRazas.length)) {
          tbodyRazas.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin razas en la base de datos.</td></tr>';
        } else {
          tbodyRazas.innerHTML = catalogoRazas.map(function (r) {
            const badgeClass = r.estado === 'Activo' ? 'bg-success' : 'bg-secondary';
            return '<tr><td class="ps-3 fw-bold">' + (r.codigo || '') + '</td><td>' + (r.nombre || '') + '</td><td>' + (r.tamano || '—') + '</td><td><span class="badge ' + badgeClass + '">' + (r.estado || '') + '</span></td></tr>';
          }).join('');
        }
      }
      const cR = document.getElementById('contadorRazasCatalogo');
      if (cR) cR.textContent = (catalogoRazas ? catalogoRazas.length : 0) + ' registros';

      const tbodyAlc = document.querySelector('#catAlcaldiasInline tbody');
      if (tbodyAlc) {
        if (!(catalogoAlcaldias && catalogoAlcaldias.length)) {
          tbodyAlc.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin alcaldías en la base de datos.</td></tr>';
        } else {
          tbodyAlc.innerHTML = catalogoAlcaldias.map(function (a) {
            const badgeClass = a.estado === 'Activo' ? 'bg-success' : 'bg-secondary';
            const zona = (a.zona && a.zona !== '—') ? a.zona : '—';
            return '<tr><td class="ps-3 fw-bold">' + (a.id || '') + '</td><td>' + (a.nombre || '') + '</td><td>' + zona + '</td><td><span class="badge ' + badgeClass + '">' + (a.estado || '') + '</span></td></tr>';
          }).join('');
        }
      }
      const cA = document.getElementById('contadorAlcaldiasCatalogo');
      if (cA) cA.textContent = (catalogoAlcaldias ? catalogoAlcaldias.length : 0) + ' registros';
    }
    let modalPdfBs = null;
    let modalExpedienteBs = null;

    /** Normaliza foto_url de BD a ruta usable en el navegador */

    /** Carga imagen same-origin como dataURL (para PDF sin CORS) */
    function loadImageAsDataUrl(src) {
      return new Promise(function (resolve) {
        if (!src || typeof src !== 'string') { resolve(null); return; }
        // Ya es dataURL
        if (src.indexOf('data:image') === 0) { resolve(src); return; }
        // Fetch blob (misma origen)
        fetch(src, { credentials: 'same-origin', cache: 'no-cache' })
          .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.blob();
          })
          .then(function (blob) {
            return new Promise(function (res2) {
              var reader = new FileReader();
              reader.onloadend = function () { res2(reader.result); };
              reader.onerror = function () { res2(null); };
              reader.readAsDataURL(blob);
            });
          })
          .then(function (dataUrl) { resolve(dataUrl); })
          .catch(function () {
            // Fallback Image + canvas
            var img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function () {
              try {
                var c = document.createElement('canvas');
                c.width = img.naturalWidth || 200;
                c.height = img.naturalHeight || 200;
                c.getContext('2d').drawImage(img, 0, 0);
                resolve(c.toDataURL('image/jpeg', 0.88));
              } catch (e) { resolve(null); }
            };
            img.onerror = function () { resolve(null); };
            img.src = src + (src.indexOf('?') >= 0 ? '&' : '?') + '_=' + Date.now();
          });
      });
    }

    function normalizarFotoUrl(foto) {
      if (!foto || typeof foto !== 'string') return '';
      foto = foto.trim();
      if (!foto || foto === 'null' || foto === 'undefined') return '';
      if (foto.indexOf('data:') === 0 || foto.indexOf('http://') === 0 || foto.indexOf('https://') === 0) return foto;
      if (foto.charAt(0) === '/') return foto;
      if (foto.indexOf('assets/uploads/') === 0) return '/' + foto;
      if (foto.indexOf('uploads/') === 0) return '/assets/' + foto;
      // solo nombre de archivo
      if (foto.indexOf('/') === -1) return '/assets/uploads/' + foto;
      return '/' + foto.replace(/^\/+/, '');
    }

    function fotoSrcReporte(r) {
      if (!r) return (typeof FOTO_PLACEHOLDER_ADMIN !== 'undefined' ? FOTO_PLACEHOLDER_ADMIN : '');
      var f = normalizarFotoUrl(r.foto || r.foto_url || '');
      return f || (typeof FOTO_PLACEHOLDER_ADMIN !== 'undefined' ? FOTO_PLACEHOLDER_ADMIN : '');
    }

    function verDetallePDF(folio) {
      const listaPdf = (Array.isArray(reportesRUPE) && reportesRUPE.length)
        ? reportesRUPE
        : (Array.isArray(window.reportesRUPE) ? window.reportesRUPE : []);
      let r = listaPdf.find(x => String(x.folio) === String(folio));
      if (!r && Array.isArray(window.__RUPE_REPORTES__)) {
        const raw = window.__RUPE_REPORTES__.find(function (m) {
          return String(m.folio || '') === String(folio);
        });
        if (raw && typeof mapMascotaAReporte === 'function') r = mapMascotaAReporte(raw);
        else if (raw) r = {
          folio: raw.folio, mascota: raw.nombre || raw.mascota, raza: raw.raza_nombre || raw.raza,
          tamano: raw.tamano, color: raw.color, alcaldia: raw.alcaldia_nombre || raw.alcaldia,
          colonia: raw.colonia, estatus: raw.estatus, tutor: raw.tutor_nombre || raw.tutor,
          tel: raw.tutor_telefono || raw.telefono, chip: raw.microchip || raw.collar,
          obs: raw.senas_particulares, foto: normalizarFotoUrl(raw.foto_url || raw.foto || ''),
          qr_token: raw.qr_token, lat: raw.latitud, lng: raw.longitud,
          fecha: String(raw.created_at || raw.fecha_extravio || '').slice(0, 10),
          fecha_alta: String(raw.created_at || '').slice(0, 10),
          antiguedad_dias: raw.antiguedad_dias
        };
      }
      if (!r) {
        if (typeof mostrarToast === 'function') mostrarToast('No se encontró el folio ' + folio, 'warning');
        return;
      }

      // Foto real de BD
      r.foto = normalizarFotoUrl(r.foto || r.foto_url || '') || (typeof FOTO_PLACEHOLDER_ADMIN !== 'undefined' ? FOTO_PLACEHOLDER_ADMIN : '');

      const contenedor = document.getElementById('contenidoPdfModal');
      if (contenedor) {
        contenedor.innerHTML =
          '<div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">' +
          '<div><h5 class="fw-bold m-0 text-danger">REGISTRO ÚNICO DE PERROS EXTRAVIADOS (RUPE)</h5>' +
          '<small class="text-muted">EXPEDIENTE OFICIAL DE CASO</small></div>' +
          '<div class="text-end"><span class="fw-bold d-block">' + (r.folio || '') + '</span>' +
          '<small class="text-muted">' + (typeof fechaLarga === 'function' ? fechaLarga(r.fecha) : (r.fecha || '')) + '</small></div></div>' +
          '<div class="row g-3 mb-3"><div class="col-6">' +
          '<strong>Mascota:</strong> ' + (r.mascota || '') + '<br><strong>Raza:</strong> ' + (r.raza || '') + ' · ' + (r.tamano || '') +
          '<br><strong>Color:</strong> ' + (r.color || '') + '<br><strong>Alcaldía:</strong> ' + (r.alcaldia || '') +
          (r.colonia ? ', ' + r.colonia : '') + '</div><div class="col-6">' +
          '<strong>Estatus:</strong> ' + (r.estatus_bd || r.estatus || '') +
          '<br><strong>Tutor:</strong> ' + (r.tutor || '') + '<br><strong>Tel:</strong> ' + (r.tel || '') +
          '<br><strong>Microchip:</strong> ' + (r.chip || '—') + '</div></div>' +
          '<div class="p-3 bg-light rounded border mb-3"><strong>Señas:</strong> ' + (r.obs || 'Sin registro') + '</div>';
      }

      const imgFoto = document.getElementById('pdfFotoMascota');
      if (imgFoto) {
        imgFoto.src = r.foto;
        imgFoto.alt = r.mascota || 'Foto mascota';
        imgFoto.onerror = function () {
          this.onerror = null;
          this.src = (typeof FOTO_PLACEHOLDER_ADMIN !== 'undefined' ? FOTO_PLACEHOLDER_ADMIN : '');
        };
        // Preload dataURL para el PDF (fetch blob, sin CORS)
        window._pdfFotoDataUrl = null;
        if (typeof loadImageAsDataUrl === 'function' && r.foto) {
          loadImageAsDataUrl(r.foto).then(function (du) {
            if (du) window._pdfFotoDataUrl = du;
          });
        }
      }

      if (typeof renderizarQrYGpsPdf === 'function') renderizarQrYGpsPdf(r);
      window._pdfFolioActual = r.folio;
      window._pdfReporteActual = r;

      try {
        const mEl = document.getElementById('modalPDF');
        if (mEl && window.bootstrap) bootstrap.Modal.getOrCreateInstance(mEl).show();
        else if (typeof modalPdfBs !== 'undefined' && modalPdfBs) modalPdfBs.show();
      } catch (e) { console.warn(e); }
    }
    /** URL misma-origen del QR (proxy PHP → sin CORS en canvas/PDF) */
    function urlQrImagen(texto, size) {
      size = size || 200;
      return '/api/public/qr?size=' + size + '&data=' + encodeURIComponent(String(texto || 'RUPE'));
    }

    function generarQrDataUrl(texto, size) {
      size = size || 200;
      texto = String(texto || 'RUPE');
      return new Promise(function (resolve) {
        // 1) Librería QRCode local (mejor calidad, sin red)
        if (window.QRCode && typeof QRCode.toDataURL === 'function') {
          try {
            QRCode.toDataURL(texto, {
              width: size,
              margin: 2,
              color: { dark: '#6B1D2F', light: '#ffffff' }
            }, function (err, url) {
              if (!err && url) { resolve(url); return; }
              // 2) Proxy same-origin
              loadImageAsDataUrl('/api/public/qr?size=' + size + '&data=' + encodeURIComponent(texto))
                .then(resolve);
            });
            return;
          } catch (e) {}
        }
        // 2) Proxy PHP
        loadImageAsDataUrl('/api/public/qr?size=' + size + '&data=' + encodeURIComponent(texto))
          .then(resolve);
      });
    }

    function buildFichaUrl(r) {
      let url = (window.location.origin || '') + '/ficha?folio=' + encodeURIComponent(r.folio || '');
      if (r.qr_token) url += '&token=' + encodeURIComponent(String(r.qr_token));
      const lat = parseFloat(r.lat);
      const lng = parseFloat(r.lng);
      if (!isNaN(lat) && !isNaN(lng) && Math.abs(lat) > 0.01) {
        url += '&lat=' + lat.toFixed(6) + '&lng=' + lng.toFixed(6);
      }
      return url;
    }

    function renderizarQrYGpsPdf(r) {
      if (!r) return;
      var lat = typeof r.lat === 'number' ? r.lat : parseFloat(r.lat);
      var lng = typeof r.lng === 'number' ? r.lng : parseFloat(r.lng);
      var tieneGps = !isNaN(lat) && !isNaN(lng) && Math.abs(lat) > 0.01 && Math.abs(lng) > 0.01;
      if (tieneGps) { r.lat = lat; r.lng = lng; r.gpsValidado = true; }

      var urlVerificacion = (window.location.origin || '') + '/ficha?folio=' + encodeURIComponent(r.folio || '');
      if (r.qr_token) urlVerificacion += '&token=' + encodeURIComponent(String(r.qr_token));
      if (tieneGps) urlVerificacion += '&lat=' + lat.toFixed(6) + '&lng=' + lng.toFixed(6);
      if (typeof buildFichaUrl === 'function') {
        try { urlVerificacion = buildFichaUrl(r) || urlVerificacion; } catch (_) {}
      }
      window._pdfUrlQr = urlVerificacion;
      window._pdfFolioActual = r.folio;
      window._pdfQrDataUrl = null;

      var canvas = document.getElementById('qrCanvasPdf');
      var img = document.getElementById('qrImgPdf');
      var label = document.getElementById('qrLabelPdf');
      if (label) {
        label.innerHTML = '<strong>' + (r.folio || '') + '</strong><br><span class="text-muted" style="font-size:.65rem;word-break:break-all;">' + urlVerificacion + '</span>';
      }

      // Mostrar QR: preferir img visible
      if (canvas) canvas.classList.add('d-none');
      if (img) {
        img.classList.remove('d-none');
        img.style.display = 'inline-block';
        img.alt = 'QR ' + (r.folio || '');
      }

      generarQrDataUrl(urlVerificacion, 200).then(function (dataUrl) {
        if (!dataUrl) {
          // último recurso: src directo al proxy
          if (img) img.src = '/api/public/qr?size=180&data=' + encodeURIComponent(urlVerificacion) + '&t=' + Date.now();
          return;
        }
        window._pdfQrDataUrl = dataUrl;
        if (img) {
          img.src = dataUrl;
          img.classList.remove('d-none');
          img.style.display = 'inline-block';
        }
        if (canvas) {
          try {
            var ctx = canvas.getContext('2d');
            var im = new Image();
            im.onload = function () {
              ctx.clearRect(0, 0, canvas.width, canvas.height);
              ctx.drawImage(im, 0, 0, canvas.width, canvas.height);
            };
            im.src = dataUrl;
          } catch (_) {}
        }
      });

      var gpsDiv = document.getElementById('gpsEstatusPdf');
      var btnValidar = document.getElementById('btnValidarGpsPdf');
      if (gpsDiv) {
        if (!tieneGps) {
          gpsDiv.innerHTML = '<span class="badge bg-secondary"><i class="bi bi-geo-alt"></i> Sin GPS</span>'
            + '<p class="small text-muted mt-2 mb-0">Sin coordenadas en este folio (BD).</p>';
          if (btnValidar) btnValidar.classList.add('d-none');
        } else {
          gpsDiv.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> GPS</span>'
            + '<p class="small mb-1 mt-2"><code>' + lat.toFixed(5) + ', ' + lng.toFixed(5) + '</code></p>'
            + '<a class="small" target="_blank" rel="noopener" href="https://www.openstreetmap.org/?mlat=' + lat + '&mlon=' + lng + '#map=16/' + lat + '/' + lng + '">Abrir mapa</a>';
          if (btnValidar) btnValidar.classList.add('d-none');
        }
      }
    }

    function validarGpsDesdeModal() {
      const r = reportesRUPE.find(x => x.folio === window._pdfFolioActual);
      if (!r) return;
      r.gpsValidado = true;
      registrarBitacora('Validación GPS', `Coordenadas del folio ${r.folio} validadas y habilitadas en el QR oficial.`);
      renderizarQrYGpsPdf(r);
    }
    function descargarPDFReal() {
      var lista = (Array.isArray(window.reportesRUPE) && window.reportesRUPE.length)
        ? window.reportesRUPE
        : (Array.isArray(reportesRUPE) ? reportesRUPE : []);
      var r = lista.find(function (x) { return String(x.folio) === String(window._pdfFolioActual); });
      if (!r) r = window._pdfReporteActual;
      if (!r) {
        if (typeof mostrarToast === 'function') mostrarToast('No hay expediente seleccionado.', 'warning');
        return;
      }
      if (!window.jspdf || !window.jspdf.jsPDF) {
        if (typeof mostrarToast === 'function') mostrarToast('jsPDF no está cargado. Recarga la página.', 'error');
        return;
      }
      var { jsPDF } = window.jspdf;
      var btn = document.getElementById('btnDescargarPdfReal');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generando…';
      }
      function finishBtn() {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-download me-1"></i> Descargar PDF';
        }
      }

      (async function () {
        try {
          var fotoSrc = (typeof normalizarFotoUrl === 'function')
            ? normalizarFotoUrl(r.foto || r.foto_url || '')
            : (r.foto || r.foto_url || '');
          var urlQr = window._pdfUrlQr || (
            (window.location.origin || '') + '/ficha?folio=' + encodeURIComponent(r.folio || '')
            + (r.qr_token ? '&token=' + encodeURIComponent(r.qr_token) : '')
          );

          var fotoData = window._pdfFotoDataUrl || null;
          if (!fotoData && fotoSrc) {
            fotoData = await loadImageAsDataUrl(fotoSrc);
            window._pdfFotoDataUrl = fotoData;
          }

          var qrData = window._pdfQrDataUrl || null;
          if (!qrData) {
            qrData = await generarQrDataUrl(urlQr, 220);
            window._pdfQrDataUrl = qrData;
          }
          if (!qrData) {
            qrData = await loadImageAsDataUrl('/api/public/qr?size=220&data=' + encodeURIComponent(urlQr));
            window._pdfQrDataUrl = qrData;
          }

          // PDF carta — UNA SOLA HOJA (compacto)
          var doc = new jsPDF({ unit: 'pt', format: 'letter', compress: true });
          var pageW = doc.internal.pageSize.getWidth();   // ~612
          var pageH = doc.internal.pageSize.getHeight();  // ~792
          var margin = 36;
          var maxY = pageH - 36; // no pasar de aquí (1 página)

          // Encabezado
          doc.setFillColor(107, 29, 47);
          doc.rect(0, 0, pageW, 52, 'F');
          doc.setTextColor(255, 255, 255);
          doc.setFontSize(13);
          doc.setFont(undefined, 'bold');
          doc.text('RUPE CDMX — Expediente del reporte', margin, 24);
          doc.setFontSize(9);
          doc.setFont(undefined, 'normal');
          doc.text('Registro Único de Perros Extraviados · Documento oficial (1 hoja)', margin, 40);

          // Foto + QR (fila superior, tamaño controlado)
          var imgY = 64;
          var imgSize = 88;
          if (fotoData) {
            try {
              var fmtF = fotoData.indexOf('image/png') >= 0 ? 'PNG' : 'JPEG';
              doc.addImage(fotoData, fmtF, margin, imgY, imgSize, imgSize);
            } catch (e) {
              doc.setDrawColor(200, 200, 200);
              doc.rect(margin, imgY, imgSize, imgSize);
              doc.setFontSize(8);
              doc.setTextColor(150, 150, 150);
              doc.text('Sin foto', margin + 28, imgY + 48);
            }
          } else {
            doc.setDrawColor(200, 200, 200);
            doc.rect(margin, imgY, imgSize, imgSize);
            doc.setFontSize(8);
            doc.setTextColor(150, 150, 150);
            doc.text('Sin foto', margin + 28, imgY + 48);
          }
          if (qrData) {
            try {
              var fmtQ = qrData.indexOf('image/jpeg') >= 0 ? 'JPEG' : 'PNG';
              doc.addImage(qrData, fmtQ, pageW - margin - imgSize, imgY, imgSize, imgSize);
              doc.setFontSize(7);
              doc.setTextColor(100, 100, 100);
              doc.text('Escanear QR de ficha', pageW - margin - imgSize + 8, imgY + imgSize + 12);
            } catch (e) {
              doc.rect(pageW - margin - imgSize, imgY, imgSize, imgSize);
            }
          } else {
            doc.setDrawColor(180, 180, 180);
            doc.rect(pageW - margin - imgSize, imgY, imgSize, imgSize);
            doc.setFontSize(8);
            doc.setTextColor(150, 150, 150);
            doc.text('Sin QR', pageW - margin - imgSize + 28, imgY + 48);
          }

          // Datos compactos (columna central-izquierda, sin desbordar)
          doc.setTextColor(20, 20, 20);
          doc.setFontSize(9);
          var y = imgY;
          var labelX = margin + imgSize + 14;
          var valueX = labelX + 78;
          var valueW = pageW - margin - imgSize - 14 - valueX - 8; // espacio antes del QR

          function linea(et, val) {
            if (y > maxY - 40) return;
            var v = String(val == null || val === '' ? '—' : val);
            // truncar líneas largas a 2 renglones máx
            var lines = doc.splitTextToSize(v, valueW);
            if (lines.length > 2) lines = lines.slice(0, 2);
            doc.setFont(undefined, 'bold');
            doc.setTextColor(80, 80, 80);
            doc.text(et, labelX, y + 10);
            doc.setFont(undefined, 'normal');
            doc.setTextColor(20, 20, 20);
            doc.text(lines, valueX, y + 10);
            y += 12 * Math.max(1, lines.length) + 4;
          }

          var dias = r.antiguedad_dias != null ? r.antiguedad_dias : 0;
          var _lat = parseFloat(r.lat), _lng = parseFloat(r.lng);
          var gpsTxt = (!isNaN(_lat) && !isNaN(_lng))
            ? (_lat.toFixed(5) + ', ' + _lng.toFixed(5))
            : 'Sin coordenadas';

          linea('Folio', r.folio || '—');
          linea('Mascota', (r.mascota || '—') + (r.raza ? ' · ' + r.raza : '') + (r.tamano && r.tamano !== '—' ? ' (' + r.tamano + ')' : ''));
          linea('Color', r.color || '—');
          linea('Tutor', (r.tutor || '—') + (r.tel ? ' · ' + r.tel : ''));
          linea('Alcaldía', (r.alcaldia || '—') + (r.colonia ? ', ' + r.colonia : ''));
          linea('Estatus', r.estatus_bd || r.estatus || '—');
          linea('Fecha', r.fecha_alta || r.fecha || r.fecha_extravio || '—');
          linea('Antigüedad', dias + ' días');
          linea('Collar / Chip', (r.collar || '—') + ' / ' + (r.chip || r.microchip || '—'));
          linea('GPS', gpsTxt);

          // Señas (bloque inferior, máximo ~4 líneas)
          y = Math.max(y, imgY + imgSize + 20);
          if (y < maxY - 80) {
            doc.setDrawColor(220, 220, 220);
            doc.line(margin, y, pageW - margin, y);
            y += 14;
            doc.setFont(undefined, 'bold');
            doc.setFontSize(9);
            doc.setTextColor(80, 80, 80);
            doc.text('Señas particulares', margin, y);
            y += 12;
            doc.setFont(undefined, 'normal');
            doc.setTextColor(20, 20, 20);
            var obsTxt = String(r.obs || r.senas_particulares || 'Sin registro').trim() || 'Sin registro';
            var obs = doc.splitTextToSize(obsTxt, pageW - margin * 2);
            if (obs.length > 4) obs = obs.slice(0, 4);
            doc.text(obs, margin, y);
            y += 11 * obs.length + 8;
          }

          // Pie fijo al final de la única hoja
          doc.setFontSize(7);
          doc.setTextColor(120, 120, 120);
          doc.setDrawColor(220, 220, 220);
          doc.line(margin, maxY - 22, pageW - margin, maxY - 22);
          doc.text('Sistema RUPE CDMX · Generado ' + new Date().toLocaleString('es-MX') + ' · Página 1 de 1', margin, maxY - 10);
          var urlShort = String(urlQr || '').length > 90 ? String(urlQr).slice(0, 87) + '…' : String(urlQr || '');
          doc.text(urlShort, margin, maxY);

          // No llamar addPage: forzar una sola hoja
          doc.save('RUPE_' + String(r.folio || 'expediente').replace(/[^a-zA-Z0-9_-]/g, '_') + '.pdf');
          if (typeof mostrarToast === 'function') mostrarToast('PDF del expediente (1 hoja) descargado', 'success');
        } catch (e) {
          console.warn(e);
          if (typeof mostrarToast === 'function') mostrarToast('Error al generar PDF: ' + (e.message || e), 'error');
        } finally {
          finishBtn();
        }
      })();
    }
    window.descargarPDFReal = descargarPDFReal;
    window.renderizarQrYGpsPdf = renderizarQrYGpsPdf;
    window.generarQrDataUrl = generarQrDataUrl;
    window.loadImageAsDataUrl = loadImageAsDataUrl;
    window.normalizarFotoUrl = normalizarFotoUrl;



    const HOY = new Date();
    /** Solo datos reales de mascotas (reportes de tutores) — sin mocks */
    let reportesRUPE = [];

    function mapMascotaAReporte(m) {
      if (!m || typeof m !== 'object') return null;
      const estatusRaw = String(m.estatus || 'En Búsqueda').trim();
      let estatus = estatusRaw;
      if (/búsqueda|busqueda|pendiente/i.test(estatusRaw)) estatus = 'Pendiente';
      else if (/resguardo/i.test(estatusRaw)) estatus = 'En Seguimiento';
      else if (/localizado|encontrado|resuelto/i.test(estatusRaw)) estatus = 'Resuelto';
      else if (/validado/i.test(estatusRaw)) estatus = 'Validado';
      else if (/rechaz/i.test(estatusRaw)) estatus = 'Rechazado';
      else estatus = 'Pendiente';
      const especie = String(m.especie || 'perro').toLowerCase();
      const tipo = /resguardo/i.test(estatusRaw) ? 'En Resguardo' : 'Extraviado';
      let foto = '';
      if (typeof normalizarFotoUrl === 'function') {
        foto = normalizarFotoUrl(m.foto_url || m.foto || m.foto_src || '');
      } else {
        foto = m.foto_url || m.foto || m.foto_src || '';
        if (foto && foto.indexOf('http') !== 0 && foto.charAt(0) !== '/') {
          if (foto.indexOf('assets/') === 0) foto = '/' + foto;
          else if (foto.indexOf('/') === -1) foto = '/assets/uploads/' + foto;
          else foto = '/' + foto;
        }
      }
      // Fecha extravío (dato del tutor) y fecha de alta del registro en BD
      let fechaEx = m.fecha_extravio;
      if (fechaEx == null || fechaEx === '' || fechaEx === '0000-00-00' || fechaEx === 'null') {
        fechaEx = '';
      }
      const fechaExtravio = String(fechaEx).replace('T', ' ').slice(0, 10);
      const fechaAlta = String(m.created_at || '').replace('T', ' ').slice(0, 10);
      // Antigüedad = días desde el REGISTRO (created_at) en el sistema
      const fecha = fechaAlta || fechaExtravio;
      let diasBd = m.antiguedad_dias != null ? parseInt(m.antiguedad_dias, 10) : NaN;
      if (isNaN(diasBd) || diasBd < 0) diasBd = null;
      const latN = parseFloat(m.latitud != null ? m.latitud : m.lat);
      const lngN = parseFloat(m.longitud != null ? m.longitud : m.lng);
      const hasGps = !isNaN(latN) && !isNaN(lngN) && Math.abs(latN) > 0.01 && Math.abs(lngN) > 0.01;
      return {
        id_mascota: m.id_mascota || m.id || 0,
        folio: String(m.folio || ''),
        mascota: m.nombre || m.mascota || 'Sin nombre',
        raza: m.raza_nombre || m.raza || '—',
        tamano: (m.tamano || m.tamaño || '').trim() || '—',
        color: m.color || '—',
        alcaldia: m.alcaldia_nombre || m.alcaldia || '—',
        colonia: m.colonia || '',
        tipo: tipo,
        estatus: estatus,
        estatus_bd: estatusRaw,
        fecha: fecha,
        fecha_extravio: fechaExtravio || fechaAlta,
        fecha_alta: fechaAlta,
        fechaCierre: m.fecha_cierre || '',
        tutor: m.tutor_nombre || m.tutor || '—',
        tel: m.tutor_telefono || m.telefono || m.tutor_correo || '',
        chip: m.microchip || m.chip || m.collar || '',
        obs: m.senas_particulares || m.notas || '',
        foto: foto,
        lat: hasGps ? latN : null,
        lng: hasGps ? lngN : null,
        gpsValidado: hasGps,
        especie: especie,
        qr_token: m.qr_token || '',
        antiguedad_dias: diasBd,
      };
    }

    function diasTranscurridos(fechaIso, diasPrecalc) {
      // Preferir días calculados en MySQL desde created_at (registro)
      if (diasPrecalc != null && diasPrecalc !== '' && !isNaN(Number(diasPrecalc))) {
        const n = parseInt(diasPrecalc, 10);
        return n < 0 ? 0 : n;
      }
      if (fechaIso && typeof fechaIso === 'object') {
        const r = fechaIso;
        if (r.antiguedad_dias != null && !isNaN(Number(r.antiguedad_dias))) {
          const n = parseInt(r.antiguedad_dias, 10);
          return n < 0 ? 0 : n;
        }
        // Días desde alta del registro
        fechaIso = r.fecha_alta || r.fecha || r.fecha_extravio || '';
      }
      if (!fechaIso) return 0;
      const raw = String(fechaIso).trim();
      const m = raw.match(/(\d{4})-(\d{2})-(\d{2})/);
      let inicio;
      if (m) {
        inicio = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
      } else {
        inicio = new Date(raw);
      }
      if (isNaN(inicio.getTime())) return 0;
      const hoy = new Date();
      hoy.setHours(0, 0, 0, 0);
      inicio.setHours(0, 0, 0, 0);
      const d = Math.floor((hoy.getTime() - inicio.getTime()) / 86400000);
      return d < 0 ? 0 : d;
    }

    function claseSemaforo(dias) {
      if (dias <= 7) return 'semaforo-verde';
      if (dias <= 15) return 'semaforo-amarillo';
      return 'semaforo-rojo';
    }

    function fechaLarga(fechaIso) {
      if (!fechaIso || typeof fechaIso !== 'string' || fechaIso.length < 8) return '—';
      const parts = String(fechaIso).slice(0, 10).split('-');
      if (parts.length < 3) return String(fechaIso);
      return parts[2] + '/' + parts[1] + '/' + parts[0];
    }

    function cargarReportesDesdeBD(lista) {
      const arr = Array.isArray(lista) ? lista : [];
      reportesRUPE = arr.map(mapMascotaAReporte).filter(Boolean);
      window.reportesRUPE = reportesRUPE;
      window.__RUPE_REPORTES__ = arr;
      try { if (typeof renderizarTablaReportes === 'function') renderizarTablaReportes(); } catch (e) { console.warn('tabla', e); }
      try { if (typeof renderizarValidacion === 'function') renderizarValidacion(); } catch (e) { console.warn('validacion', e); }
      try { if (typeof renderizarPanelReportes === 'function') renderizarPanelReportes(); } catch (e) {}
      try { if (typeof generarReporteEstadistico === 'function') generarReporteEstadistico(); } catch (e) {}
      try { if (typeof renderizarCoincidencias === 'function') renderizarCoincidencias(); } catch (e) {}
      return reportesRUPE;
    }
    window.cargarReportesDesdeBD = cargarReportesDesdeBD;
    window.reportesRUPE = reportesRUPE;

    let bitacoraReportes = [];
    // Admin en sesión (evita ReferenceError adminActual is not defined)
    var adminActual = window.adminActual || {
      nombre: (window.__RUPE_ADMIN_USER__ && (window.__RUPE_ADMIN_USER__.nombre || window.__RUPE_ADMIN_USER__.correo))
        || (window.__RUPE_USER__ && (window.__RUPE_USER__.nombre || window.__RUPE_USER__.correo))
        || (window.RUPE_ADMIN_INIT && window.RUPE_ADMIN_INIT.user && (window.RUPE_ADMIN_INIT.user.nombre || window.RUPE_ADMIN_INIT.user.correo))
        || 'Administrador'
    };
    window.adminActual = adminActual;
    const ESTATUS_META = {
      'Pendiente':      { badge: 'bg-warning text-dark', icono: 'bi-hourglass-split' },
      'Validado':       { badge: 'bg-primary',           icono: 'bi-patch-check' },
      'En Seguimiento': { badge: 'bg-info text-dark',    icono: 'bi-search' },
      'Resuelto':       { badge: 'bg-success',           icono: 'bi-check2-circle' },
      'Rechazado':      { badge: 'bg-secondary',         icono: 'bi-x-circle' }
    };
    const TIPO_BADGE = {
      'Extraviado': 'bg-danger',
      'Encontrado': 'bg-success',
      'En Resguardo': 'bg-warning text-dark'
    };
    let ordenarPorPrioridad = false;
    function registrarBitacora(accion, detalle) {
      try {
        if (!window.adminActual || typeof window.adminActual !== 'object') {
          window.adminActual = { nombre: 'Administrador' };
        }
        if (!window.adminActual.nombre) window.adminActual.nombre = 'Administrador';
        const ahora = new Date();
        const sello = String(ahora.getDate()).padStart(2,'0') + '/' + String(ahora.getMonth()+1).padStart(2,'0') + '/' + ahora.getFullYear() + ' ' + String(ahora.getHours()).padStart(2,'0') + ':' + String(ahora.getMinutes()).padStart(2,'0');
        if (!Array.isArray(bitacoraReportes)) bitacoraReportes = [];
        bitacoraReportes.unshift({
          fecha: sello,
          usuario: window.adminActual.nombre,
          accion: accion || '',
          detalle: detalle || ''
        });
        if (typeof renderizarBitacora === 'function') renderizarBitacora();
      } catch (e) {
        console.warn('registrarBitacora', e);
      }
    }
    window.registrarBitacora = registrarBitacora;
    function refrescarModuloReportes() {
      renderizarPanelReportes();
      renderizarTablaReportes();
      renderizarValidacion();
      renderizarCoincidencias();
      renderizarBitacora();
    }
    let chartTiposInstancia = null;
    let chartAlcaldiasInstancia = null;
    const COLOR_GUINDA = '#6B1D2F';
    function renderizarGraficaPastel(idCanvas, etiquetas, valores, colores) {
      const canvas = document.getElementById(idCanvas);
      if (!canvas || typeof Chart === 'undefined') return;
      if (chartTiposInstancia) chartTiposInstancia.destroy();
      chartTiposInstancia = new Chart(canvas.getContext('2d'), {
        type: 'pie',
        data: {
          labels: etiquetas,
          datasets: [{ data: valores, backgroundColor: colores, borderColor: '#fff', borderWidth: 2 }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.parsed} casos` } }
          }
        }
      });
    }
    function renderizarGraficaBarras(idCanvas, etiquetas, valores) {
      const canvas = document.getElementById(idCanvas);
      if (!canvas || typeof Chart === 'undefined') return;
      if (chartAlcaldiasInstancia) chartAlcaldiasInstancia.destroy();
      chartAlcaldiasInstancia = new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
          labels: etiquetas,
          datasets: [{ label: 'Casos', data: valores, backgroundColor: COLOR_GUINDA, borderRadius: 6, maxBarThickness: 42 }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => `${ctx.parsed.y} caso(s)` } } },
          scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
      });
    }
    function _esResueltoPanel(r) {
      const e = String(r.estatus || '');
      const bd = String(r.estatus_bd || '');
      return /resuelto|localizado/i.test(e) || /localizado|resuelto/i.test(bd);
    }
    function _esAbiertoPanel(r) {
      return !_esResueltoPanel(r) && String(r.estatus || '') !== 'Rechazado';
    }

    /**
     * Panel de Control — solo KPIs y gráficas desde reportes reales (mascotas + JOINs).
     */
    function renderizarPanelReportes() {
      const lista = (Array.isArray(reportesRUPE) && reportesRUPE.length)
        ? reportesRUPE
        : (Array.isArray(window.reportesRUPE) ? window.reportesRUPE : []);
      const total = lista.length;
      const abiertos = lista.filter(_esAbiertoPanel).length;
      const resueltos = lista.filter(_esResueltoPanel);
      const tasa = total ? Math.round((resueltos.length / total) * 100) : 0;

      let promDias = 0;
      if (resueltos.length) {
        const suma = resueltos.reduce(function (acc, r) {
          try {
            const ini = r.fecha ? new Date(r.fecha) : null;
            const fin = r.fechaCierre ? new Date(r.fechaCierre) : new Date();
            if (!ini || isNaN(ini.getTime())) return acc;
            return acc + Math.max(1, Math.round((fin - ini) / 86400000));
          } catch (_) {
            return acc;
          }
        }, 0);
        promDias = (suma / resueltos.length).toFixed(1);
      }

      const setTxt = function (id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = String(val);
      };
      setTxt('kpiTotal', total);
      setTxt('kpiAbiertos', abiertos);
      setTxt('kpiTasa', tasa + '%');
      setTxt('kpiDias', promDias);

      // Distribución por estatus BD (más fiel que "tipo" demo)
      const porEstatus = {};
      lista.forEach(function (r) {
        const k = r.estatus_bd || r.estatus || 'Sin estatus';
        porEstatus[k] = (porEstatus[k] || 0) + 1;
      });
      const estOrden = Object.keys(porEstatus).sort(function (a, b) { return porEstatus[b] - porEstatus[a]; });
      const COLOR_EST = {
        'En Búsqueda': '#ffc107',
        'En Resguardo': '#0dcaf0',
        'Localizado': '#198754',
        'Pendiente': '#ffc107',
        'En Seguimiento': '#0dcaf0',
        'Resuelto': '#198754',
        'Extraviado': '#dc3545'
      };
      // Fallback: también por tipo si hay variedad
      const porTipo = {};
      lista.forEach(function (r) {
        const k = r.tipo || 'Extraviado';
        porTipo[k] = (porTipo[k] || 0) + 1;
      });
      const tiposOrdenados = Object.keys(porTipo).sort(function (a, b) { return porTipo[b] - porTipo[a]; });
      const COLOR_TIPO = { 'Extraviado': '#dc3545', 'Encontrado': '#198754', 'En Resguardo': '#ffc107' };

      const labelsPie = estOrden.length ? estOrden : tiposOrdenados;
      const valsPie = estOrden.length ? estOrden.map(function (t) { return porEstatus[t]; }) : tiposOrdenados.map(function (t) { return porTipo[t]; });
      const colsPie = labelsPie.map(function (t) { return COLOR_EST[t] || COLOR_TIPO[t] || '#6c757d'; });

      try {
        renderizarGraficaPastel('graficaTipos', labelsPie, valsPie, colsPie);
      } catch (e) { console.warn(e); }

      const contTipos = document.getElementById('distTipos');
      if (contTipos) {
        if (!total) {
          contTipos.innerHTML = '<div class="text-muted small">Sin reportes en la base de datos.</div>';
        } else {
          contTipos.innerHTML = labelsPie.map(function (t, i) {
            const n = valsPie[i];
            const pct = total ? Math.round((n / total) * 100) : 0;
            const col = colsPie[i];
            return '<div class="d-flex justify-content-between align-items-center small mb-1">'
              + '<span><span class="leyenda-punto" style="background:' + col + '"></span>' + t + '</span>'
              + '<span class="text-muted">' + n + ' casos · ' + pct + '%</span></div>';
          }).join('');
        }
      }

      // Top alcaldías reales
      const porAlc = {};
      lista.forEach(function (r) {
        const k = r.alcaldia || 'Sin alcaldía';
        porAlc[k] = (porAlc[k] || 0) + 1;
      });
      const rankAlc = Object.keys(porAlc).sort(function (a, b) { return porAlc[b] - porAlc[a]; }).slice(0, 8);

      try {
        renderizarGraficaBarras('graficaAlcaldias', rankAlc, rankAlc.map(function (a) { return porAlc[a]; }));
      } catch (e) { console.warn(e); }

      const contAlc = document.getElementById('distAlcaldias');
      if (contAlc) {
        if (!rankAlc.length) {
          contAlc.innerHTML = '<div class="text-muted small">Sin datos de alcaldía.</div>';
        } else {
          contAlc.innerHTML = rankAlc.map(function (a) {
            const n = porAlc[a];
            const pct = total ? Math.round((n / total) * 100) : 0;
            return '<div class="d-flex justify-content-between align-items-center small mb-1">'
              + '<span class="text-truncate me-2">' + a + '</span>'
              + '<span class="text-muted text-nowrap">' + n + ' · ' + pct + '%</span></div>';
          }).join('');
        }
      }

      // Top razas: Raza | Tamaño | Casos | % del total
      // Tamaño: mascota → catálogo razas (API) → moda de mascotas
      const mapaTamRaza = {};
      const fuentesCat = []
        .concat(Array.isArray(catalogoRazas) ? catalogoRazas : [])
        .concat(Array.isArray(window.__RUPE_RAZAS__) ? window.__RUPE_RAZAS__ : [])
        .concat((window.RUPE_ADMIN_INIT && Array.isArray(window.RUPE_ADMIN_INIT.razas)) ? window.RUPE_ADMIN_INIT.razas : []);
      fuentesCat.forEach(function (rz) {
        const n = String(rz.nombre || '').trim().toLowerCase();
        const t = String(rz.tamano || rz.tamaño || '').trim();
        if (n && t && t !== '—') mapaTamRaza[n] = t;
      });

      const porRaza = {};
      lista.forEach(function (r) {
        const nombre = String(r.raza || r.raza_nombre || 'Sin raza').trim() || 'Sin raza';
        let tam = String(r.tamano || r.tamaño || '').trim();
        if (!tam || tam === '—') {
          tam = mapaTamRaza[nombre.toLowerCase()] || '';
        }
        if (!tam) tam = '—';
        if (!porRaza[nombre]) {
          porRaza[nombre] = { casos: 0, tamanos: {} };
        }
        porRaza[nombre].casos += 1;
        porRaza[nombre].tamanos[tam] = (porRaza[nombre].tamanos[tam] || 0) + 1;
      });
      const rankRaza = Object.keys(porRaza).sort(function (a, b) {
        return porRaza[b].casos - porRaza[a].casos;
      }).slice(0, 10);
      const tbodyRazas = document.getElementById('tbodyTopRazas');
      if (tbodyRazas) {
        if (!rankRaza.length || !total) {
          tbodyRazas.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin razas reportadas en la base de datos.</td></tr>';
        } else {
          tbodyRazas.innerHTML = rankRaza.map(function (nombre) {
            const info = porRaza[nombre];
            let tamTop = '—';
            let maxT = 0;
            Object.keys(info.tamanos).forEach(function (t) {
              if (t === '—') return;
              if (info.tamanos[t] > maxT) {
                maxT = info.tamanos[t];
                tamTop = t;
              }
            });
            if (tamTop === '—') {
              tamTop = mapaTamRaza[nombre.toLowerCase()] || '—';
            }
            const pct = total ? ((info.casos / total) * 100).toFixed(1) : '0.0';
            return '<tr>'
              + '<td class="ps-3 fw-semibold">' + nombre + '</td>'
              + '<td><span class="badge bg-light text-dark border">' + tamTop + '</span></td>'
              + '<td class="text-center"><strong>' + info.casos + '</strong></td>'
              + '<td class="text-end pe-3">'
              + '<div class="d-flex align-items-center justify-content-end gap-2">'
              + '<div class="progress" style="width:64px;height:6px">'
              + '<div class="progress-bar bg-primary" style="width:' + pct + '%"></div></div>'
              + '<span class="small text-muted">' + pct + '%</span></div></td></tr>';
          }).join('');
        }
      }

      renderizarAlertas();
    }

    function renderizarAlertas() {
      const cont = document.getElementById('listaAlertas') || document.getElementById('contenedorAlertas');
      if (!cont) return;
      const lista = (Array.isArray(reportesRUPE) && reportesRUPE.length)
        ? reportesRUPE
        : (Array.isArray(window.reportesRUPE) ? window.reportesRUPE : []);
      const pendientes = lista.filter(function (r) {
        return typeof esPendienteValidacion === 'function' ? esPendienteValidacion(r) : String(r.estatus) === 'Pendiente';
      });
      const rezagados = lista.filter(function (r) {
        return _esAbiertoPanel(r) && diasTranscurridos(r.fecha) > 15;
      });
      const sinChip = lista.filter(function (r) {
        const c = String(r.chip || '');
        return !c || /sin|no registrado|^—$|^-$/i.test(c);
      });
      const sinFoto = lista.filter(function (r) {
        const f = String(r.foto || r.foto_url || '').trim();
        return !f || f === 'null' || f === 'undefined' || /placeholder/i.test(f);
      });
      function tieneGpsValido(r) {
        const lat = parseFloat(r.lat != null ? r.lat : r.latitud);
        const lng = parseFloat(r.lng != null ? r.lng : r.longitud);
        return !isNaN(lat) && !isNaN(lng)
          && Math.abs(lat) > 0.01 && Math.abs(lng) > 0.01
          && Math.abs(lat) <= 90 && Math.abs(lng) <= 180;
      }
      const conGps = lista.filter(tieneGpsValido);
      const sinGps = lista.filter(function (r) { return !tieneGpsValido(r); });

      const alertas = [];
      if (!lista.length) {
        alertas.push({
          icono: 'bi-database',
          clase: 'border-secondary',
          titulo: 'Sin reportes de tutores en la base de datos',
          texto: 'Cuando los tutores registren mascotas, los KPIs y gráficas se actualizarán solos.'
        });
      } else {
        alertas.push({
          icono: 'bi-hourglass-split',
          clase: 'border-warning',
          titulo: pendientes.length + ' reporte(s) en búsqueda / pendientes de validación',
          texto: 'Revísalos en la Bandeja de Validación.'
        });
        alertas.push({
          icono: 'bi-exclamation-triangle',
          clase: 'border-danger',
          titulo: rezagados.length + ' caso(s) abiertos con más de 15 días',
          texto: 'Prioriza seguimiento con el tutor o brigada.'
        });
        alertas.push({
          icono: 'bi-cpu',
          clase: 'border-secondary',
          titulo: sinChip.length + ' registro(s) sin microchip',
          texto: 'Dato opcional del formulario de registro del tutor.'
        });
        alertas.push({
          icono: 'bi-image',
          clase: 'border-info',
          titulo: sinFoto.length + ' sin fotografía',
          texto: 'La ficha ciudadana se beneficia de una foto del canino.'
        });
        alertas.push({
          icono: 'bi-geo-alt-fill',
          clase: conGps.length ? 'border-success' : 'border-primary',
          titulo: conGps.length + ' con GPS · ' + sinGps.length + ' sin coordenadas GPS',
          texto: conGps.length
            ? ('Hay ' + conGps.length + ' folio(s) con lat/lng válidos en la BD. Los demás se capturan en GPS/QR del tutor.')
            : 'Ningún reporte tiene latitud/longitud en la base. Captúralas en el módulo GPS/QR del tutor.'
        });
      }

      cont.innerHTML = alertas.map(function (a) {
        return '<div class="d-flex align-items-start gap-3 p-3 mb-2 bg-white border ' + a.clase + ' border-2 rounded">'
          + '<i class="bi ' + a.icono + ' fs-4 text-guinda"></i>'
          + '<div><div class="fw-bold small">' + a.titulo + '</div>'
          + '<div class="text-muted small">' + a.texto + '</div></div></div>';
      }).join('');
    }

    function renderizarTablaReportes() {
      try {
        const elBuscar = document.getElementById('repBuscar');
        const elTipo = document.getElementById('repTipo');
        const elEst = document.getElementById('repEstatus');
        const elAlc = document.getElementById('repAlcaldia');
        const q = ((elBuscar && elBuscar.value) || '').toLowerCase().trim();
        const fTipo = (elTipo && elTipo.value) || '';
        const fEst = (elEst && elEst.value) || '';
        const fAlc = (elAlc && elAlc.value) || '';

        const base = Array.isArray(reportesRUPE) ? reportesRUPE : (Array.isArray(window.reportesRUPE) ? window.reportesRUPE : []);
        let lista = base.filter(function (r) {
          if (!r) return false;
          const coincideTexto = !q
            || String(r.folio || '').toLowerCase().indexOf(q) !== -1
            || String(r.mascota || '').toLowerCase().indexOf(q) !== -1
            || String(r.tutor || '').toLowerCase().indexOf(q) !== -1;
          return coincideTexto
            && (!fTipo || r.tipo === fTipo)
            && (!fEst || r.estatus === fEst)
            && (!fAlc || r.alcaldia === fAlc);
        });

        try {
          if (typeof ordenarPorPrioridad !== 'undefined' && ordenarPorPrioridad) {
            lista = lista.slice().sort(function (a, b) {
              return diasTranscurridos(b.fecha_alta || b.fecha, b.antiguedad_dias)
                - diasTranscurridos(a.fecha_alta || a.fecha, a.antiguedad_dias);
            });
          } else {
            lista = lista.slice().sort(function (a, b) {
              return String(b.fecha_alta || b.fecha || '').localeCompare(String(a.fecha_alta || a.fecha || ''));
            });
          }
        } catch (_) {}

        const tbody = document.querySelector('#tablaReportes tbody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (!lista.length) {
          tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-5"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No hay expedientes en la base de datos o no coinciden con los filtros.</td></tr>';
        } else {
          const ph = (typeof FOTO_PLACEHOLDER_ADMIN !== 'undefined') ? FOTO_PLACEHOLDER_ADMIN : '';
          const rows = lista.map(function (r) {
            const dias = diasTranscurridos(r.fecha_alta || r.fecha, r.antiguedad_dias);
            const sem = (typeof claseSemaforo === 'function') ? claseSemaforo(dias) : '';
            const meta = (typeof ESTATUS_META !== 'undefined' && ESTATUS_META[r.estatus])
              ? ESTATUS_META[r.estatus]
              : { badge: 'bg-secondary', icono: 'bi-dot' };
            const foto = r.foto || ph;
            const folio = String(r.folio || '');
            const folioAttr = folio.replace(/"/g, '&quot;');
            return (
              '<tr data-folio="' + folioAttr + '">' +
              '<td class="ps-4"><img src="' + String(foto).replace(/"/g, '') + '" alt="" class="foto-miniatura" style="width:48px;height:48px;object-fit:cover;border-radius:8px;" data-ph="1"></td>' +
              '<td class="fw-bold small">' + folio + '</td>' +
              '<td><div class="fw-semibold">' + String(r.mascota || '—') + '</div><div class="small text-muted">' +
                String(r.raza || '') + (r.tamano && r.tamano !== '—' ? ' · ' + r.tamano : '') + '</div></td>' +
              '<td class="small">' + String(r.tutor || '—') + '</td>' +
              '<td class="small">' + String(r.alcaldia || '—') +
                (r.colonia ? '<br><span class="text-muted">' + String(r.colonia) + '</span>' : '') + '</td>' +
              '<td><span class="badge bg-danger">' + String(r.tipo || 'Extraviado') + '</span></td>' +
              '<td><span class="badge ' + meta.badge + '"><i class="bi ' + meta.icono + ' me-1"></i>' +
                String(r.estatus || '') + '</span></td>' +
              '<td class="text-center small"><span class="semaforo ' + sem + '"></span> <strong>' + dias +
                '</strong> día' + (dias === 1 ? '' : 's') +
                '<br><span class="text-muted" style="font-size:.7rem">Registro: ' +
                String(r.fecha_alta || r.fecha || '—') + '</span></td>' +
              '<td class="text-end pe-4 text-nowrap">' +
              '<button type="button" class="btn btn-sm btn-outline-secondary me-1 btn-ver-exp" data-folio="' + folioAttr + '" title="Expediente"><i class="bi bi-folder2-open"></i></button>' +
              '<button type="button" class="btn btn-sm btn-outline-primary me-1 btn-avanzar-exp" data-folio="' + folioAttr + '" title="Avanzar"><i class="bi bi-arrow-right-circle"></i></button>' +
              '<button type="button" class="btn btn-sm btn-outline-danger btn-pdf-exp" data-folio="' + folioAttr + '" title="PDF"><i class="bi bi-file-earmark-pdf"></i></button>' +
              '</td></tr>'
            );
          });
          tbody.innerHTML = rows.join('');
          tbody.querySelectorAll('img[data-ph]').forEach(function (img) {
            img.addEventListener('error', function () {
              if (ph) { this.onerror = null; this.src = ph; }
            });
          });
        }

        const repMostrando = document.getElementById('repMostrando');
        if (repMostrando) repMostrando.textContent = String(lista.length);
        const repTotal = document.getElementById('repTotal');
        if (repTotal) repTotal.textContent = String(base.length);

        if (elAlc && elAlc.options.length <= 1) {
          const set = {};
          base.forEach(function (r) {
            if (r.alcaldia && r.alcaldia !== '—') set[r.alcaldia] = true;
          });
          Object.keys(set).sort().forEach(function (a) {
            const opt = document.createElement('option');
            opt.value = a;
            opt.textContent = a;
            elAlc.appendChild(opt);
          });
        }
      } catch (err) {
        console.error('[RUPE] renderizarTablaReportes', err);
      }
    }

    function limpiarFiltrosReportes() {
      document.getElementById('repBuscar').value = '';
      document.getElementById('repTipo').value = '';
      document.getElementById('repEstatus').value = '';
      document.getElementById('repAlcaldia').value = '';
      renderizarTablaReportes();
      mostrarToast('Filtros restablecidos', 'info');
    }
    function alternarPrioridad() {
      ordenarPorPrioridad = !ordenarPorPrioridad;
      const btn = document.getElementById('btnPrioridad');
      if (ordenarPorPrioridad) {
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-primary', 'text-white');
        mostrarToast('Ordenado por antigüedad: casos más rezagados primero', 'info');
      } else {
        btn.classList.add('btn-outline-primary');
        btn.classList.remove('btn-primary', 'text-white');
        mostrarToast('Ordenado por fecha de captura', 'info');
      }
      renderizarTablaReportes();
    }
    const FLUJO_ESTATUS = ['Pendiente', 'Validado', 'En Seguimiento', 'Resuelto'];
    function avanzarEstatus(folio) {
      const lista = (Array.isArray(reportesRUPE) && reportesRUPE.length)
        ? reportesRUPE
        : (Array.isArray(window.reportesRUPE) ? window.reportesRUPE : []);
      const r = lista.find(x => String(x.folio) === String(folio));
      if (!r) { if (typeof mostrarToast === 'function') mostrarToast('No se encontró el folio ' + folio, 'warning'); return; }
      if (r.estatus === 'Rechazado') {
        mostrarToast(`El expediente ${folio} fue rechazado y no admite seguimiento`, 'warning');
        return;
      }
      const i = FLUJO_ESTATUS.indexOf(r.estatus);
      if (i === -1 || i === FLUJO_ESTATUS.length - 1) {
        mostrarToast(`El expediente ${folio} ya se encuentra Resuelto`, 'info');
        return;
      }
      r.estatus = FLUJO_ESTATUS[i + 1];
      if (r.estatus === 'Resuelto') r.fechaCierre = HOY.toISOString().split('T')[0];
      registrarBitacora('Cambio de estatus', `${folio} avanzó a ${r.estatus}.`);
      refrescarModuloReportes();
      generarReporteEstadistico();
      mostrarToast(`${folio} actualizado a ${r.estatus}`, 'success');
    }
    function constanciaDesdeListado(folio) {
      verDetallePDF(folio);
    }
    window.constanciaDesdeListado = constanciaDesdeListado;
    window.verDetallePDF = verDetallePDF;
    window.avanzarEstatus = avanzarEstatus;

    function verExpediente(folio) {
      const lista = (Array.isArray(reportesRUPE) && reportesRUPE.length)
        ? reportesRUPE
        : (Array.isArray(window.reportesRUPE) ? window.reportesRUPE : []);
      const r = lista.find(x => String(x.folio) === String(folio));
      if (!r) { if (typeof mostrarToast === 'function') mostrarToast('No se encontró el folio ' + folio, 'warning'); return; }
      const dias = diasTranscurridos(r.fecha_alta || r.fecha, r.antiguedad_dias);
      const meta = (typeof ESTATUS_META !== 'undefined' && ESTATUS_META[r.estatus]) ? ESTATUS_META[r.estatus] : { badge: 'bg-secondary', icono: 'bi-dot' };
      const cuerpo = document.getElementById('cuerpoExpediente');
      if (!cuerpo) { if (typeof mostrarToast === 'function') mostrarToast('Modal no disponible', 'error'); return; }
      const movimientos = (Array.isArray(typeof bitacoraReportes !== 'undefined' ? bitacoraReportes : []) ? bitacoraReportes : []).filter(function(b){ return String((b && b.detalle) || '').includes(folio); });
      cuerpo.innerHTML = `
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3 flex-wrap gap-3">
          <div class="d-flex gap-3 align-items-center">
            <img src="${r.foto || FOTO_PLACEHOLDER_ADMIN}" alt="${r.mascota}" class="foto-miniatura-lg" onerror="this.onerror=null;this.src=FOTO_PLACEHOLDER_ADMIN;">
            <div>
              <h5 class="fw-bold m-0 text-guinda">${r.mascota} · ${r.raza}</h5>
              <small class="text-muted">Folio ${r.folio} · Captura ciudadana del tutor</small>
            </div>
          </div>
          <div class="text-end">
            <span class="badge ${TIPO_BADGE[r.tipo] || 'bg-secondary'} mb-1">${r.tipo}</span><br>
            <span class="badge ${meta.badge}"><i class="bi ${meta.icono} me-1"></i>${r.estatus}</span>
          </div>
        </div>
        <div class="row g-3 small">
          <div class="col-md-6">
            <div class="p-3 bg-light rounded border h-100">
              <div class="fw-bold text-uppercase text-muted mb-2" style="font-size:.7rem;">Datos del canino</div>
              <div><strong>Tamaño:</strong> ${r.tamano}</div>
              <div><strong>Color:</strong> ${r.color}</div>
              <div><strong>Microchip:</strong> ${r.chip}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 bg-light rounded border h-100">
              <div class="fw-bold text-uppercase text-muted mb-2" style="font-size:.7rem;">Contacto y ubicación</div>
              <div><strong>Tutor / reportante:</strong> ${r.tutor}</div>
              <div><strong>Teléfono:</strong> ${r.tel}</div>
              <div><strong>Zona:</strong> ${r.colonia}, ${r.alcaldia}</div>
            </div>
          </div>
          <div class="col-12">
            <div class="p-3 border rounded">
              <div class="fw-bold text-uppercase text-muted mb-2" style="font-size:.7rem;">Señas particulares y observaciones</div>
              <p class="m-0">${r.obs}</p>
            </div>
          </div>
          <div class="col-12">
            <div class="d-flex gap-4 flex-wrap p-3 bg-white border rounded">
              <div><span class="semaforo ${claseSemaforo(dias)}"></span><strong>${dias} días</strong> desde la captura</div>
              <div><strong>Fecha de reporte:</strong> ${fechaLarga(r.fecha)}</div>
              <div><strong>Cierre:</strong> ${r.fechaCierre ? fechaLarga(r.fechaCierre) : 'Sin cerrar'}</div>
            </div>
          </div>
          <div class="col-12">
            <div class="fw-bold text-uppercase text-muted mb-2" style="font-size:.7rem;">Movimientos administrativos</div>
            ${movimientos.length
              ? '<ul class="list-group list-group-flush">' + movimientos.map(m => `<li class="list-group-item px-0"><strong>${m.fecha}</strong> · ${m.accion}<br><span class="text-muted">${m.detalle} (${m.usuario})</span></li>`).join('') + '</ul>'
              : '<p class="text-muted m-0">Sin movimientos administrativos registrados.</p>'}
          </div>
        </div>`;
      try {
        const mEl = document.getElementById('modalExpediente');
        if (mEl && window.bootstrap) {
          bootstrap.Modal.getOrCreateInstance(mEl).show();
        } else if (modalExpedienteBs) {
          modalExpedienteBs.show();
        } else if (mEl) {
          mEl.classList.add('show');
          mEl.style.display = 'block';
        }
      } catch (eShow) { console.warn(eShow); }
    }
    function esPendienteValidacion(r) {
      if (!r) return false;
      const e = String(r.estatus || '');
      const bd = String(r.estatus_bd || '');
      // Pendientes de validar: Pendiente o aún "En Búsqueda" sin validar
      if (e === 'Validado' || e === 'Resuelto' || e === 'Rechazado') return false;
      return e === 'Pendiente'
        || /búsqueda|busqueda/i.test(bd)
        || /búsqueda|busqueda|pendiente/i.test(e)
        || e === 'En Seguimiento';
    }

    function renderizarValidacion() {
      try {
        const fuente = Array.isArray(reportesRUPE) && reportesRUPE.length
          ? reportesRUPE
          : (Array.isArray(window.reportesRUPE) ? window.reportesRUPE : []);
        const pendientes = fuente.filter(esPendienteValidacion);

        const badge = document.getElementById('badgePendientes');
        if (badge) badge.textContent = String(pendientes.length);
        const valC = document.getElementById('valContador');
        if (valC) valC.textContent = String(pendientes.length);

        const cont = document.getElementById('contenedorValidacion');
        if (!cont) return;
        cont.innerHTML = '';

        if (!pendientes.length) {
          cont.innerHTML =
            '<div class="col-12"><div class="text-center text-muted py-5 bg-white border rounded">' +
            '<i class="bi bi-check2-circle fs-1 d-block mb-2 text-success"></i>' +
            'No hay reportes pendientes en la base de datos.</div></div>';
          return;
        }

        const ph = (typeof FOTO_PLACEHOLDER_ADMIN !== 'undefined') ? FOTO_PLACEHOLDER_ADMIN : '';
        const cards = pendientes.map(function (r) {
          const dias = diasTranscurridos(r.fecha_alta || r.fecha, r.antiguedad_dias);
          const faltantes = [];
          if (!r.chip || /sin|no proporcionado|—/i.test(String(r.chip))) faltantes.push('Microchip sin verificar');
          if (!r.tel || /no proporcionado|anónimo|—/i.test(String(r.tel))) faltantes.push('Contacto incompleto');
          if (!r.mascota || r.mascota === 'Sin nombre') faltantes.push('Sin nombre del canino');
          const folio = String(r.folio || '').replace(/"/g, '&quot;');
          const tipoBadge = (typeof TIPO_BADGE !== 'undefined' && TIPO_BADGE[r.tipo]) ? TIPO_BADGE[r.tipo] : 'bg-secondary';
          const alertHtml = faltantes.length
            ? '<div class="alert alert-warning py-2 px-2 small mb-3"><i class="bi bi-exclamation-triangle me-1"></i>Revisar: ' + faltantes.join(' · ') + '</div>'
            : '<div class="alert alert-success py-2 px-2 small mb-3"><i class="bi bi-check2 me-1"></i>Expediente completo, listo para validar</div>';

          return (
            '<div class="col-lg-6">' +
            '<div class="card shadow-sm border-0 h-100 validacion-card">' +
            '<div class="card-body">' +
            '<div class="d-flex gap-3">' +
            '<img src="' + String(r.foto || ph).replace(/"/g, '') + '" alt="" class="rounded" style="width:72px;height:72px;object-fit:cover;" data-ph="1">' +
            '<div class="flex-grow-1 min-w-0">' +
            '<div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">' +
            '<div><span class="fw-bold text-guinda">' + String(r.folio || '') + '</span>' +
            '<span class="badge ' + tipoBadge + ' ms-2">' + String(r.tipo || '') + '</span></div>' +
            '<small class="text-muted"><span class="semaforo ' + claseSemaforo(dias) + '"></span> ' + dias + ' días</small></div>' +
            '<div class="fw-semibold">' + String(r.mascota || '—') + ' · ' + String(r.raza || '') + '</div>' +
            '<div class="small text-muted mb-1">' + String(r.colonia || '') + (r.colonia ? ', ' : '') + String(r.alcaldia || '') +
            ' · ' + (typeof fechaLarga === 'function' ? fechaLarga(r.fecha_alta || r.fecha) : String(r.fecha || '')) + '</div>' +
            '<div class="small mb-2"><i class="bi bi-person me-1"></i>' + String(r.tutor || '—') +
            ' · <i class="bi bi-telephone me-1"></i>' + String(r.tel || '—') + '</div>' +
            '<p class="small text-muted border-start border-3 ps-2 mb-3">' + String(r.obs || 'Sin señas registradas') + '</p>' +
            alertHtml +
            '<div class="d-flex gap-2 flex-wrap">' +
            '<button type="button" class="btn btn-sm btn-success btn-validar-card" data-folio="' + folio + '"><i class="bi bi-patch-check me-1"></i>Validar</button>' +
            '<button type="button" class="btn btn-sm btn-outline-primary btn-exp-card" data-folio="' + folio + '"><i class="bi bi-folder2-open me-1"></i>Expediente</button>' +
            '</div></div></div></div></div></div>'
          );
        });
        cont.innerHTML = cards.join('');
        cont.querySelectorAll('img[data-ph]').forEach(function (img) {
          img.addEventListener('error', function () { if (ph) { this.onerror = null; this.src = ph; } });
        });
      } catch (err) {
        console.error('[RUPE] renderizarValidacion', err);
        const cont = document.getElementById('contenedorValidacion');
        if (cont) cont.innerHTML = '<div class="col-12 text-danger">Error al renderizar bandeja: ' + String(err.message || err) + '</div>';
      }
    }
    window.renderizarValidacion = renderizarValidacion;
    window.esPendienteValidacion = esPendienteValidacion;

    async function validarReporte(folio, btnEl) {
      try {
        const r = (Array.isArray(reportesRUPE) ? reportesRUPE : []).find(function (x) {
          return String(x.folio) === String(folio);
        });
        if (!r) {
          mostrarToast('No se encontró el folio ' + folio, 'warning');
          return;
        }
        const id = r.id_mascota || r.id;
        if (!id) {
          mostrarToast('El reporte no tiene ID de base de datos', 'warning');
          return;
        }
        if (btnEl) {
          btnEl.disabled = true;
          btnEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>…';
        }
        let resOk = false;
        try {
          if (window.RUPEAdmin && window.RUPEAdmin.api) {
            await window.RUPEAdmin.api.put('/api/admin/reportes/' + encodeURIComponent(id), {
              estatus: 'En Búsqueda',
              accion: 'validar'
            });
            resOk = true;
          } else {
            const res = await fetch('/api/admin/reportes/' + encodeURIComponent(id), {
              method: 'PUT',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': window.__CSRF__ || '',
                'X-Requested-With': 'XMLHttpRequest'
              },
              credentials: 'same-origin',
              body: JSON.stringify({ estatus: 'En Búsqueda', accion: 'validar', _csrf: window.__CSRF__ || '' })
            });
            const data = await res.json().catch(function () { return {}; });
            if (!res.ok || data.success === false) {
              throw new Error(data.message || 'Error al validar');
            }
            resOk = true;
          }
        } catch (apiErr) {
          console.warn(apiErr);
          mostrarToast((apiErr && apiErr.message) ? apiErr.message : 'No se pudo validar en la base de datos', 'error');
          if (btnEl) {
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="bi bi-patch-check me-1"></i>Validar';
          }
          return;
        }
        r.estatus = 'Validado';
        r.estatus_bd = 'En Búsqueda';
        mostrarToast('✓ ' + folio + ' validado correctamente', 'success');
        try { registrarBitacora('Validación', folio + ' validado y confirmado en BD.'); } catch (_) {}
        try { if (typeof renderizarValidacion === 'function') renderizarValidacion(); } catch (_) {}
        try { if (typeof renderizarTablaReportes === 'function') renderizarTablaReportes(); } catch (_) {}
        try { if (typeof renderizarPanelReportes === 'function') renderizarPanelReportes(); } catch (_) {}
      } catch (err) {
        console.warn(err);
        mostrarToast((err && err.message) ? err.message : 'Error al validar', 'error');
        if (btnEl) {
          btnEl.disabled = false;
          btnEl.innerHTML = '<i class="bi bi-patch-check me-1"></i>Validar';
        }
      }
    }

    async function validarTodosPendientes() {
      const pendientes = (Array.isArray(reportesRUPE) ? reportesRUPE : []).filter(
        typeof esPendienteValidacion === 'function' ? esPendienteValidacion : function (r) {
          return String(r.estatus) === 'Pendiente';
        }
      );
      if (!pendientes.length) {
        if (typeof mostrarToast === 'function') mostrarToast('No hay reportes pendientes por validar', 'info');
        return;
      }
      const btn = document.getElementById('btnValidarTodosPendientes');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Validando…';
      }
      let ok = 0, fail = 0;
      for (let i = 0; i < pendientes.length; i++) {
        const r = pendientes[i];
        const id = r.id_mascota || r.id;
        try {
          if (id && window.RUPEAdmin && window.RUPEAdmin.api) {
            await window.RUPEAdmin.api.put('/api/admin/reportes/' + encodeURIComponent(id), {
              estatus: 'En Búsqueda',
              accion: 'validar'
            });
          }
          r.estatus = 'Validado';
          r.estatus_bd = 'En Búsqueda';
          ok++;
        } catch (e) {
          fail++;
          console.warn(e);
        }
      }
      if (typeof registrarBitacora === 'function') {
        registrarBitacora('Validación masiva', 'Validados ' + ok + ' de ' + pendientes.length + ' reportes.');
      }
      if (typeof renderizarValidacion === 'function') renderizarValidacion();
      if (typeof renderizarTablaReportes === 'function') renderizarTablaReportes();
      if (typeof renderizarPanelReportes === 'function') renderizarPanelReportes();
      if (typeof mostrarToast === 'function') {
        mostrarToast(ok + ' validado(s)' + (fail ? (', ' + fail + ' con error') : ''), fail ? 'warning' : 'success');
      }
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-all me-1"></i> Validar todos';
      }
    }

    window.validarReporte = validarReporte;
    window.validarTodosPendientes = validarTodosPendientes;

    function normTxt(s) {
      return String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    }

    function calcularAfinidad(a, b) {
      if (!a || !b) return 0;
      if (a.folio && b.folio && a.folio === b.folio) return 0;
      if (a.id_mascota && b.id_mascota && String(a.id_mascota) === String(b.id_mascota)) return 0;
      let p = 0;
      const razaA = normTxt(a.raza);
      const razaB = normTxt(b.raza);
      if (razaA && razaB && razaA === razaB) p += 40;
      else if (razaA && razaB && (razaA.includes(razaB) || razaB.includes(razaA))) p += 25;
      const alcA = normTxt(a.alcaldia);
      const alcB = normTxt(b.alcaldia);
      if (alcA && alcB && alcA === alcB) p += 25;
      const tamA = normTxt(a.tamano);
      const tamB = normTxt(b.tamano);
      if (tamA && tamB && tamA === tamB && tamA !== '—' && tamA !== '-') p += 15;
      const colA = normTxt(a.color);
      const colB = normTxt(b.color);
      if (colA && colB && colA !== '—' && (colA.includes(colB) || colB.includes(colA))) p += 15;
      const espA = normTxt(a.especie || 'perro');
      const espB = normTxt(b.especie || 'perro');
      if (espA && espB && espA === espB) p += 5;
      try {
        const fa = a.fecha ? new Date(a.fecha) : null;
        const fb = b.fecha ? new Date(b.fecha) : null;
        if (fa && fb && !isNaN(fa) && !isNaN(fb)) {
          const brecha = Math.abs((fa - fb) / 86400000);
          if (brecha > 45) p -= 15;
          else if (brecha > 30) p -= 8;
        }
      } catch (_) {}
      return Math.max(0, Math.min(100, p));
    }

    /**
     * Coincidencias solo con reportes reales (mascotas de tutores).
     * Cruza pares distintos por raza / alcaldía / tamaño / color.
     */
    async function ejecutarCruceCoincidencias(avisar) {
      const cont = document.getElementById('contenedorMatches');
      const badge = document.getElementById('badgeMatches');
      const btn = document.getElementById('btnEjecutarCruce') || document.querySelector('[data-action="ejecutar-cruce"]');
      if (cont) {
        cont.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-secondary" role="status"></div><div class="small text-muted mt-2">Cargando reportes y ejecutando cruce…</div></div>';
      }
      if (btn) {
        btn.disabled = true;
        btn.dataset._old = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Cruzando…';
      }

      // 1) Asegurar datos reales desde API / BD
      try {
        if (window.RUPEAdmin && typeof window.RUPEAdmin.loadReportes === 'function') {
          const res = await window.RUPEAdmin.loadReportes();
          const list = (res && res.mascotas) || window.__RUPE_REPORTES__ || [];
          if (window.cargarReportesDesdeBD) window.cargarReportesDesdeBD(list);
        } else if ((!reportesRUPE || !reportesRUPE.length) && window.__RUPE_REPORTES__ && window.cargarReportesDesdeBD) {
          window.cargarReportesDesdeBD(window.__RUPE_REPORTES__);
        }
      } catch (e) {
        console.warn('load reportes para cruce', e);
      }

      renderizarCoincidencias(!!avisar);

      if (btn) {
        btn.disabled = false;
        if (btn.dataset._old) btn.innerHTML = btn.dataset._old;
      }
    }

    function renderizarCoincidencias(avisar) {
      const umbralEl = document.getElementById('umbralMatch');
      const umbral = parseInt(umbralEl && umbralEl.value ? umbralEl.value : '50', 10) || 50;
      const fuente = Array.isArray(reportesRUPE) && reportesRUPE.length
        ? reportesRUPE
        : (Array.isArray(window.__RUPE_REPORTES__) ? window.__RUPE_REPORTES__.map(function (m) {
            return typeof mapMascotaAReporte === 'function' ? mapMascotaAReporte(m) : m;
          }).filter(Boolean) : []);

      const lista = fuente.filter(function (r) {
        return r && r.folio && String(r.estatus || '') !== 'Rechazado';
      });

      const pares = [];
      for (let i = 0; i < lista.length; i++) {
        for (let j = i + 1; j < lista.length; j++) {
          const a = lista[i];
          const b = lista[j];
          const score = calcularAfinidad(a, b);
          if (score >= umbral) {
            let p = a, h = b;
            if (String(b.tipo) === 'Extraviado' && String(a.tipo) !== 'Extraviado') {
              p = b; h = a;
            }
            pares.push({ p: p, h: h, score: score });
          }
        }
      }
      pares.sort(function (x, y) { return y.score - x.score; });

      const badge = document.getElementById('badgeMatches');
      if (badge) badge.textContent = String(pares.length);
      const cont = document.getElementById('contenedorMatches');
      if (!cont) return;
      cont.innerHTML = '';

      if (!lista.length) {
        cont.innerHTML = '<div class="col-12"><div class="text-center text-muted py-5 bg-white border rounded">'
          + '<i class="bi bi-database fs-1 d-block mb-2"></i>No hay reportes de tutores en la base de datos para cruzar.'
          + '<div class="mt-3"><button type="button" class="btn btn-sm btn-outline-primary" onclick="ejecutarCruceCoincidencias(true)">Reintentar carga</button></div>'
          + '</div></div>';
        if (avisar && typeof mostrarToast === 'function') mostrarToast('Sin datos de mascotas en BD', 'info');
        return;
      }

      if (!pares.length) {
        cont.innerHTML = '<div class="col-12"><div class="text-center text-muted py-5 bg-white border rounded">'
          + '<i class="bi bi-search fs-1 d-block mb-2"></i>Sin coincidencias ≥ ' + umbral + '% entre <strong>' + lista.length + '</strong> reportes reales.'
          + '<p class="small mt-2 mb-0">Prueba bajar el umbral a 25%.</p></div></div>';
        if (avisar && typeof mostrarToast === 'function') mostrarToast('Sin coincidencias con umbral ' + umbral + '% (' + lista.length + ' casos)', 'info');
        return;
      }

      const ph = (typeof FOTO_PLACEHOLDER_ADMIN !== 'undefined') ? FOTO_PLACEHOLDER_ADMIN : 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48"><rect fill="#eee" width="48" height="48"/></svg>');
      pares.slice(0, 50).forEach(function (item) {
        const p = item.p, h = item.h, score = item.score;
        const colorBarra = score >= 80 ? 'bg-success' : (score >= 60 ? 'bg-warning' : 'bg-secondary');
        cont.innerHTML +=
          '<div class="col-12">' +
          '<div class="card shadow-sm border-0 mb-2">' +
          '<div class="card-body">' +
          '<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">' +
          '<span class="badge bg-dark">Afinidad ' + score + '%</span>' +
          '<div class="progress flex-grow-1 mx-3" style="height:8px;max-width:200px;">' +
          '<div class="progress-bar ' + colorBarra + '" style="width:' + score + '%"></div></div></div>' +
          '<div class="row g-3 align-items-center">' +
          '<div class="col-md-5"><div class="d-flex gap-2 align-items-center">' +
          '<img src="' + (p.foto || ph) + '" class="foto-miniatura" alt="" onerror="this.onerror=null;this.src=\'' + ph + '\';">' +
          '<div><div class="fw-bold text-guinda">' + (p.folio || '') + '</div>' +
          '<div class="small">' + (p.mascota || '') + ' · ' + (p.raza || '') + '</div>' +
          '<div class="small text-muted">' + (p.alcaldia || '') + ' · ' + (p.estatus_bd || p.estatus || '') + '</div></div></div></div>' +
          '<div class="col-md-2 text-center"><i class="bi bi-arrow-left-right fs-4 text-muted"></i></div>' +
          '<div class="col-md-5"><div class="d-flex gap-2 align-items-center">' +
          '<img src="' + (h.foto || ph) + '" class="foto-miniatura" alt="" onerror="this.onerror=null;this.src=\'' + ph + '\';">' +
          '<div><div class="fw-bold text-guinda">' + (h.folio || '') + '</div>' +
          '<div class="small">' + (h.mascota || '') + ' · ' + (h.raza || '') + '</div>' +
          '<div class="small text-muted">' + (h.alcaldia || '') + ' · ' + (h.estatus_bd || h.estatus || '') + '</div></div></div></div>' +
          '</div>' +
          '<div class="mt-2 text-end">' +
          '<button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="verExpediente(\'' + String(p.folio || '').replace(/'/g, "\\'") + '\')"><i class="bi bi-folder2-open me-1"></i>Ver</button>' +
          '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="verExpediente(\'' + String(h.folio || '').replace(/'/g, "\\'") + '\')"><i class="bi bi-folder2-open me-1"></i>Ver</button>' +
          '</div></div></div></div>';
      });
      if (avisar && typeof mostrarToast === 'function') {
        mostrarToast(pares.length + ' coincidencia(s) entre ' + lista.length + ' reportes reales', 'success');
      }
    }

    window.renderizarCoincidencias = renderizarCoincidencias;
    window.ejecutarCruceCoincidencias = ejecutarCruceCoincidencias;

    function generarReporteEstadistico() {
      // Solo datos reales ya cargados desde mascotas (reportesRUPE)
      if (!Array.isArray(reportesRUPE) || !reportesRUPE.length) {
        if (window.__RUPE_REPORTES__ && window.cargarReportesDesdeBD) {
          try { window.cargarReportesDesdeBD(window.__RUPE_REPORTES__); } catch (_) {}
        }
      }
      const tipoEl = document.getElementById('estTipoReporte');
      const desdeEl = document.getElementById('estDesde');
      const hastaEl = document.getElementById('estHasta');
      const tipo = tipoEl ? tipoEl.value : 'alcaldia';
      const desde = desdeEl ? desdeEl.value : '';
      const hasta = hastaEl ? hastaEl.value : '';

      const universo = (Array.isArray(reportesRUPE) ? reportesRUPE : []).filter(function (r) {
        if (!r) return false;
        const f = String(r.fecha || '').slice(0, 10);
        if (desde && f && f < desde) return false;
        if (hasta && f && f > hasta) return false;
        return true;
      });
      const total = universo.length;

      let titulo = '';
      let encabezados = [];
      let filas = [];

      const esResuelto = function (r) {
        const e = String(r.estatus || '');
        const bd = String(r.estatus_bd || '');
        return /resuelto|localizado/i.test(e) || /localizado|resuelto/i.test(bd);
      };
      const esAbierto = function (r) {
        return !esResuelto(r) && String(r.estatus || '') !== 'Rechazado';
      };

      const resumen = function (claveFn) {
        const mapa = {};
        universo.forEach(function (r) {
          const k = claveFn(r) || 'Sin dato';
          if (!mapa[k]) mapa[k] = { total: 0, resueltos: 0, abiertos: 0 };
          mapa[k].total++;
          if (esResuelto(r)) mapa[k].resueltos++;
          else if (esAbierto(r)) mapa[k].abiertos++;
        });
        return mapa;
      };

      if (tipo === 'alcaldia' || tipo === 'raza') {
        const esAlc = tipo === 'alcaldia';
        titulo = esAlc ? 'Concentrado por alcaldía (datos reales)' : 'Concentrado por raza (datos reales)';
        encabezados = [esAlc ? 'Alcaldía' : 'Raza', 'Total', 'Abiertos', 'Resueltos', 'Efectividad', 'Participación'];
        const mapa = resumen(function (r) { return esAlc ? (r.alcaldia || '—') : (r.raza || '—'); });
        filas = Object.keys(mapa).sort(function (a, b) { return mapa[b].total - mapa[a].total; }).map(function (k) {
          const d = mapa[k];
          const efec = d.total ? Math.round((d.resueltos / d.total) * 100) : 0;
          const part = total ? Math.round((d.total / total) * 100) : 0;
          return [k, d.total, d.abiertos, d.resueltos, efec + '%', part + '%'];
        });
      } else if (tipo === 'estatus') {
        titulo = 'Concentrado por estatus (datos reales)';
        encabezados = ['Estatus', 'Casos', 'Participación', 'Interpretación'];
        const notas = {
          'Pendiente': 'En Búsqueda — pendiente de validación / búsqueda activa',
          'En Seguimiento': 'En Resguardo o seguimiento operativo',
          'Resuelto': 'Localizado / caso cerrado',
          'Validado': 'Validado administrativamente',
          'Rechazado': 'Descartado'
        };
        const mapa = {};
        universo.forEach(function (r) {
          const k = r.estatus_bd || r.estatus || 'Sin estatus';
          mapa[k] = (mapa[k] || 0) + 1;
        });
        filas = Object.keys(mapa).sort(function (a, b) { return mapa[b] - mapa[a]; }).map(function (k) {
          const part = total ? Math.round((mapa[k] / total) * 100) : 0;
          return [k, mapa[k], part + '%', notas[k] || notas[String(k)] || 'Estatus en BD'];
        });
      } else {
        titulo = 'Comportamiento mensual (datos reales)';
        encabezados = ['Periodo', 'Reportes recibidos', 'Extraviados', 'En seguimiento', 'Resueltos'];
        const meses = { '01': 'Enero', '02': 'Febrero', '03': 'Marzo', '04': 'Abril', '05': 'Mayo', '06': 'Junio', '07': 'Julio', '08': 'Agosto', '09': 'Septiembre', '10': 'Octubre', '11': 'Noviembre', '12': 'Diciembre' };
        const mapa = {};
        universo.forEach(function (r) {
          const f = String(r.fecha || '').slice(0, 7);
          if (!f || f.length < 7) return;
          if (!mapa[f]) mapa[f] = { total: 0, ext: 0, hall: 0, res: 0 };
          mapa[f].total++;
          if (String(r.tipo) === 'Extraviado') mapa[f].ext++; else mapa[f].hall++;
          if (esResuelto(r)) mapa[f].res++;
        });
        filas = Object.keys(mapa).sort().map(function (k) {
          const d = mapa[k];
          const etiqueta = (meses[k.slice(5, 7)] || k) + ' ' + k.slice(0, 4);
          return [etiqueta, d.total, d.ext, d.hall, d.res];
        });
      }

      const titEl = document.getElementById('tituloReporteEst');
      if (titEl) titEl.textContent = titulo;
      const tabla = document.getElementById('tablaReporteEst');
      if (!tabla) {
        if (window.mostrarToast) mostrarToast('No se encontró la tabla del reporte.', 'warning');
        return;
      }
      const thead = tabla.querySelector('thead tr');
      const tbody = tabla.querySelector('tbody');
      if (thead) {
        thead.innerHTML = encabezados.map(function (h, i) {
          return '<th class="' + (i === 0 ? 'ps-4' : 'text-center') + '">' + h + '</th>';
        }).join('');
      }
      if (tbody) {
        tbody.innerHTML = '';
        if (!filas.length) {
          tbody.innerHTML = '<tr><td colspan="' + encabezados.length + '" class="text-center text-muted py-5">No hay reportes de tutores en la base de datos para el periodo seleccionado.</td></tr>';
        } else {
          filas.forEach(function (f) {
            tbody.innerHTML += '<tr>' + f.map(function (c, i) {
              return i === 0
                ? '<td class="ps-4 fw-semibold">' + c + '</td>'
                : '<td class="text-center">' + c + '</td>';
            }).join('') + '</tr>';
          });
        }
      }

      const pie = document.getElementById('pieReporteEst');
      if (pie) {
        pie.innerHTML = '<i class="bi bi-database me-1"></i>Universo: <strong>' + total + '</strong> expedientes reales (tabla mascotas)'
          + ' · Periodo: ' + (desde ? fechaLarga(desde) : 'inicio') + ' – ' + (hasta ? fechaLarga(hasta) : 'hoy')
          + ' · Fuente: BD RUPE';
      }
      window._ultimoReporteEst = { titulo: titulo, encabezados: encabezados, filas: filas, total: total, desde: desde, hasta: hasta };
      if (window.mostrarToast) mostrarToast('Reporte generado: ' + total + ' expediente(s)', total ? 'success' : 'info');
    }
    window.generarReporteEstadistico = generarReporteEstadistico;

    function descargarPDFEstadistico() {
      if (typeof generarReporteEstadistico === 'function') {
        try { generarReporteEstadistico(); } catch (_) {}
      }
      const data = window._ultimoReporteEst;
      const tabla = document.getElementById('tablaReporteEst');
      if (!window.jspdf || !window.jspdf.jsPDF) {
        if (window.mostrarToast) mostrarToast('jsPDF no está cargado. Usa Imprimir o recarga la página.', 'warning');
        else alert('jsPDF no disponible');
        return;
      }
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF({ unit: 'pt', format: 'letter' });
      const titulo = (data && data.titulo) || (document.getElementById('tituloReporteEst') || {}).textContent || 'Reporte estadístico RUPE';
      doc.setFontSize(14);
      doc.setTextColor(107, 29, 47);
      doc.text('RUPE CDMX — Reportes Estadísticos', 40, 48);
      doc.setFontSize(11);
      doc.setTextColor(40, 40, 40);
      doc.text(String(titulo), 40, 70);
      doc.setFontSize(9);
      doc.setTextColor(100, 100, 100);
      const pieTxt = (document.getElementById('pieReporteEst') || {}).textContent || '';
      const pieLines = doc.splitTextToSize(pieTxt, 520);
      doc.text(pieLines, 40, 88);

      let y = 110;
      const headers = (data && data.encabezados) || [];
      const rows = (data && data.filas) || [];
      // Fallback: leer tabla DOM
      let headersUse = headers.slice();
      let rowsUse = rows.slice();
      if ((!headersUse.length || !rowsUse.length) && tabla) {
        headersUse = [];
        rowsUse = [];
        tabla.querySelectorAll('thead th').forEach(function (th) { headersUse.push(th.textContent.trim()); });
        tabla.querySelectorAll('tbody tr').forEach(function (tr) {
          const cells = [];
          tr.querySelectorAll('td').forEach(function (td) { cells.push(td.textContent.trim()); });
          if (cells.length) rowsUse.push(cells);
        });
      }
      if (!headersUse.length) {
        if (window.mostrarToast) mostrarToast('Genera el reporte antes de descargar el PDF.', 'warning');
        return;
      }
      const colW = Math.min(90, Math.floor(520 / Math.max(headersUse.length, 1)));
      doc.setFontSize(9);
      doc.setTextColor(20, 20, 20);
      doc.setFont(undefined, 'bold');
      headersUse.forEach(function (h, i) {
        doc.text(String(h).slice(0, 18), 40 + i * colW, y);
      });
      y += 14;
      doc.setDrawColor(200, 200, 200);
      doc.line(40, y, 570, y);
      y += 12;
      doc.setFont(undefined, 'normal');
      rowsUse.forEach(function (row) {
        if (y > 740) {
          doc.addPage();
          y = 50;
        }
        row.forEach(function (cell, i) {
          doc.text(String(cell).slice(0, 22), 40 + i * colW, y);
        });
        y += 14;
      });
      y += 20;
      doc.setFontSize(8);
      doc.setTextColor(120, 120, 120);
      doc.text('Generado: ' + new Date().toLocaleString('es-MX') + ' · Solo datos de la base de datos RUPE', 40, y);
      const fname = 'RUPE_reporte_estadistico_' + new Date().toISOString().slice(0, 10) + '.pdf';
      doc.save(fname);
      if (window.mostrarToast) mostrarToast('PDF descargado: ' + fname, 'success');
    }
    window.descargarPDFEstadistico = descargarPDFEstadistico;

    function exportarReporteEstadistico() {
      const tabla = document.getElementById('tablaReporteEst');
      let csv = "\uFEFF";
      tabla.querySelectorAll('tr').forEach(fila => {
        const celdas = fila.querySelectorAll('th, td');
        const datos = [];
        celdas.forEach(c => datos.push(`"${c.textContent.trim().replace(/"/g, '""')}"`));
        csv += datos.join(',') + '\n';
      });
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.setAttribute('download', 'Reporte_Estadistico_RUPE.csv');
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      registrarBitacora('Exportación', `Se descargó el reporte “${document.getElementById('tituloReporteEst').textContent}” en formato CSV.`);
      mostrarToast('Reporte estadístico exportado en CSV', 'success');
    }
    function imprimirReporteEstadistico() {
      registrarBitacora('Impresión', `Se envió a impresión el reporte “${document.getElementById('tituloReporteEst').textContent}”.`);
      mostrarToast('Preparando vista de impresión...', 'info');
      window.print();
    }
    function renderizarBitacora() {
      const ul = document.getElementById('bitacoraGlobal');
      if (!ul) return;
      ul.innerHTML = '';
      bitacoraReportes.slice(0, 12).forEach(b => {
        ul.innerHTML += `
          <li class="timeline-item">
            <div class="d-flex justify-content-between flex-wrap">
              <span class="fw-bold small">${b.accion}</span>
              <small class="text-muted">${b.fecha}</small>
            </div>
            <div class="small text-muted">${b.detalle}</div>
            <div class="small"><i class="bi bi-person-badge me-1"></i>${b.usuario}</div>
          </li>`;
      });
    }
    function exportarReportesExcel() {
      const tabla = document.getElementById('tablaReportes');
      let contenidoCSV = "\uFEFF";
      const filas = tabla.querySelectorAll('tr');
      filas.forEach(fila => {
        const celdas = fila.querySelectorAll('th, td');
        const filaDatos = [];
        for (let i = 0; i < celdas.length - 1; i++) {
          let texto = celdas[i].textContent.trim().replace(/"/g, '""');
          filaDatos.push(`"${texto}"`);
        }
        contenidoCSV += filaDatos.join(',') + '\n';
      });
      const blob = new Blob([contenidoCSV], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const enlace = document.createElement('a');
      enlace.href = url;
      enlace.setAttribute('download', 'Reportes_RUPE_CDMX.csv');
      document.body.appendChild(enlace);
      enlace.click();
      document.body.removeChild(enlace);
      mostrarToast('Archivo Excel exportado correctamente', 'success');
    }
    function mostrarToast(mensaje, tipo) {
      tipo = tipo || 'success';
      if (tipo === 'error') tipo = 'danger';
      if (window.RUPEAdmin && typeof window.RUPEAdmin.toast === 'function') {
        try { window.RUPEAdmin.toast(mensaje, tipo === 'danger' ? 'error' : tipo); } catch (_) {}
      }
      let container = document.getElementById('toastContainer');
      if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container-custom';
        container.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:99999;';
        document.body.appendChild(container);
      }
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
        warning: 'bg-warning text-dark'
      };
      const toast = document.createElement('div');
      toast.className = 'toast-custom alert ' + (bgMap[tipo] || 'bg-success') + ' text-white d-flex align-items-center shadow-lg mb-2';
      toast.style.minWidth = '260px';
      toast.innerHTML = '<i class="bi ' + (iconMap[tipo] || 'bi-info-circle-fill') + ' me-2 fs-5"></i><span>' + String(mensaje || '') + '</span>';
      container.appendChild(toast);
      setTimeout(function () {
        toast.style.transition = 'opacity 0.3s';
        toast.style.opacity = '0';
        setTimeout(function () { try { toast.remove(); } catch (_) {} }, 300);
      }, 3500);
    }
    window.mostrarToast = mostrarToast;

    document.addEventListener('DOMContentLoaded', function () {
      try {
        const mPdf = document.getElementById('modalPDF');
        if (mPdf && window.bootstrap) modalPdfBs = bootstrap.Modal.getOrCreateInstance(mPdf);
      } catch (e) { console.warn('modalPDF', e); }
      try {
        const mExp = document.getElementById('modalExpediente');
        if (mExp && window.bootstrap) modalExpedienteBs = bootstrap.Modal.getOrCreateInstance(mExp);
      } catch (e) { console.warn('modalExpediente', e); }

      try { if (typeof poblarSelectoresCatalogo === 'function') poblarSelectoresCatalogo(); } catch (e) {}
      try { if (typeof refrescarModuloReportes === 'function') refrescarModuloReportes(); } catch (e) {}
      try { if (typeof generarReporteEstadistico === 'function') generarReporteEstadistico(); } catch (e) {}

      // Sub-tabs: Panel / Expedientes / Validación / etc.
      const subTabLinks = document.querySelectorAll('#subTabReportes .nav-link');
      const subTabContents = document.querySelectorAll('.sub-tab-content');
      subTabLinks.forEach(function (link) {
        link.style.cursor = 'pointer';
        link.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          subTabLinks.forEach(function (l) { l.classList.remove('active'); });
          this.classList.add('active');
          subTabContents.forEach(function (c) { c.classList.remove('activo'); });
          const target = this.getAttribute('data-subtab');
          const panel = document.getElementById('subtab-' + target);
          if (panel) panel.classList.add('activo');
          // Re-render al entrar a pestaña
          (async function () {
            try {
              // Asegurar datos reales de BD
              if ((!reportesRUPE || !reportesRUPE.length) && window.RUPEAdmin && window.RUPEAdmin.loadReportes) {
                const res = await window.RUPEAdmin.loadReportes();
                const list = (res && res.mascotas) || window.__RUPE_REPORTES__ || [];
                if (window.cargarReportesDesdeBD) window.cargarReportesDesdeBD(list);
              }
              if (target === 'reportes-listado' && typeof renderizarTablaReportes === 'function') renderizarTablaReportes();
              if (target === 'reportes-validacion' && typeof renderizarValidacion === 'function') renderizarValidacion();
              if (target === 'reportes-panel' && typeof renderizarPanelReportes === 'function') renderizarPanelReportes();
              if (target === 'reportes-coincidencias') {
                if (typeof ejecutarCruceCoincidencias === 'function') await ejecutarCruceCoincidencias(false);
                else if (typeof renderizarCoincidencias === 'function') renderizarCoincidencias(false);
              }
            } catch (err) { console.warn(err); }
          })();
        });
      });

      // Delegación de botones de la tabla de expedientes
      const tabla = document.getElementById('tablaReportes');
      if (tabla) {
        tabla.addEventListener('click', function (ev) {
          const btn = ev.target.closest('button[data-folio]');
          if (!btn) return;
          const folio = btn.getAttribute('data-folio');
          if (!folio) return;
          ev.preventDefault();
          try {
            if (btn.classList.contains('btn-ver-exp') && typeof verExpediente === 'function') verExpediente(folio);
            else if (btn.classList.contains('btn-avanzar-exp') && typeof avanzarEstatus === 'function') avanzarEstatus(folio);
            else if (btn.classList.contains('btn-pdf-exp') && typeof constanciaDesdeListado === 'function') constanciaDesdeListado(folio);
          } catch (err) { console.warn(err); }
        });
      }
    });

    // Exponer funciones globales para onclick residuales
    window.renderizarTablaReportes = renderizarTablaReportes;
    if (typeof verExpediente === 'function') window.verExpediente = verExpediente;
    if (typeof avanzarEstatus === 'function') window.avanzarEstatus = avanzarEstatus;
    if (typeof constanciaDesdeListado === 'function') window.constanciaDesdeListado = constanciaDesdeListado;
    if (typeof verDetallePDF === 'function') window.verDetallePDF = verDetallePDF;

    window.renderizarValidacion = renderizarValidacion;
    window.cargarReportesDesdeBD = cargarReportesDesdeBD;
    window.verExpediente = typeof verExpediente === 'function' ? verExpediente : window.verExpediente;
    window.avanzarEstatus = typeof avanzarEstatus === 'function' ? avanzarEstatus : window.avanzarEstatus;

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

<script src="/assets/js/admin-app.js?v=20260923g" defer></script>


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
(function(){
  function applyDb(){
    var init = window.RUPE_ADMIN_INIT || {};
    if (Array.isArray(init.mascotas) && init.mascotas.length) {
      window.__RUPE_REPORTES__ = init.mascotas;
      if (typeof window.reportesData !== 'undefined') window.reportesData = init.mascotas;
      // Si hay variable local reportes en scope global
      try { if (typeof reportes !== 'undefined') reportes = init.mascotas; } catch(e){}
    }
    if (init.stats) window.__RUPE_STATS__ = init.stats;
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', applyDb);
  else applyDb();
  window.addEventListener('load', applyDb);
})();
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  setTimeout(function () {
    if (window.RUPEAdmin && window.RUPEAdmin.loadReportes) {
      window.RUPEAdmin.loadReportes().then(function (res) {
        const list = (res && res.mascotas) || window.__RUPE_REPORTES__ || [];
        if (window.cargarReportesDesdeBD) window.cargarReportesDesdeBD(list);
      }).catch(function () {
        if (window.__RUPE_REPORTES__ && window.cargarReportesDesdeBD) {
          window.cargarReportesDesdeBD(window.__RUPE_REPORTES__);
        }
      });
    } else if (window.__RUPE_REPORTES__ && window.cargarReportesDesdeBD) {
      window.cargarReportesDesdeBD(window.__RUPE_REPORTES__);
    }
  }, 300);
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
  function runCatalogos() {
    if (typeof window.cargarCatalogosDesdeBD === 'function') {
      window.cargarCatalogosDesdeBD().catch(function (e) { console.warn(e); });
    }
  }
  setTimeout(runCatalogos, 200);
  setTimeout(runCatalogos, 1200);
  document.addEventListener('click', function (ev) {
    const tab = ev.target.closest('[data-subtab="reportes-catalogos"]');
    if (tab) setTimeout(runCatalogos, 100);
  });
});
</script>


<script>
(function () {
  async function bootExpedientesDesdeBD() {
    try {
      var list = [];
      if (window.RUPEAdmin && typeof window.RUPEAdmin.loadReportes === 'function') {
        var res = await window.RUPEAdmin.loadReportes();
        list = (res && res.mascotas) || window.__RUPE_REPORTES__ || [];
      } else if (Array.isArray(window.__RUPE_REPORTES__) && window.__RUPE_REPORTES__.length) {
        list = window.__RUPE_REPORTES__;
      } else {
        var r = await fetch('/api/admin/reportes', {
          credentials: 'same-origin',
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        var j = await r.json();
        var d = j.data !== undefined ? j.data : j;
        list = (d && d.mascotas) ? d.mascotas : (Array.isArray(d) ? d : []);
      }
      if (typeof window.cargarReportesDesdeBD === 'function') {
        window.cargarReportesDesdeBD(list);
      }
      if (typeof window.renderizarTablaReportes === 'function') {
        window.renderizarTablaReportes();
      }
      if (typeof window.renderizarPanelReportes === 'function') {
        window.renderizarPanelReportes();
      }
      if (typeof window.renderizarValidacion === 'function') {
        window.renderizarValidacion();
      }
    } catch (e) {
      console.warn('[RUPE] boot expedientes', e);
    }
  }
  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(bootExpedientesDesdeBD, 300);
    setTimeout(bootExpedientesDesdeBD, 1500);
  });
  // Al abrir pestaña Expedientes
  document.addEventListener('click', function (ev) {
    var t = ev.target.closest('[data-subtab="reportes-expedientes"], a[href="#subtab-reportes-expedientes"]');
    if (t) setTimeout(bootExpedientesDesdeBD, 100);
  });
})();
</script>








<script>
/**
 * Cargador único: API → reportesRUPE → Expedientes + Bandeja + Coincidencias
 */
(function () {
  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
  }

  function mapRow(m) {
    if (!m || typeof m !== 'object') return null;
    var estatusRaw = String(m.estatus || 'En Búsqueda').trim();
    var estatus = 'Pendiente';
    if (/validado/i.test(estatusRaw)) estatus = 'Validado';
    else if (/localizado|encontrado|resuelto/i.test(estatusRaw)) estatus = 'Resuelto';
    else if (/resguardo/i.test(estatusRaw)) estatus = 'En Seguimiento';
    else if (/rechaz/i.test(estatusRaw)) estatus = 'Rechazado';
    else if (/búsqueda|busqueda|pendiente/i.test(estatusRaw)) estatus = 'Pendiente';

    var foto = m.foto_url || m.foto || '';
    if (foto) {
      foto = String(foto).trim();
      if (foto.indexOf('http') !== 0 && foto.indexOf('data:') !== 0) {
        if (foto.charAt(0) !== '/') {
          if (foto.indexOf('assets/') === 0) foto = '/' + foto;
          else if (foto.indexOf('/') === -1) foto = '/assets/uploads/' + foto;
          else foto = '/' + foto;
        }
      }
    }

    var fechaAlta = String(m.created_at || '').replace('T', ' ').slice(0, 10);
    var fechaEx = String(m.fecha_extravio || '').slice(0, 10);
    if (fechaEx === '0000-00-00') fechaEx = '';
    var dias = m.antiguedad_dias != null ? parseInt(m.antiguedad_dias, 10) : null;
    if (dias == null || isNaN(dias)) {
      var base = fechaAlta || fechaEx;
      if (base) {
        var p = base.split('-');
        if (p.length === 3) {
          var d0 = new Date(+p[0], +p[1] - 1, +p[2]);
          var hoy = new Date(); hoy.setHours(0,0,0,0);
          dias = Math.max(0, Math.floor((hoy - d0) / 86400000));
        } else dias = 0;
      } else dias = 0;
    }

    return {
      id_mascota: m.id_mascota || m.id || 0,
      folio: String(m.folio || ''),
      mascota: m.nombre || m.mascota || 'Sin nombre',
      raza: m.raza_nombre || m.raza || '—',
      tamano: (m.tamano || '').trim() || '—',
      color: m.color || '—',
      alcaldia: m.alcaldia_nombre || m.alcaldia || '—',
      colonia: m.colonia || '',
      tipo: /resguardo/i.test(estatusRaw) ? 'En Resguardo' : 'Extraviado',
      estatus: estatus,
      estatus_bd: estatusRaw,
      fecha: fechaAlta || fechaEx,
      fecha_extravio: fechaEx || fechaAlta,
      fecha_alta: fechaAlta,
      tutor: m.tutor_nombre || m.tutor || '—',
      tel: m.tutor_telefono || m.telefono || '',
      chip: m.microchip || m.chip || m.collar || '',
      obs: m.senas_particulares || m.notas || '',
      foto: foto,
      lat: parseFloat(m.latitud) || null,
      lng: parseFloat(m.longitud) || null,
      antiguedad_dias: dias,
      especie: String(m.especie || 'perro').toLowerCase(),
      qr_token: m.qr_token || ''
    };
  }

  function renderBandeja(list) {
    var cont = document.getElementById('contenedorValidacion');
    if (!cont) return;
    var pendientes = list.filter(function (r) {
      return r.estatus !== 'Resuelto' && r.estatus !== 'Rechazado';
    });
    var badge = document.getElementById('badgePendientes');
    if (badge) badge.textContent = String(pendientes.length);
    var valC = document.getElementById('valContador');
    if (valC) valC.textContent = String(pendientes.length);

    if (!pendientes.length) {
      cont.innerHTML = '<div class="col-12"><div class="text-center text-muted py-5 bg-white border rounded">' +
        '<i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay reportes en la base de datos para validar.</div></div>';
      return;
    }

    cont.innerHTML = pendientes.map(function (r) {
      return '<div class="col-lg-6"><div class="card shadow-sm border-0 h-100">' +
        '<div class="card-body"><div class="d-flex gap-3">' +
        '<img src="' + esc(r.foto) + '" alt="" class="rounded" style="width:72px;height:72px;object-fit:cover;background:#eee">' +
        '<div class="flex-grow-1">' +
        '<div class="fw-bold text-guinda">' + esc(r.folio) +
        ' <span class="badge bg-warning text-dark">' + esc(r.estatus_bd || r.estatus) + '</span></div>' +
        '<div class="fw-semibold">' + esc(r.mascota) + ' · ' + esc(r.raza) + '</div>' +
        '<div class="small text-muted">' + esc(r.alcaldia) + (r.colonia ? ', ' + esc(r.colonia) : '') + '</div>' +
        '<div class="small"><i class="bi bi-person"></i> ' + esc(r.tutor) +
        ' · <i class="bi bi-telephone"></i> ' + esc(r.tel || '—') + '</div>' +
        '<div class="small text-muted">' + (r.antiguedad_dias || 0) + ' días · Registro: ' + esc(r.fecha_alta || r.fecha) + '</div>' +
        '<div class="mt-2">' +
        '<button type="button" class="btn btn-sm btn-success me-1" data-act="validar" data-folio="' + esc(r.folio) + '">Validar</button>' +
        '<button type="button" class="btn btn-sm btn-outline-primary" data-act="exp" data-folio="' + esc(r.folio) + '">Expediente</button>' +
        '</div></div></div></div></div></div>';
    }).join('');
  }

  function afinidad(a, b) {
    var score = 0, max = 0;
    function add(cond, w) { max += w; if (cond) score += w; }
    add(a.especie && b.especie && a.especie === b.especie, 15);
    add(a.raza && b.raza && a.raza !== '—' && a.raza.toLowerCase() === b.raza.toLowerCase(), 30);
    add(a.tamano && b.tamano && a.tamano === b.tamano, 15);
    add(a.alcaldia && b.alcaldia && a.alcaldia === b.alcaldia, 25);
    add(a.color && b.color && a.color !== '—' && a.color.toLowerCase() === b.color.toLowerCase(), 15);
    return max ? Math.round((score / max) * 100) : 0;
  }

  function renderCoincidencias(list) {
    var cont = document.getElementById('contenedorMatches');
    if (!cont) return;
    var umbralEl = document.getElementById('umbralMatch');
    var umbral = parseInt(umbralEl && umbralEl.value ? umbralEl.value : '50', 10) || 50;
    var pares = [];
    for (var i = 0; i < list.length; i++) {
      for (var j = i + 1; j < list.length; j++) {
        var s = afinidad(list[i], list[j]);
        if (s >= umbral) pares.push({ a: list[i], b: list[j], score: s });
      }
    }
    pares.sort(function (x, y) { return y.score - x.score; });
    var badge = document.getElementById('badgeMatches');
    if (badge) badge.textContent = String(pares.length);

    if (!list.length) {
      cont.innerHTML = '<div class="col-12 text-center text-muted py-5">Sin reportes en la BD para cruzar.</div>';
      return;
    }
    if (!pares.length) {
      cont.innerHTML = '<div class="col-12"><div class="text-center text-muted py-5 bg-white border rounded">' +
        'Sin coincidencias ≥ ' + umbral + '% entre ' + list.length + ' reportes. Baja el umbral a 25%.</div></div>';
      return;
    }

    cont.innerHTML = pares.slice(0, 40).map(function (p) {
      var fa = esc(p.a.folio);
      var fb = esc(p.b.folio);
      var colorBarra = p.score >= 80 ? 'bg-success' : (p.score >= 60 ? 'bg-warning' : 'bg-secondary');
      return '<div class="col-12"><div class="card shadow-sm border-0 mb-2"><div class="card-body">' +
        '<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">' +
        '<span class="badge bg-dark">Afinidad ' + p.score + '%</span>' +
        '<div class="progress flex-grow-1 mx-2" style="height:8px;max-width:200px">' +
        '<div class="progress-bar ' + colorBarra + '" style="width:' + p.score + '%"></div></div>' +
        '<div class="btn-group btn-group-sm">' +
        '<button type="button" class="btn btn-outline-secondary" data-act="ver-exp" data-folio="' + fa + '" title="Ver caso A"><i class="bi bi-folder2-open"></i> A</button>' +
        '<button type="button" class="btn btn-outline-secondary" data-act="ver-exp" data-folio="' + fb + '" title="Ver caso B"><i class="bi bi-folder2-open"></i> B</button>' +
        '<button type="button" class="btn btn-outline-danger" data-act="pdf-exp" data-folio="' + fa + '" title="PDF caso A"><i class="bi bi-file-earmark-pdf"></i></button>' +
        '<button type="button" class="btn btn-outline-danger" data-act="pdf-exp" data-folio="' + fb + '" title="PDF caso B"><i class="bi bi-file-earmark-pdf"></i></button>' +
        '</div></div>' +
        '<div class="row g-3 align-items-center">' +
        '<div class="col-md-5">' +
        '<div class="d-flex gap-2 align-items-start">' +
        '<img src="' + esc(p.a.foto || '') + '" alt="" class="rounded" style="width:56px;height:56px;object-fit:cover;background:#eee" onerror="this.style.opacity=.3">' +
        '<div><div class="fw-bold text-guinda">' + fa + '</div>' +
        '<div class="small">' + esc(p.a.mascota) + ' · ' + esc(p.a.raza) + '</div>' +
        '<div class="small text-muted">' + esc(p.a.alcaldia) + ' · ' + esc(p.a.estatus_bd || p.a.estatus || '') + '</div>' +
        '<div class="small text-muted"><i class="bi bi-person"></i> ' + esc(p.a.tutor || '—') + '</div>' +
        '<div class="mt-1">' +
        '<button type="button" class="btn btn-sm btn-outline-primary me-1" data-act="ver-exp" data-folio="' + fa + '"><i class="bi bi-folder2-open me-1"></i>Expediente</button>' +
        '<button type="button" class="btn btn-sm btn-outline-danger" data-act="pdf-exp" data-folio="' + fa + '"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</button>' +
        '</div></div></div></div>' +
        '<div class="col-md-2 text-center"><i class="bi bi-arrow-left-right fs-4 text-muted"></i><div class="small text-muted mt-1">' + p.score + '%</div></div>' +
        '<div class="col-md-5">' +
        '<div class="d-flex gap-2 align-items-start">' +
        '<img src="' + esc(p.b.foto || '') + '" alt="" class="rounded" style="width:56px;height:56px;object-fit:cover;background:#eee" onerror="this.style.opacity=.3">' +
        '<div><div class="fw-bold text-guinda">' + fb + '</div>' +
        '<div class="small">' + esc(p.b.mascota) + ' · ' + esc(p.b.raza) + '</div>' +
        '<div class="small text-muted">' + esc(p.b.alcaldia) + ' · ' + esc(p.b.estatus_bd || p.b.estatus || '') + '</div>' +
        '<div class="small text-muted"><i class="bi bi-person"></i> ' + esc(p.b.tutor || '—') + '</div>' +
        '<div class="mt-1">' +
        '<button type="button" class="btn btn-sm btn-outline-primary me-1" data-act="ver-exp" data-folio="' + fb + '"><i class="bi bi-folder2-open me-1"></i>Expediente</button>' +
        '<button type="button" class="btn btn-sm btn-outline-danger" data-act="pdf-exp" data-folio="' + fb + '"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</button>' +
        '</div></div></div></div>' +
        '</div></div></div></div>';
    }).join('');
  }

  function renderTabla(list) {
    var tbody = document.querySelector('#tablaReportes tbody');
    if (!tbody) return;
    if (!list.length) {
      tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-5">Sin expedientes en la base de datos.</td></tr>';
      return;
    }
    tbody.innerHTML = list.map(function (r) {
      var folio = esc(r.folio);
      return '<tr data-folio="' + folio + '">' +
        '<td class="ps-4"><img src="' + esc(r.foto) + '" style="width:48px;height:48px;object-fit:cover;border-radius:8px;background:#eee" alt=""></td>' +
        '<td class="fw-bold small">' + folio + '</td>' +
        '<td><div class="fw-semibold">' + esc(r.mascota) + '</div><div class="small text-muted">' + esc(r.raza) +
          (r.tamano && r.tamano !== '—' ? ' · ' + esc(r.tamano) : '') + '</div></td>' +
        '<td class="small">' + esc(r.tutor) + '</td>' +
        '<td class="small">' + esc(r.alcaldia) + '</td>' +
        '<td><span class="badge bg-danger">' + esc(r.tipo) + '</span></td>' +
        '<td><span class="badge bg-secondary">' + esc(r.estatus_bd || r.estatus) + '</span></td>' +
        '<td class="text-center small"><strong>' + (r.antiguedad_dias || 0) + '</strong> días</td>' +
        '<td class="text-end pe-4 text-nowrap">' +
          '<button type="button" class="btn btn-sm btn-outline-secondary me-1" data-act="ver-exp" data-folio="' + folio + '" title="Ver expediente"><i class="bi bi-folder2-open"></i></button>' +
          '<button type="button" class="btn btn-sm btn-outline-primary me-1" data-act="avanzar-exp" data-folio="' + folio + '" title="Avanzar estatus"><i class="bi bi-arrow-right-circle"></i></button>' +
          '<button type="button" class="btn btn-sm btn-outline-danger" data-act="pdf-exp" data-folio="' + folio + '" title="Constancia PDF"><i class="bi bi-file-earmark-pdf"></i></button>' +
        '</td></tr>';
    }).join('');
    var tot = document.getElementById('repTotal');
    if (tot) tot.textContent = String(list.length);
    var mos = document.getElementById('repMostrando');
    if (mos) mos.textContent = String(list.length);
  }

  async function cargarDesdeAPI() {
    var listRaw = [];
    try {
      var r = await fetch('/api/admin/reportes', {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });
      var j = await r.json();
      var d = j.data !== undefined ? j.data : j;
      listRaw = (d && d.mascotas) ? d.mascotas : (Array.isArray(d) ? d : []);
      if (d && d.database) {
        window.__RUPE_DB__ = d.database;
        console.info('[RUPE] BD reportes:', d.database, 'total:', listRaw.length);
      }
    } catch (e) {
      console.warn('[RUPE] API reportes', e);
      listRaw = Array.isArray(window.__RUPE_REPORTES__) ? window.__RUPE_REPORTES__ : [];
    }

    // También datos inyectados por PHP
    if (!listRaw.length && window.RUPE_ADMIN_INIT && Array.isArray(window.RUPE_ADMIN_INIT.mascotas)) {
      listRaw = window.RUPE_ADMIN_INIT.mascotas;
    }

    var list = listRaw.map(mapRow).filter(Boolean);
    window.__RUPE_REPORTES__ = listRaw;
    window.reportesRUPE = list;
    if (typeof window.cargarReportesDesdeBD === 'function') {
      try { window.cargarReportesDesdeBD(listRaw); } catch (_) {}
    }

    renderTabla(list);
    renderBandeja(list);
    renderCoincidencias(list);

    // Re-exponer para pestañas
    window.renderizarValidacion = function () { renderBandeja(window.reportesRUPE || []); };
    window.renderizarCoincidencias = function (avisar) { renderCoincidencias(window.reportesRUPE || []); };
    window.ejecutarCruceCoincidencias = function () { renderCoincidencias(window.reportesRUPE || []); };
    window.renderizarTablaReportes = function () { renderTabla(window.reportesRUPE || []); };
    window.ejecutarCruceCoincidencias = function () { renderCoincidencias(window.reportesRUPE || []); };

    return list;
  }

  function onReady(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  onReady(function () {
    cargarDesdeAPI();
    setTimeout(cargarDesdeAPI, 1500);

    // Clicks en sub-tabs
    document.querySelectorAll('#subTabReportes .nav-link').forEach(function (link) {
      link.addEventListener('click', function () {
        setTimeout(function () {
          var list = window.reportesRUPE || [];
          var t = link.getAttribute('data-subtab') || '';
          if (t.indexOf('validacion') !== -1) renderBandeja(list);
          if (t.indexOf('coincidencias') !== -1) renderCoincidencias(list);
          if (t.indexOf('listado') !== -1) renderTabla(list);
        }, 50);
      });
    });

    // Clicks: Expedientes (acciones) + Bandeja Validación
    document.addEventListener('click', function (ev) {
      var btn = ev.target.closest('button[data-act][data-folio], button[data-folio]');
      if (!btn) return;
      var folio = btn.getAttribute('data-folio');
      if (!folio) return;
      var act = btn.getAttribute('data-act') || '';
      ev.preventDefault();
      try {
        if (act === 'ver-exp' || act === 'exp') {
          if (typeof window.verExpediente === 'function') window.verExpediente(folio);
          else if (typeof verExpediente === 'function') verExpediente(folio);
          else alert('Expediente: ' + folio);
        } else if (act === 'avanzar-exp') {
          if (typeof window.avanzarEstatus === 'function') window.avanzarEstatus(folio);
          else if (typeof avanzarEstatus === 'function') avanzarEstatus(folio);
        } else if (act === 'pdf-exp') {
          if (typeof window.constanciaDesdeListado === 'function') window.constanciaDesdeListado(folio);
          else if (typeof constanciaDesdeListado === 'function') constanciaDesdeListado(folio);
          else if (typeof window.verDetallePDF === 'function') window.verDetallePDF(folio);
          else if (typeof verDetallePDF === 'function') verDetallePDF(folio);
        } else if (act === 'validar') {
          if (typeof window.validarReporte === 'function') window.validarReporte(folio, btn);
          else if (typeof validarReporte === 'function') validarReporte(folio, btn);
        }
      } catch (err) {
        console.warn('[RUPE] accion expediente', act, folio, err);
      }
    });
  });
})();
</script>


<script>
/* ACCIONES EXPEDIENTES — handler final (no depende de otros scopes) */
(function () {
  function listaReportes() {
    if (Array.isArray(window.reportesRUPE) && window.reportesRUPE.length) return window.reportesRUPE;
    if (Array.isArray(window.__RUPE_REPORTES__) && window.__RUPE_REPORTES__.length) {
      return window.__RUPE_REPORTES__.map(function (m) {
        if (m && m.folio && m.mascota) return m;
        return {
          id_mascota: m.id_mascota || m.id || 0,
          folio: String(m.folio || ''),
          mascota: m.nombre || m.mascota || 'Sin nombre',
          raza: m.raza_nombre || m.raza || '—',
          tamano: m.tamano || '—',
          color: m.color || '—',
          alcaldia: m.alcaldia_nombre || m.alcaldia || '—',
          colonia: m.colonia || '',
          tipo: 'Extraviado',
          estatus: m.estatus || 'En Búsqueda',
          estatus_bd: m.estatus || '',
          fecha: String(m.created_at || m.fecha_extravio || '').slice(0, 10),
          fecha_alta: String(m.created_at || '').slice(0, 10),
          tutor: m.tutor_nombre || m.tutor || '—',
          tel: m.tutor_telefono || '',
          chip: m.microchip || m.collar || '',
          obs: m.senas_particulares || '',
          foto: (m.foto_url || m.foto || '').indexOf('/') === 0 || (m.foto_url || m.foto || '').indexOf('http') === 0 ? (m.foto_url || m.foto || '') : ((m.foto_url || m.foto) ? '/assets/uploads/' + String(m.foto_url || m.foto).replace(/^.*\//, '') : ''),
          antiguedad_dias: m.antiguedad_dias != null ? m.antiguedad_dias : 0
        };
      });
    }
    return [];
  }

  function toastMsg(msg, tipo) {
    if (typeof window.mostrarToast === 'function') window.mostrarToast(msg, tipo || 'info');
    else if (window.RUPEAdmin && window.RUPEAdmin.toast) window.RUPEAdmin.toast(msg, tipo || 'info');
    else console.info('[RUPE]', msg);
  }

  function openModal(id) {
    var el = document.getElementById(id);
    if (!el) return false;
    if (window.bootstrap && bootstrap.Modal) {
      bootstrap.Modal.getOrCreateInstance(el).show();
      return true;
    }
    el.classList.add('show');
    el.style.display = 'block';
    el.removeAttribute('aria-hidden');
    document.body.classList.add('modal-open');
    return true;
  }

  window.verExpediente = function (folio) {
    var lista = listaReportes();
    var r = lista.find(function (x) { return String(x.folio) === String(folio); });
    if (!r) {
      toastMsg('No se encontró el folio ' + folio, 'warning');
      return;
    }
    var cuerpo = document.getElementById('cuerpoExpediente');
    if (!cuerpo) {
      toastMsg('Modal de expediente no disponible', 'error');
      return;
    }
    var dias = r.antiguedad_dias != null ? r.antiguedad_dias : 0;
    cuerpo.innerHTML =
      '<div class="d-flex gap-3 mb-3 border-bottom pb-3">' +
      '<img src="' + (r.foto || '') + '" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:10px;background:#eee" onerror="this.onerror=null;this.src=\'data:image/svg+xml,' + encodeURIComponent('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"80\" height=\"80\"><rect fill=\"#ddd\" width=\"80\" height=\"80\"/><text x=\"40\" y=\"44\" text-anchor=\"middle\" fill=\"#999\" font-size=\"10\">Sin foto</text></svg>') + '\'">' +
      '<div><h5 class="fw-bold mb-1">' + (r.mascota || '—') + ' · ' + (r.raza || '') + '</h5>' +
      '<div class="small text-muted">Folio <strong>' + (r.folio || '') + '</strong></div>' +
      '<span class="badge bg-secondary">' + (r.estatus_bd || r.estatus || '') + '</span></div></div>' +
      '<div class="row g-2 small">' +
      '<div class="col-md-6"><div class="p-2 bg-light rounded border">' +
      '<div><strong>Tamaño:</strong> ' + (r.tamano || '—') + '</div>' +
      '<div><strong>Color:</strong> ' + (r.color || '—') + '</div>' +
      '<div><strong>Chip/collar:</strong> ' + (r.chip || '—') + '</div></div></div>' +
      '<div class="col-md-6"><div class="p-2 bg-light rounded border">' +
      '<div><strong>Tutor:</strong> ' + (r.tutor || '—') + '</div>' +
      '<div><strong>Tel:</strong> ' + (r.tel || '—') + '</div>' +
      '<div><strong>Alcaldía:</strong> ' + (r.alcaldia || '—') + (r.colonia ? ', ' + r.colonia : '') + '</div></div></div>' +
      '<div class="col-12"><div class="p-2 border rounded"><strong>Señas:</strong> ' + (r.obs || 'Sin registro') + '</div></div>' +
      '<div class="col-12 text-muted">Antigüedad: <strong>' + dias + '</strong> días · Registro: ' + (r.fecha_alta || r.fecha || '—') + '</div>' +
      '</div>';
    openModal('modalExpediente');
  };

  window.avanzarEstatus = function (folio) {
    var lista = listaReportes();
    var r = lista.find(function (x) { return String(x.folio) === String(folio); });
    if (!r) {
      toastMsg('No se encontró el folio ' + folio, 'warning');
      return;
    }
    var flujo = ['Pendiente', 'Validado', 'En Seguimiento', 'Resuelto'];
    var actual = r.estatus || 'Pendiente';
    var i = flujo.indexOf(actual);
    if (i < 0) i = 0;
    if (i >= flujo.length - 1) {
      toastMsg(folio + ' ya está en ' + actual, 'info');
      return;
    }
    var nuevo = flujo[i + 1];
    r.estatus = nuevo;
    // Persistir en BD si hay id
    var id = r.id_mascota || r.id;
    if (id) {
      var mapBd = { 'Pendiente': 'En Búsqueda', 'Validado': 'En Búsqueda', 'En Seguimiento': 'En Resguardo', 'Resuelto': 'Localizado' };
      var estatusBd = mapBd[nuevo] || 'En Búsqueda';
      r.estatus_bd = estatusBd;
      fetch('/api/admin/reportes/' + encodeURIComponent(id), {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': window.__CSRF__ || '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ estatus: estatusBd, _csrf: window.__CSRF__ || '' })
      }).then(function (res) {
        return res.json().catch(function () { return {}; });
      }).then(function (data) {
        if (data && data.success === false) toastMsg(data.message || 'No se pudo actualizar en BD', 'warning');
        else toastMsg(folio + ' → ' + nuevo, 'success');
      }).catch(function () {
        toastMsg(folio + ' → ' + nuevo + ' (solo local)', 'info');
      });
    } else {
      toastMsg(folio + ' → ' + nuevo, 'success');
    }
    // Refrescar tabla si existe
    if (typeof window.renderizarTablaReportes === 'function') {
      try { window.renderizarTablaReportes(); } catch (_) {}
    }
  };

  window.constanciaDesdeListado = function (folio) {
    if (typeof window.verDetallePDF === 'function') {
      try { window.verDetallePDF(folio); return; } catch (e) { console.warn(e); }
    }
    // Fallback: abrir expediente
    window.verExpediente(folio);
    toastMsg('Vista de constancia para ' + folio, 'info');
  };

  // Captura clics en botones de la tabla (capturing phase = gana a otros handlers)
  document.addEventListener('click', function (ev) {
    var btn = ev.target.closest('button[data-folio]');
    if (!btn) return;
    // Solo botones dentro de #tablaReportes o con data-act de expedientes
    var act = btn.getAttribute('data-act') || '';
    var folio = btn.getAttribute('data-folio');
    if (!folio) return;
    var enTabla = btn.closest('#tablaReportes');
    var esAccion = act === 'ver-exp' || act === 'avanzar-exp' || act === 'pdf-exp' ||
      btn.classList.contains('btn-ver-exp') || btn.classList.contains('btn-avanzar-exp') || btn.classList.contains('btn-pdf-exp');
    if (!enTabla && !esAccion) return;

    ev.preventDefault();
    ev.stopPropagation();

    if (act === 'ver-exp' || act === 'exp' || btn.classList.contains('btn-ver-exp')) {
      window.verExpediente(folio);
    } else if (act === 'avanzar-exp' || btn.classList.contains('btn-avanzar-exp')) {
      window.avanzarEstatus(folio);
    } else if (act === 'pdf-exp' || btn.classList.contains('btn-pdf-exp')) {
      window.constanciaDesdeListado(folio);
    }
  }, true); // capture = true

  console.info('[RUPE] Acciones Expedientes listas');
})();
</script>

  <script src="/assets/js/rupe-responsive-nav.js?v=20260925nav" defer></script>
</body>
</html>
