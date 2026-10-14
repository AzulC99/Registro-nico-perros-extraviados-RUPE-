<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RUPE - Catálogo de Razas</title>
  
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
            <a class="nav-link nav-modulo active" href="/admin/razas">
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

    <section id="modulo-razas" class="modulo-seccion activo">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="fw-bold text-dark m-0">Catálogo de Razas</h3>
          <p class="text-muted small m-0">Parámetros de razas caninas — Alta, edición y baja lógica.</p>
          <span class="catalogo-counter"><i class="bi bi-collection me-1"></i><span id="contadorRazas"><?= (int) count(is_array($razas ?? null) ? $razas : []) ?></span> razas en BD</span>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-success btn-sm" id="btnExportRazasExcel" onclick="exportarCatalogoExcel('tablaRazas', 'Razas_RUPE')">
            <i class="bi bi-file-earmark-excel me-1"></i> Exportar Excel
          </button>
          <button class="btn btn-primary btn-sm" onclick="abrirModalRaza()"><i class="bi bi-plus-lg me-1"></i> Agregar Raza</button>
        </div>
      </div>

      <!-- Barra de búsqueda -->
      <div class="card shadow-sm p-3 mb-3">
        <div class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Buscar</label>
            <input type="text" id="filtroRazaBuscar" class="form-control form-control-sm" placeholder="Nombre o código…">
          </div>
          <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Estado</label>
            <select id="filtroRazaEstado" class="form-select form-select-sm">
              <option value="">Todos</option>
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Especie</label>
            <select id="filtroRazaEspecie" class="form-select form-select-sm">
              <option value="perro" selected>perro</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Tamaño</label>
            <select id="filtroRazaTamano" class="form-select form-select-sm">
              <option value="">Todos</option>
              <option value="Miniatura">Miniatura</option>
              <option value="Pequeño">Pequeño</option>
              <option value="Mediano">Mediano</option>
              <option value="Grande">Grande</option>
              <option value="Gigante">Gigante</option>
            </select>
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary flex-fill" id="btnFiltrarRazas"
                    onclick="if(window.aplicarFiltrosRazas)aplicarFiltrosRazas(true);return false;">
              <i class="bi bi-funnel me-1"></i> Filtrar
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary flex-fill"
                    onclick="if(window.limpiarFiltrosRazas)limpiarFiltrosRazas();return false;">
              Limpiar
            </button>
          </div>
        </div>
      </div>


      <div class="card shadow-sm">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaRazas">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">Código</th>
                  <th>Nombre de la Raza</th>
                  <th>Especie</th>
                  <th>Tamaño</th>
                  <th>Registros</th>
                  <th>Estado</th>
                  <th>Fecha Alta</th>
                  <th class="text-end pe-4">Acciones</th>
                </tr>
              </thead>
              <tbody id="tbodyRazas">
                <?php
                try {
                    $listaRazas = (new \App\Models\RazaDAO())->listAllAdmin();
                } catch (\Throwable $e) {
                    $listaRazas = is_array($razas ?? null) ? $razas : [];
                }
                if (!is_array($listaRazas) || !$listaRazas): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No hay razas en la base de datos.</td></tr>
                <?php else:
                  foreach ($listaRazas as $rz):
                    $rid = (int)($rz['id_raza'] ?? 0);
                    if ($rid <= 0) continue;
                    $codigo = 'RZ-' . str_pad((string)$rid, 2, '0', STR_PAD_LEFT);
                    $nombre = htmlspecialchars((string)($rz['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $esp = 'perro';
                    $espLbl = 'perro';
                    $tamRaw = trim((string)($rz['tamano'] ?? ''));
                    if ($tamRaw === '' || strcasecmp($tamRaw, 'null') === 0) {
                      $tamRaw = trim((string)($rz['tamano_mascota'] ?? ''));
                    }
                    if ($tamRaw === '' || strcasecmp($tamRaw, 'null') === 0) {
                      $tamRaw = '';
                    }
                    $tam = htmlspecialchars($tamRaw !== '' ? $tamRaw : '—', ENT_QUOTES, 'UTF-8');
                    $regs = (int)($rz['registros'] ?? 0);
                    $act = (int)($rz['activo'] ?? 1) === 1;
                    $fecha = !empty($rz['created_at']) ? date('d/m/Y', strtotime((string)$rz['created_at'])) : '—';
                ?>
                <tr data-id="<?= $rid ?>" data-estado="<?= $act ? 'Activo' : 'Inactivo' ?>" data-tamano="<?= htmlspecialchars($tamRaw, ENT_QUOTES, 'UTF-8') ?>" data-especie="<?= htmlspecialchars($esp, ENT_QUOTES, 'UTF-8') ?>">
                  <td class="ps-4 fw-bold"><?= $codigo ?></td>
                  <td><?= $nombre ?></td>
                  <td><?= $espLbl ?></td>
                  <td><?= $tam ?></td>
                  <td class="text-center"><?= $regs ?></td>
                  <td><span class="badge <?= $act ? 'bg-success' : 'bg-secondary' ?>"><?= $act ? 'Activo' : 'Inactivo' ?></span></td>
                  <td><?= htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-end pe-4 text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit-raza" data-id="<?= $rid ?>" title="Editar"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="toggle-raza" data-id="<?= $rid ?>" data-activo="<?= $act ? 1 : 0 ?>" title="Activar/Desactivar"><i class="bi bi-toggle-on"></i></button>
                  </td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- MODAL CRUD PARA RAZAS -->
  <div class="modal fade" id="modalRaza" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header text-white" style="background-color: var(--color-primary);">
          <h5 class="modal-title fw-bold" id="modalRazaTitulo">Agregar Raza</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formRaza" onsubmit="event.preventDefault(); if(window.guardarRaza){return guardarRaza(event);} if(typeof guardarRaza==='function'){return guardarRaza(event);} alert('Error: guardarRaza no cargó. Recarga la página.'); return false;">
            <input type="hidden" id="inputRazaId" value="" inputmode="numeric" pattern="[0-9]*">
            <div class="mb-3">
              <label class="form-label fw-semibold">Código de Raza</label>
              <input type="text" id="inputRazaCodigo" class="form-control" readonly value="(automático)" placeholder="Se asigna al guardar (RZ-##)">
              <div class="form-text">El código RZ-## se genera automáticamente con el ID de la base de datos.</div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Nombre de la Raza *</label>
              <div class="input-group">
                <input type="text" id="inputRazaNombre" class="form-control" required maxlength="80"
                       placeholder="Ej: Dálmata" autocomplete="off"
                       pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9][A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9\s\-/]{1,78}$">
                <span class="input-group-text d-none text-success" id="okRazaNombre"><i class="bi bi-check-circle-fill"></i></span>
              </div>
              <div class="form-text">Mínimo 2 caracteres (letras, números, espacios).</div>
              <div class="invalid-feedback">Escribe un nombre de raza válido (mín. 2 caracteres).</div>
            </div>
            <div class="row g-2">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Especie *</label>
                <div class="input-group">
                  <select id="inputRazaEspecie" class="form-select" required>
                    <option value="perro" selected>perro</option>
                  </select>
                  <span class="input-group-text text-success" id="okRazaEspecie"><i class="bi bi-check-circle-fill"></i></span>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Tamaño *</label>
                <div class="input-group">
                  <select id="inputRazaTamano" class="form-select" required>
                    <option value="">Seleccionar tamaño…</option>
                    <?php
                      $opsTam = ['Miniatura','Pequeño','Mediano','Grande','Gigante'];
                      try {
                        $opsTam = (new \App\Models\RazaDAO())->listTamanosFromMascotas();
                      } catch (\Throwable $e) {}
                      foreach ($opsTam as $opT):
                        $opT = htmlspecialchars((string)$opT, ENT_QUOTES, 'UTF-8');
                        if ($opT === '') continue;
                    ?>
                    <option value="<?= $opT ?>"><?= $opT ?></option>
                    <?php endforeach; ?>
                  </select>
                  <span class="input-group-text d-none text-success" id="okRazaTamano"><i class="bi bi-check-circle-fill"></i></span>
                </div>
                <div class="form-text small">Elige un tamaño del catálogo / mascotas.</div>
                <div class="invalid-feedback">Selecciona el tamaño.</div>
              </div>
            </div>
            
            <div class="text-end mt-4">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn text-white" style="background-color: var(--color-primary); border:none;" id="btnGuardarRaza">Guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container-custom" id="toastContainer"></div>

  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  /* Solo BD — sin arrays demo ni filas inventadas */
  function abrirModalRaza() {
    const form = document.getElementById('formRaza');
    if (form) form.reset();
    const hid = document.getElementById('inputRazaId');
    if (hid) hid.value = '';
    const cod = document.getElementById('inputRazaCodigo');
    if (cod) cod.value = '(automático)';
    const tit = document.getElementById('modalRazaTitulo');
    if (tit) tit.textContent = 'Agregar Raza';
    const esp = document.getElementById('inputRazaEspecie');
    if (esp) esp.value = 'perro';
    const tam = document.getElementById('inputRazaTamano');
    if (tam) tam.value = '';
    const modal = document.getElementById('modalRaza');
    if (modal && window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(modal).show();
    }
  }
  window.abrirModalRaza = abrirModalRaza;
  window.abrirModalCrear = function () { abrirModalRaza(); };

  function mostrarToast(mensaje, tipo) {
    if (window.RUPEAdmin && typeof window.RUPEAdmin.toast === 'function') {
      window.RUPEAdmin.toast(mensaje, tipo || 'info');
      return;
    }
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const el = document.createElement('div');
    el.className = 'toast-custom alert bg-' + (tipo === 'danger' ? 'danger' : tipo === 'warning' ? 'warning' : tipo === 'info' ? 'info' : 'success') + ' text-white shadow-lg';
    el.textContent = mensaje;
    container.appendChild(el);
    setTimeout(function () { el.remove(); }, 3000);
  }
  window.mostrarToast = mostrarToast;

  document.addEventListener('DOMContentLoaded', function () {
    // Cargar SOLO desde API/BD
    setTimeout(function () {
      if (window.rupeLoadRazas) window.rupeLoadRazas();
      else if (window.RUPEAdmin && window.RUPEAdmin.loadRazas) window.RUPEAdmin.loadRazas();
    }, 200);
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

<script src="/assets/js/admin-app.js?v=20260926razaval1" defer></script>


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
window.__RUPE_DB__ = <?= json_encode(defined('DB_NAME') ? DB_NAME : '', JSON_UNESCAPED_UNICODE) ?>;
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  setTimeout(function () {
    if (window.rupeLoadRazas) window.rupeLoadRazas();
    else if (window.RUPEAdmin && window.RUPEAdmin.loadRazas) window.RUPEAdmin.loadRazas();
  }, 300);
});
</script>








<script>
(function () {
  function toastRaza(msg, tipo) {
    tipo = tipo || 'success';
    var c = document.getElementById('toastContainer');
    if (!c) {
      c = document.createElement('div');
      c.id = 'toastContainer';
      c.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;max-width:440px;';
      document.body.appendChild(c);
    }
    var colors = { success:'#198754', error:'#dc3545', warning:'#e8a33d', info:'#0d6efd', danger:'#dc3545' };
    var el = document.createElement('div');
    el.style.cssText = 'background:'+(colors[tipo]||colors.success)+';color:#fff;padding:14px 18px;border-radius:12px;margin-top:8px;box-shadow:0 8px 24px rgba(0,0,0,.25);font-size:14px;font-weight:600;';
    el.textContent = msg;
    c.appendChild(el);
    setTimeout(function(){ try{el.remove();}catch(e){} }, 5000);
  }

  window.guardarRaza = async function (e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    var nombreEl = document.getElementById('inputRazaNombre');
    var tamanoEl = document.getElementById('inputRazaTamano');
    var hid = document.getElementById('inputRazaId');
    var btn = document.getElementById('btnGuardarRaza') || document.querySelector('#formRaza button[type="submit"]');
    var nombre = (nombreEl && nombreEl.value || '').trim();
    var tamano = (tamanoEl && tamanoEl.value || '').trim();
    var idEdit = parseInt((hid && hid.value || '').trim(), 10) || 0;

    if (typeof window.rupeValidarRaza === 'function' && !window.rupeValidarRaza(true)) {
      toastRaza('Completa nombre (mín. 2) y tamaño de la raza.', 'warning');
      return false;
    }
    if (nombre.length < 2) { toastRaza('Nombre obligatorio (mín. 2 caracteres)', 'warning'); return false; }
    if (!tamano || tamano === '—') { toastRaza('Selecciona el tamaño', 'warning'); if (tamanoEl) tamanoEl.focus(); return false; }

    if (btn) {
      btn.disabled = true;
      btn.dataset._old = btn.innerHTML;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando…';
    }

    var csrf = window.__CSRF__ || '';
    try {
      var cr = await fetch('/api/csrf', { credentials:'same-origin', headers:{'Accept':'application/json'} });
      var cj = await cr.json();
      csrf = (cj.data && cj.data.csrf) || cj.csrf || csrf;
      if (csrf) window.__CSRF__ = csrf;
    } catch (_) {}

    var payload = { nombre: nombre, especie: 'perro', tamano: tamano, activo: 1, _csrf: csrf };
    if (idEdit > 0) payload.id = idEdit;

    async function postJson(url, method) {
      var res = await fetch(url, {
        method: method || 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf
        },
        body: JSON.stringify(payload)
      });
      var text = await res.text();
      var data = {};
      try { data = JSON.parse(text); } catch (_) {
        throw new Error('Respuesta no JSON (' + res.status + '): ' + text.slice(0, 100));
      }
      if (!res.ok || data.success === false) {
        throw new Error(data.message || ('HTTP ' + res.status));
      }
      return data;
    }

    try {
      var data = null;
      var lastErr = null;
      // 1) API MVC principal
      try {
        if (idEdit > 0) {
          data = await postJson('/api/admin/razas/' + encodeURIComponent(idEdit), 'PUT');
        } else {
          data = await postJson('/api/admin/razas', 'POST');
        }
      } catch (errApi) {
        lastErr = errApi;
        // 2) Fallback endpoint directo
        try {
          data = await postJson('/guardar-raza.php', 'POST');
        } catch (err2) {
          throw lastErr || err2;
        }
      }

      var row = (data && data.data) || {};
      var newId = parseInt(row.id_raza || row.id || idEdit || 0, 10) || 0;
      var cod = newId ? ('RZ-' + String(newId).padStart(2,'0')) : 'RZ—';
      var dbMsg = (row._database || window.__RUPE_DB__ || '');
      var tamSaved = row.tamano || tamano;
      toastRaza('✓ Guardado en tabla razas' + (dbMsg ? ' (BD ' + dbMsg + ')' : '') + ': ' + nombre + ' · ' + cod + ' · ' + tamSaved, 'success');
      var modal = document.getElementById('modalRaza');
      if (modal && window.bootstrap) {
        var inst = bootstrap.Modal.getInstance(modal);
        if (inst) inst.hide();
      }
      try {
        if (window.RUPEAdmin && window.RUPEAdmin.loadRazas) {
          await window.RUPEAdmin.loadRazas();
        } else if (window.rupeLoadRazas) {
          await window.rupeLoadRazas();
        }
      } catch (_) {}
      setTimeout(function () { location.reload(); }, 700);
    } catch (err) {
      console.error('[RUPE] guardarRaza', err);
      toastRaza(err.message || 'No se pudo guardar en la tabla razas', 'error');
    } finally {
      if (btn) {
        btn.disabled = false;
        if (btn.dataset._old) btn.innerHTML = btn.dataset._old;
      }
    }
    return false;
  };

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formRaza');
    if (form) {
      form.onsubmit = function (ev) {
        ev.preventDefault();
        window.guardarRaza(ev);
        return false;
      };
    }
  });
})();
</script>


