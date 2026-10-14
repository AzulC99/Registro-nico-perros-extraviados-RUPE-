<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RUPE - Auditoría</title>
  
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
            <a class="nav-link nav-modulo active" href="/admin/auditoria">
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

    <section id="modulo-auditoria" class="modulo-seccion activo">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="fw-bold text-dark m-0">Bitácora de Auditoría</h3>
          <p class="text-muted small m-0">Registro histórico de acciones y seguridad del sistema.</p>
        </div>
        <button type="button" class="btn btn-outline-dark btn-sm" id="btnExportBitacora">
          <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar Bitácora
        </button>
      </div>

      <div class="card shadow-sm p-3 mb-4">
        <div class="row g-2 align-items-center">
          <div class="col-md-4">
            <input type="text" id="auditBuscar" class="form-control form-control-sm" placeholder="Buscar por usuario, IP o acción...">
          </div>
          <div class="col-md-3">
            <select id="auditAccion" class="form-select form-select-sm">
              <option value="" selected>Todas las Acciones</option>
              <option value="login">Inicio de sesión</option>
              <option value="crear">Creación</option>
              <option value="actualizar">Modificación</option>
              <option value="eliminar">Eliminación</option>
              <option value="estatus">Cambio de estatus</option>
              <option value="config">Configuración</option>
            </select>
          </div>
          <div class="col-md-3">
            <input type="date" id="auditFecha" class="form-control form-control-sm" value="">
          </div>
          <div class="col-md-2">
            <button type="button" id="auditFiltrar" class="btn btn-sm btn-primary w-100" style="background-color: var(--color-primary); border:none;">
              <i class="bi bi-search me-1"></i> Filtrar
            </button>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaAuditoria">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">ID Log</th>
                  <th>Fecha y Hora</th>
                  <th>Usuario</th>
                  <th>Acción</th>
                  <th>Módulo Afectado</th>
                  <th>Dirección IP</th>
                  <th class="pe-4">Detalle / Evento</th>
                </tr>
              </thead>
              <tbody id="tbodyAuditoria">
                <?php
                $logs = is_array($auditoria ?? null) ? $auditoria : [];
                if (count($logs) === 0): ?>
                <tr>
                  <td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                    No hay registros de auditoría en la base de datos.
                  </td>
                </tr>
                <?php else:
                  foreach ($logs as $a):
                    $idLog = (int) ($a['id_auditoria'] ?? 0);
                    $fecha = (string) ($a['created_at'] ?? '');
                    $usuario = (string) ($a['usuario_nombre'] ?? '');
                    if ($usuario === '') {
                      $usuario = (string) ($a['usuario_correo'] ?? '');
                    }
                    if ($usuario === '' && !empty($a['id_usuario'])) {
                      $usuario = 'Usuario #' . (int) $a['id_usuario'];
                    }
                    if ($usuario === '') {
                      $usuario = 'Sistema';
                    }
                    $accion = (string) ($a['accion'] ?? '');
                    $modulo = (string) ($a['modulo'] ?? '');
                    if ($modulo === '') {
                      $modulo = (string) ($a['tabla'] ?? '');
                    }
                    if ($modulo === '' && class_exists(\App\Models\AuditoriaDAO::class)) {
                      $modulo = \App\Models\AuditoriaDAO::moduloDesdeAccion($accion);
                    }
                    if ($modulo === '') {
                      $modulo = 'Sistema';
                    }
                    $ip = (string) ($a['ip'] ?? '');
                    if ($ip === '') {
                      $ip = '—';
                    }
                    $detalle = (string) ($a['detalle_texto'] ?? '');
                    if ($detalle === '') {
                      $detalleRaw = $a['detalle'] ?? null;
                      if (class_exists(\App\Models\AuditoriaDAO::class)) {
                        $detalle = \App\Models\AuditoriaDAO::detalleDesdeAccion(
                          $accion,
                          $modulo,
                          $detalleRaw,
                          !empty($a['id_registro']) ? (int) $a['id_registro'] : null
                        );
                      } elseif (is_string($detalleRaw) && $detalleRaw !== '') {
                        $decoded = json_decode($detalleRaw, true);
                        if (is_array($decoded)) {
                          $parts = [];
                          foreach ($decoded as $k => $v) {
                            if (is_scalar($v) || $v === null) {
                              $parts[] = $k . ': ' . (string) $v;
                            }
                          }
                          $detalle = $parts ? implode(' · ', $parts) : $detalleRaw;
                        } else {
                          $detalle = $detalleRaw;
                        }
                      } else {
                        $detalle = $accion !== '' ? ('Evento: ' . $accion) : 'Sin detalle';
                      }
                    }
                    $accionLower = strtolower($accion);
                    $badge = 'bg-secondary';
                    if (str_contains($accionLower, 'login') || str_contains($accionLower, 'auth')) {
                      $badge = 'bg-primary';
                    } elseif (str_contains($accionLower, 'crear') || str_contains($accionLower, 'insert') || str_contains($accionLower, 'create')) {
                      $badge = 'bg-success';
                    } elseif (str_contains($accionLower, 'eliminar') || str_contains($accionLower, 'delete')) {
                      $badge = 'bg-danger';
                    } elseif (str_contains($accionLower, 'actualizar') || str_contains($accionLower, 'update') || str_contains($accionLower, 'estatus') || str_contains($accionLower, 'modif')) {
                      $badge = 'bg-warning text-dark';
                    }
                ?>
                <tr data-accion="<?= htmlspecialchars($accion, ENT_QUOTES, 'UTF-8') ?>" data-usuario="<?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?>" data-ip="<?= htmlspecialchars($ip, ENT_QUOTES, 'UTF-8') ?>">
                  <td class="ps-4 fw-bold">#<?= $idLog ?></td>
                  <td class="small"><?= htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($accion, ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= htmlspecialchars($modulo !== '' ? $modulo : '—', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><code class="small"><?= htmlspecialchars($ip, ENT_QUOTES, 'UTF-8') ?></code></td>
                  <td class="pe-4 text-muted small"><?= htmlspecialchars($detalle, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

  </main>

  <div class="toast-container-custom" id="toastContainer"></div>

  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Este módulo es de solo consulta; no requiere lógica adicional.
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
/* Filtro cliente sobre filas de auditoría (datos BD) */
(function () {
  function applyFilter() {
    const q = (document.getElementById('auditBuscar') && document.getElementById('auditBuscar').value || '').toLowerCase().trim();
    const accionSel = (document.getElementById('auditAccion') && document.getElementById('auditAccion').value || '').toLowerCase();
    const fecha = (document.getElementById('auditFecha') && document.getElementById('auditFecha').value) || '';
    const rows = document.querySelectorAll('#tbodyAuditoria tr[data-accion]');
    let visible = 0;
    rows.forEach(function (tr) {
      const text = tr.textContent.toLowerCase();
      const acc = (tr.getAttribute('data-accion') || '').toLowerCase();
      const okQ = !q || text.indexOf(q) !== -1;
      const okA = !accionSel || acc.indexOf(accionSel) !== -1;
      const okF = !fecha || (tr.children[1] && tr.children[1].textContent.indexOf(fecha) !== -1);
      const show = okQ && okA && okF;
      tr.style.display = show ? '' : 'none';
      if (show) visible++;
    });
  }
  document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('auditFiltrar');
    if (btn) btn.addEventListener('click', applyFilter);
    const buscar = document.getElementById('auditBuscar');
    if (buscar) buscar.addEventListener('input', applyFilter);
    const accion = document.getElementById('auditAccion');
    if (accion) accion.addEventListener('change', applyFilter);
    const fecha = document.getElementById('auditFecha');
    if (fecha) fecha.addEventListener('change', applyFilter);
  });
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
  <script src="/assets/js/rupe-responsive-nav.js?v=20260925nav" defer></script>

<script>
(function () {
  'use strict';

  function cellText(el) {
    if (!el) return '';
    var t = (el.innerText || el.textContent || '').replace(/\s+/g, ' ').trim();
    if (/[",\n\r]/.test(t)) t = '"' + t.replace(/"/g, '""') + '"';
    return t;
  }

  function exportarBitacoraAuditoria() {
    try {
      var table = document.getElementById('tablaAuditoria');
      if (!table) {
        alert('No se encontró la tabla de bitácora.');
        return;
      }
      var headRow = table.querySelector('thead tr');
      var tbody = table.querySelector('tbody') || document.getElementById('tbodyAuditoria');
      if (!headRow || !tbody) {
        alert('La bitácora no tiene datos para exportar.');
        return;
      }

      var headers = Array.prototype.slice.call(headRow.children);
      var cols = headers.length;
      var lines = [];
      lines.push(headers.map(cellText).join(','));

      var dataRows = 0;
      tbody.querySelectorAll('tr').forEach(function (tr) {
        if (tr.querySelector('td[colspan]')) return;
        var st = window.getComputedStyle ? window.getComputedStyle(tr) : null;
        if (tr.style.display === 'none' || tr.classList.contains('d-none') || (st && st.display === 'none')) return;
        var cells = tr.children;
        if (!cells || !cells.length) return;
        var vals = [];
        for (var i = 0; i < cols && i < cells.length; i++) vals.push(cellText(cells[i]));
        if (vals.every(function (v) { return !v; })) return;
        lines.push(vals.join(','));
        dataRows++;
      });

      if (dataRows === 0) {
        alert('No hay registros visibles para exportar. Ajusta los filtros o espera a que carguen los logs.');
        return;
      }

      var csv = '\uFEFF' + lines.join('\r\n');
      var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      var name = 'Bitacora_Auditoria_RUPE_' + new Date().toISOString().slice(0, 10) + '.csv';
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url;
      a.download = name;
      document.body.appendChild(a);
      a.click();
      setTimeout(function () {
        try { document.body.removeChild(a); } catch (e) {}
        URL.revokeObjectURL(url);
      }, 250);

      if (typeof mostrarToast === 'function') {
        mostrarToast('Bitácora exportada: ' + dataRows + ' registro(s).', 'success');
      }
    } catch (err) {
      console.error('exportarBitacoraAuditoria', err);
      alert('Error al exportar la bitácora.');
    }
  }

  window.exportarBitacoraAuditoria = exportarBitacoraAuditoria;

  function bindBtn() {
    var btn = document.getElementById('btnExportBitacora');
    if (!btn || btn._exportBound) return;
    btn._exportBound = true;
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      exportarBitacoraAuditoria();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindBtn);
  } else {
    bindBtn();
  }
})();
</script>

</body>
</html>