<script>
window.RUPEAdmin = window.RUPEAdmin || {};
window.RUPEAdmin.loadRazas = async function () {
  try {
    var r = await fetch('/api/admin/razas', { credentials:'same-origin', headers:{'Accept':'application/json'} });
    var j = await r.json();
    var d = j.data !== undefined ? j.data : j;
    var list = Array.isArray(d) ? d : (Array.isArray(d.items) ? d.items : (Array.isArray(d.razas) ? d.razas : []));
    window.__RUPE_RAZAS__ = list;
    if (d && d.database) {
      window.__RUPE_DB__ = d.database;
      var badge = document.getElementById('badgeDbRazas');
      if (badge) badge.textContent = 'BD: ' + d.database;
    }
    var tbody = document.querySelector('#tablaRazas tbody') || document.getElementById('tbodyRazas');
    if (!tbody) return list;
    // No borrar filas PHP si la API viene vacía (auth/error temporal)
    if (!list.length) {
      if (tbody.querySelector('tr[data-id]')) return list;
      tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Sin razas en la base de datos.</td></tr>';
      return list;
    }
    tbody.innerHTML = list.map(function (rz) {
      var id = parseInt(rz.id_raza != null ? rz.id_raza : rz.id, 10) || 0;
      if (id <= 0) return '';
      var cod = 'RZ-' + String(id).padStart(2,'0');
      var nombre = String(rz.nombre || '').replace(/</g,'&lt;');
      var especie = 'perro';
      var tamano = String(rz.tamano || rz.tamano_mascota || rz.tamano_raza || '—').replace(/</g,'&lt;');
      if (!tamano || tamano === 'null') tamano = '—';
      var reg = rz.registros != null ? rz.registros : 0;
      var activo = Number(rz.activo) === 1 || rz.activo === true || rz.activo === '1';
      var estado = activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';
      var fecha = (rz.created_at || '').toString().slice(0,10);
      if (fecha) {
        var p = fecha.split('-');
        if (p.length === 3) fecha = p[2] + '/' + p[1] + '/' + p[0];
      }
      return '<tr data-id="'+id+'" data-tamano="'+tamano+'" data-especie="perro">'
        + '<td class="ps-3 fw-bold">'+cod+'</td>'
        + '<td>'+nombre+'</td>'
        + '<td>'+especie+'</td>'
        + '<td>'+tamano+'</td>'
        + '<td class="text-center">'+reg+'</td>'
        + '<td>'+estado+'</td>'
        + '<td class="small text-muted">'+fecha+'</td>'
        + '<td class="text-end pe-3 text-nowrap">'
        + '<button type="button" class="btn btn-sm btn-outline-primary me-1" data-action="edit-raza" data-id="'+id+'" title="Editar"><i class="bi bi-pencil"></i></button>'
        + '<button type="button" class="btn btn-sm btn-outline-secondary" data-action="toggle-raza" data-id="'+id+'" data-activo="'+(activo?1:0)+'" title="Activar/Desactivar"><i class="bi bi-toggle-on"></i></button>'
        + '</td></tr>';
    }).join('');
    var cont = document.getElementById('contadorRazas');
    if (cont) cont.textContent = String(list.length);
    return list;
  } catch (e) {
    console.warn('loadRazas', e);
    return [];
  }
};
document.addEventListener('DOMContentLoaded', function () {
  if (window.RUPEAdmin && window.RUPEAdmin.loadRazas) {
    window.RUPEAdmin.loadRazas();
  }
});
</script>

  <script src="/assets/js/rupe-responsive-nav.js?v=20260925nav" defer></script>

<script>
(function(){
  function fillTamanoSelect(extra) {
    var sel = document.getElementById('inputRazaTamano');
    if (!sel) return;
    var cur = sel.value;
    var base = ['Miniatura','Pequeño','Mediano','Grande','Gigante'];
    var all = base.slice();
    (extra || []).forEach(function(t){
      t = String(t || '').trim();
      if (t && all.indexOf(t) < 0) all.push(t);
    });
    sel.innerHTML = '<option value="">Seleccionar tamaño…</option>';
    all.forEach(function(t){
      var o = document.createElement('option');
      o.value = t; o.textContent = t;
      sel.appendChild(o);
    });
    if (cur) try { sel.value = cur; } catch(_){}
  }
  window.rupeFillTamanoRaza = fillTamanoSelect;
  var _lr = window.RUPEAdmin && window.RUPEAdmin.loadRazas;
  if (_lr) {
    window.RUPEAdmin.loadRazas = async function() {
      var list = await _lr.apply(this, arguments);
      try {
        var r = await fetch('/api/admin/razas', { credentials:'same-origin', headers:{'Accept':'application/json'} });
        var j = await r.json();
        var d = j.data || j;
        fillTamanoSelect(d.tamanos_mascotas || []);
        if (d.database) {
          window.__RUPE_DB__ = d.database;
          var b = document.getElementById('badgeDbRazas');
          if (b) b.textContent = 'BD: ' + d.database;
        }
      } catch(_){}
      return list;
    };
  }
  document.addEventListener('DOMContentLoaded', function(){ fillTamanoSelect([]); });
})();
</script>


<script>
(function () {
  function validNombreRaza(v) {
    v = (v || '').trim().replace(/\s+/g, ' ');
    if (v.length < 2 || v.length > 80) return false;
    return /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9][A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9\s\-\/]*$/.test(v);
  }
  function validTamano(v) {
    return String(v || '').trim() !== '';
  }
  function validEspecie(v) {
    return String(v || '').trim() === 'perro';
  }

  function bindRazaValid() {
    var nom = document.getElementById('inputRazaNombre');
    var tam = document.getElementById('inputRazaTamano');
    var esp = document.getElementById('inputRazaEspecie');
    var okN = document.getElementById('okRazaNombre');
    var okT = document.getElementById('okRazaTamano');
    var okE = document.getElementById('okRazaEspecie');

    function checkNombre() {
      if (!nom) return false;
      var ok = validNombreRaza(nom.value);
      nom.classList.toggle('is-valid', ok);
      nom.classList.toggle('is-invalid', !ok && (nom.value || '').trim() !== '');
      if (okN) okN.classList.toggle('d-none', !ok);
      return ok;
    }
    function checkTamano() {
      if (!tam) return false;
      var ok = validTamano(tam.value);
      tam.classList.toggle('is-valid', ok);
      tam.classList.toggle('is-invalid', !ok && (tam.value || '') === '');
      // show invalid only after interaction if empty on submit
      if (!ok && (tam.value || '') === '') {
        /* keep neutral until submit */
      }
      if (okT) okT.classList.toggle('d-none', !ok);
      return ok;
    }
    function checkEspecie() {
      if (!esp) return true;
      var ok = validEspecie(esp.value);
      esp.classList.toggle('is-valid', ok);
      if (okE) okE.classList.toggle('d-none', !ok);
      return ok;
    }

    if (nom) {
      nom.addEventListener('input', checkNombre);
      nom.addEventListener('blur', checkNombre);
    }
    if (tam) {
      tam.addEventListener('change', checkTamano);
      tam.addEventListener('blur', checkTamano);
    }
    if (esp) {
      esp.addEventListener('change', checkEspecie);
      checkEspecie();
    }

    window.rupeValidarRaza = function (forceInvalid) {
      var a = checkNombre();
      var b = checkTamano();
      var c = checkEspecie();
      if (forceInvalid && !b && tam) {
        tam.classList.add('is-invalid');
      }
      return a && b && c;
    };

    // Al abrir modal (agregar): limpiar validaciones
    var modal = document.getElementById('modalRaza');
    if (modal) {
      modal.addEventListener('shown.bs.modal', function () {
        var idEdit = (document.getElementById('inputRazaId') || {}).value || '';
        if (!idEdit) {
          // alta nueva
          if (nom) { nom.classList.remove('is-valid', 'is-invalid'); }
          if (tam) { tam.classList.remove('is-valid', 'is-invalid'); if (!tam.value) if (okT) okT.classList.add('d-none'); }
          if (okN) okN.classList.add('d-none');
        } else {
          // edición: revalidar valores cargados
          checkNombre();
          checkTamano();
          checkEspecie();
        }
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindRazaValid);
  } else {
    bindRazaValid();
  }
})();
</script>


<script>
/**
 * Exporta tabla HTML a CSV compatible con Excel (UTF-8 BOM).
 * tableId: id de la <table>, filenameBase: nombre del archivo sin extensión.
 * Omite la última columna si el encabezado es "Acciones".
 */
function exportarCatalogoExcel(tableId, filenameBase) {
  try {
    var table = document.getElementById(tableId);
    if (!table) {
      if (typeof mostrarToast === 'function') mostrarToast('No se encontró la tabla a exportar.', 'warning');
      else alert('No se encontró la tabla a exportar.');
      return;
    }
    var thead = table.querySelector('thead tr');
    var tbody = table.querySelector('tbody');
    if (!thead || !tbody) {
      if (typeof mostrarToast === 'function') mostrarToast('La tabla no tiene datos.', 'warning');
      return;
    }

    function cellText(el) {
      if (!el) return '';
      var t = (el.innerText || el.textContent || '').replace(/\s+/g, ' ').trim();
      // CSV: escapar comillas
      if (/[",\n\r]/.test(t)) t = '"' + t.replace(/"/g, '""') + '"';
      return t;
    }

    var headers = Array.prototype.slice.call(thead.children);
    // Quitar columna Acciones
    var skipLast = false;
    if (headers.length) {
      var lastH = (headers[headers.length - 1].innerText || '').trim().toLowerCase();
      if (lastH.indexOf('accion') >= 0) skipLast = true;
    }
    var cols = skipLast ? headers.length - 1 : headers.length;
    if (cols < 1) cols = headers.length;

    var lines = [];
    lines.push(headers.slice(0, cols).map(cellText).join(','));

    var rows = tbody.querySelectorAll('tr');
    var dataRows = 0;
    rows.forEach(function (tr) {
      // Filas vacías / "No hay..."
      if (tr.querySelector('td[colspan]')) return;
      // Ocultas por filtro
      if (tr.style.display === 'none' || tr.classList.contains('d-none')) return;
      var cells = tr.children;
      if (!cells || cells.length < 1) return;
      var vals = [];
      for (var i = 0; i < cols && i < cells.length; i++) {
        vals.push(cellText(cells[i]));
      }
      // Fila totalmente vacía
      if (vals.every(function (v) { return !v; })) return;
      lines.push(vals.join(','));
      dataRows++;
    });

    if (dataRows === 0) {
      if (typeof mostrarToast === 'function') mostrarToast('No hay filas visibles para exportar.', 'warning');
      else alert('No hay filas visibles para exportar.');
      return;
    }

    // UTF-8 BOM para que Excel abra acentos bien
    var csv = '\uFEFF' + lines.join('\r\n');
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var name = (filenameBase || 'catalogo') + '_' + new Date().toISOString().slice(0, 10) + '.csv';
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = name;
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    setTimeout(function () {
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    }, 200);
    if (typeof mostrarToast === 'function') {
      mostrarToast('Excel exportado: ' + dataRows + ' registro(s).', 'success');
    }
  } catch (err) {
    console.error('exportarCatalogoExcel', err);
    if (typeof mostrarToast === 'function') mostrarToast('Error al exportar Excel.', 'danger');
    else alert('Error al exportar Excel.');
  }
}
window.exportarCatalogoExcel = exportarCatalogoExcel;
</script>

</body>
</html>
