<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GPS / QR · RUPE CDMX</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/rupe-responsive-nav.css?v=20261003menu">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" media="print" onload="this.media='all'">
  <style>
    :root { --guinda:#6B1D2F; --guinda-dark:#4A1521; --dorado:#E8A33D; --bg:#F4F6F8; }
    body { background: var(--bg); font-family: system-ui, -apple-system, sans-serif; margin:0; }
    .topbar {
      background: var(--guinda); color:#fff; padding:.55rem .85rem;
      display:flex; align-items:center; flex-wrap:nowrap; gap:.45rem;
      position:sticky; top:0; z-index:1030; box-shadow:0 2px 10px rgba(0,0,0,.15);
      min-height:56px;
    }
    .topbar-brand {
      display:inline-flex; align-items:center; gap:.45rem; color:#fff !important;
      text-decoration:none; font-weight:700; white-space:nowrap; flex-shrink:0;
    }
    .topbar-brand img { height:26px; width:auto; object-fit:contain; }
    .rupe-nav-wrapper {
      position:relative; flex:1 1 auto; min-width:0;
      display:flex; align-items:center; gap:.35rem;
    }
    .modulo-actual-num {
      display:inline-flex; width:26px; height:26px; border-radius:50%;
      background:var(--dorado); color:#222; font-weight:800; font-size:.8rem;
      align-items:center; justify-content:center;
    }
    /* Hamburguesa Bootstrap 5 compatible */
    .rupe-toggler {
      display:none; border:0; background:rgba(255,255,255,.15);
      border-radius:10px; width:42px; height:40px; padding:0;
      align-items:center; justify-content:center; cursor:pointer;
    }
    .rupe-toggler:focus { outline:2px solid var(--dorado); outline-offset:2px; }
    .rupe-toggler-icon { display:flex; flex-direction:column; gap:5px; width:20px; }
    .rupe-toggler-icon span {
      display:block; height:2px; width:100%; background:#fff; border-radius:2px;
      transition: transform .2s ease, opacity .2s ease;
    }
    .rupe-toggler[aria-expanded="true"] .rupe-toggler-icon span:nth-child(1) {
      transform: translateY(7px) rotate(45deg);
    }
    .rupe-toggler[aria-expanded="true"] .rupe-toggler-icon span:nth-child(2) { opacity:0; }
    .rupe-toggler[aria-expanded="true"] .rupe-toggler-icon span:nth-child(3) {
      transform: translateY(-7px) rotate(-45deg);
    }
    .rupe-nav-list {
      list-style:none; margin:0; padding:0;
      display:flex; flex-wrap:wrap; gap:.35rem; align-items:center;
    }
    .rupe-nav-link {
      display:inline-flex; align-items:center; gap:.4rem; color:#fff !important;
      text-decoration:none; padding:.4rem .75rem; border-radius:999px;
      border:1px solid rgba(255,255,255,.28); background:rgba(255,255,255,.08);
      font-size:.8rem; font-weight:600; white-space:nowrap;
    }
    .rupe-nav-link:hover { background:rgba(255,255,255,.2); color:#fff !important; }
    .rupe-nav-link.active { background:var(--dorado); color:#222 !important; border-color:var(--dorado); }

    @media (max-width: 991.98px) {
      .rupe-toggler { display:inline-flex !important; }
      .rupe-nav-collapse {
        position: absolute; top: calc(100% + 8px); left: 0; right: 0;
        background: var(--guinda-dark); border-radius: 12px;
        box-shadow: 0 12px 28px rgba(0,0,0,.28); z-index: 1040;
        padding: .75rem;
      }
      /* Bootstrap .collapse: hidden until .show */
      .rupe-nav-collapse.collapse:not(.show) { display: none !important; }
      .rupe-nav-collapse.collapse.show { display: block !important; }
      .rupe-nav-list { flex-direction: column; align-items: stretch; gap: .4rem; }
      .rupe-nav-link { width: 100%; justify-content: flex-start; border-radius: 10px; }
    }
    @media (min-width: 992px) {
      .rupe-toggler { display: none !important; }
      .rupe-nav-collapse.collapse { display: block !important; height: auto !important; visibility: visible !important; }
    }

    .btn-guinda { background:var(--guinda); color:#fff; border:none; }
    .btn-guinda:hover { background:var(--guinda-dark); color:#fff; }
    .btn-user-dropdown {
      background:rgba(255,255,255,.12); color:#fff; border:1px solid rgba(255,255,255,.3);
      display:inline-flex; align-items:center; gap:.35rem;
    }
    .btn-user-dropdown:hover, .btn-user-dropdown:focus { background:rgba(255,255,255,.22); color:#fff; }
    .avatar-mini {
      width:28px; height:28px; border-radius:50%; background:var(--dorado); color:#222;
      display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem;
    }
    .user-dropdown { flex-shrink:0; margin-left:auto; }
    .kpi-card {
      border:none; border-radius:14px; box-shadow:0 4px 14px rgba(0,0,0,.06);
      border-left:4px solid var(--guinda); transition:.2s; cursor:pointer; background:#fff;
    }
    .kpi-card:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(0,0,0,.1); }
    .kpi-card.active-filter { outline:2px solid var(--dorado); background:#fffbeb; }
    .kpi-num { font-size:1.75rem; font-weight:800; color:var(--guinda); line-height:1.1; }
    .avist-card {
      border:none; border-radius:12px; box-shadow:0 4px 14px rgba(0,0,0,.06);
      border-left:4px solid #198754; background:#fff; height:100%;
    }
    .avist-card.tipo-registro { border-left-color: var(--guinda); }
    .avist-card .foto-card {
      width:76px; height:76px; object-fit:cover; border-radius:10px;
      background:#e9ecef; border:1px solid #dee2e6; flex-shrink:0;
    }
    .gps-badge {
      display:inline-flex; align-items:center; gap:.3rem;
      background:#e7f1ff; color:#0d47a1; border-radius:6px;
      padding:.2rem .5rem; font-size:.78rem; font-family:ui-monospace,monospace;
    }
    .gps-badge.sin { background:#f1f3f5; color:#868e96; }
    #mapaGPS { height:280px; border-radius:12px; background:#e9ecef; }
    .modulo-card { border:none; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,.06); background:#fff; }
    .btn-map, .btn-msg { min-width:38px; }
  
    #formMensajeCiudadano .form-control.is-valid {
      border-color: #198754 !important;
      padding-right: calc(1.5em + 0.75rem);
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right calc(0.375em + 0.1875rem) center;
      background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    #formMensajeCiudadano .form-control.is-invalid { border-color: #dc3545 !important; }
  </style>
</head>
<body>
<?php
  $nombreTutor = '';
  if (!empty($user) && is_array($user)) {
    $nombreTutor = (string) ($user['nombre'] ?? '');
  } elseif (!empty($usuario) && is_array($usuario)) {
    $nombreTutor = (string) ($usuario['nombre'] ?? '');
  }
  $inicial = $nombreTutor !== '' ? strtoupper(substr($nombreTutor, 0, 1)) : 'T';
  $mascotas = is_array($mascotas ?? null) ? $mascotas : [];
  $bitacoras = is_array($bitacoras ?? null) ? $bitacoras : [];
  if (!$bitacoras && !empty($bitacora) && is_array($bitacora)) {
    $bitacoras = $bitacora;
  }
  $nombreTutorJs = htmlspecialchars($nombreTutor !== '' ? $nombreTutor : 'Tutor', ENT_QUOTES, 'UTF-8');
?>
  <header class="topbar">
    <a class="topbar-brand d-flex align-items-center text-decoration-none" href="/tutor" style="gap:0.45rem;">
      <span class="d-inline-flex align-items-center justify-content-center bg-white rounded-2 px-2 py-1" style="height:36px;">
        <img src="/assets/img/logo-unadm.png" alt="UnADM" style="height:26px;width:auto;object-fit:contain;" onerror="this.style.display='none'">
      </span>
      <span class="d-none d-sm-inline text-white fw-bold">RUPE CDMX</span>
    </a>

    <div class="rupe-nav-wrapper ms-2 ms-md-3">
      <button class="rupe-toggler navbar-toggler" type="button" id="rupeNavToggler"
              data-bs-toggle="collapse" data-bs-target="#rupeNavCollapse"
              aria-controls="rupeNavCollapse" aria-expanded="false" aria-label="Abrir menú de módulos">
        <span class="rupe-toggler-icon" aria-hidden="true"><span></span><span></span><span></span></span>
      </button>

      <span class="topbar-current-modulo d-flex align-items-center ms-2">
        <span class="modulo-actual-num" id="moduloActualNum">5</span>
        <span class="ms-2 small fw-semibold d-none d-sm-inline" id="moduloActualTexto">GPS / QR</span>
      </span>

      <div class="collapse rupe-nav-collapse" id="rupeNavCollapse">
        <ul class="rupe-nav-list">
          <li style="--i:1"><a class="rupe-nav-link" href="/tutor/analitico"><i class="bi bi-graph-up-arrow"></i><span>Panel Analítico</span></a></li>
          <li style="--i:2"><a class="rupe-nav-link" href="/tutor/registro"><i class="bi bi-pencil-square"></i><span>Registro</span></a></li>
          <li style="--i:3"><a class="rupe-nav-link" href="/tutor/crud"><i class="bi bi-table"></i><span>Tabla CRUD</span></a></li>
          <li style="--i:4"><a class="rupe-nav-link" href="/tutor/ficha-pdf"><i class="bi bi-file-earmark-pdf-fill"></i><span>Ficha + QR</span></a></li>
          <li style="--i:5"><a class="rupe-nav-link active" href="/tutor/gpsqr"><i class="bi bi-geo-alt-fill"></i><span>GPS / QR</span></a></li>
          <li style="--i:6"><a class="rupe-nav-link" href="/tutor/seguimiento"><i class="bi bi-radar"></i><span>Seguimiento</span></a></li>
          <li style="--i:7"><a class="rupe-nav-link" href="/tutor/estatus"><i class="bi bi-flag"></i><span>Estatus</span></a></li>
          <li style="--i:8"><a class="rupe-nav-link" href="/tutor/albergues"><i class="bi bi-house-heart-fill"></i><span>Albergues</span></a></li>
        </ul>
      </div>
    </div>

    <div class="dropdown user-dropdown ms-auto">
      <button class="btn btn-sm btn-user-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Opciones de cuenta">
        <span class="avatar-mini"><?= htmlspecialchars($inicial, ENT_QUOTES, 'UTF-8') ?></span>
        <span class="d-none d-md-inline text-truncate" style="max-width:110px"><?= $nombreTutorJs ?></span>
        <span class="d-inline d-md-none label-opciones">Opciones</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><h6 class="dropdown-header"><?= $nombreTutorJs ?> · Cuenta</h6></li>
        <li><a class="dropdown-item" href="/tutor/perfil"><i class="bi bi-person-circle me-2"></i>Mi perfil</a></li>
        <li><a class="dropdown-item" href="/tutor/cuenta"><i class="bi bi-gear me-2"></i>Configuración</a></li>
        <li><a class="dropdown-item" href="/tutor/ayuda"><i class="bi bi-question-circle me-2"></i>Ayuda</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>
      </ul>
    </div>
  </header>
  <div id="rupeNavBackdrop" aria-hidden="true"></div>

  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
      <div>
        <span class="badge bg-success mb-1"><i class="bi bi-geo-fill me-1"></i> GPS / QR</span>
        <h1 class="h4 fw-bold mb-0">Avistamientos, filtros y mensajes</h1>
        <p class="text-muted small mb-0">Registro inicial del tutor (solo mapa) y reportes ciudadanos (mensaje + mapa).</p>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-sm btn-outline-secondary" href="/tutor/seguimiento">Seguimiento</a>
        <a class="btn btn-sm btn-outline-secondary" href="/tutor/ficha-pdf">Ficha + QR</a>
      </div>
    </div>

    <div class="row g-3 mb-3" id="kpiFiltros">
      <div class="col-6 col-md-3">
        <div class="card kpi-card p-3 h-100 active-filter" data-filtro="todos">
          <div class="text-muted small fw-bold">TOTAL</div>
          <div class="kpi-num" id="numTotal">0</div>
          <div class="small text-muted">Todos los registros</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card kpi-card p-3 h-100" data-filtro="gps" style="border-left-color:#0d6efd;">
          <div class="text-muted small fw-bold">CON FOTO + GPS</div>
          <div class="kpi-num text-primary" id="numGps">0</div>
          <div class="small text-muted">Con coordenadas</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card kpi-card p-3 h-100" data-filtro="registro" style="border-left-color:#6B1D2F;">
          <div class="text-muted small fw-bold">REGISTRO INICIAL</div>
          <div class="kpi-num" id="numReg">0</div>
          <div class="small text-muted">Casos del tutor</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card kpi-card p-3 h-100" data-filtro="ciudadano" style="border-left-color:#198754;">
          <div class="text-muted small fw-bold">CIUDADANOS</div>
          <div class="kpi-num text-success" id="numCiu">0</div>
          <div class="small text-muted">Avistamientos QR</div>
        </div>
      </div>
    </div>

    <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
      <span class="badge bg-dark" id="badgeFiltrosActivos">0 filtros activos</span>
      <select id="gpsConsultaFolio" class="form-select form-select-sm" style="max-width:220px">
        <option value="">Todos los folios</option>
        <?php foreach ($mascotas as $gm):
          $gf = htmlspecialchars((string)($gm['folio'] ?? ''), ENT_QUOTES, 'UTF-8');
          $gn = htmlspecialchars((string)($gm['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
          if ($gf === '') continue; ?>
          <option value="<?= $gf ?>">#<?= $gf ?> · <?= $gn ?></option>
        <?php endforeach; ?>
      </select>
      <input type="text" id="gpsConsultaBuscar" class="form-control form-control-sm" style="max-width:200px" placeholder="Buscar…">
      <button type="button" class="btn btn-sm btn-outline-secondary" id="btnGpsLimpiar">Limpiar</button>
      <button type="button" class="btn btn-sm btn-outline-secondary" id="btnGpsRefrescar" title="Actualizar"><i class="bi bi-arrow-clockwise"></i></button>
    </div>

    <div class="card modulo-card mb-3">
      <div class="card-body p-2">
        <p class="small fw-semibold px-1 mb-2"><i class="bi bi-map text-danger me-1"></i>Mapa — clic en el icono de mapa del card para centrar</p>
        <div id="mapaGPS"></div>
      </div>
    </div>

    <h2 class="h6 fw-bold mb-2"><i class="bi bi-collection me-1 text-success"></i>Registros y avistamientos</h2>
    <div class="row g-3 mb-4" id="listaAvistCards">
      <div class="col-12 text-center text-muted py-4">Cargando…</div>
    </div>

    <div class="card modulo-card mb-4" id="seccionMensaje">
      <div class="card-header bg-white">
        <h2 class="h6 fw-bold mb-0"><i class="bi bi-envelope-heart me-2 text-danger"></i>Enviar mensaje al ciudadano</h2>
      </div>
      <div class="card-body">
        <div id="msgAlert" class="alert d-none py-2 small"></div>
        <form id="formMensajeCiudadano" novalidate>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="msgFolio">Folio (opcional)</label>
              <input type="text" class="form-control" id="msgFolio" placeholder="RUPE-…" maxlength="40" autocomplete="off">
              <div class="form-text text-success small d-none" id="okMsgFolio"><i class="bi bi-check-circle-fill me-1"></i>Folio listo</div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="msgNombre">Nombre del ciudadano *</label>
              <input type="text" class="form-control" id="msgNombre" required minlength="2" maxlength="80" autocomplete="name">
              <div class="form-text text-success small d-none" id="okMsgNombre"><i class="bi bi-check-circle-fill me-1"></i>Nombre válido</div>
              <div class="invalid-feedback">Indica nombre (solo letras, mín. 2).</div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold" for="msgCorreo">Correo del ciudadano *</label>
              <input type="email" class="form-control" id="msgCorreo" required maxlength="120" autocomplete="email">
              <div class="form-text text-success small d-none" id="okMsgCorreo"><i class="bi bi-check-circle-fill me-1"></i>Correo válido</div>
              <div class="invalid-feedback">Correo no válido.</div>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold" for="msgTexto">Mensaje * (mín. 100 caracteres)</label>
              <textarea class="form-control" id="msgTexto" rows="4" required minlength="100" maxlength="2000" placeholder="Escribe tu mensaje de agradecimiento o respuesta…"></textarea>
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 mt-1">
                <div class="form-text text-success small d-none mb-0" id="okMsgTexto"><i class="bi bi-check-circle-fill me-1"></i>Mensaje completo</div>
                <span class="small text-muted ms-auto" id="msgTextoCount">0 / 100</span>
              </div>
              <div class="invalid-feedback">El mensaje debe tener al menos 100 caracteres.</div>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-guinda" id="btnEnviarMsg" disabled><i class="bi bi-send-fill me-1"></i>Enviar mensaje</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div class="card modulo-card mb-3">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h6 fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>Bitácora de casos</h2>
        <span class="badge bg-secondary" id="badgeBitacora">0</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">Folio</th>
                <th>Mascota</th>
                <th>Estatus</th>
                <th>Última actividad</th>
                <th>Bitácora</th>
              </tr>
            </thead>
            <tbody id="tbodyBitacora">
              <tr><td colspan="5" class="text-center text-muted py-3">Cargando bitácora…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="modalMsgOk" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="modal-title text-success"><i class="bi bi-check-circle-fill me-2"></i>Mensaje enviado</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="modalMsgOkBody">El correo se envió correctamente.</div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-guinda" data-bs-dismiss="modal">Entendido</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.RUPE_TUTOR_NOMBRE = <?= json_encode($nombreTutor !== '' ? $nombreTutor : 'Tutor', JSON_UNESCAPED_UNICODE) ?>;
    window.RUPE_GPS_BOOT_MASCOTAS = <?= json_encode(array_values(array_map(static function ($m) {
      if (!is_array($m)) return [];
      return [
        'id_mascota' => $m['id_mascota'] ?? $m['id'] ?? null,
        'folio' => $m['folio'] ?? '',
        'nombre' => $m['nombre'] ?? '',
        'estatus' => $m['estatus'] ?? 'En Búsqueda',
        'colonia' => $m['colonia'] ?? '',
        'direccion_ref' => $m['direccion_ref'] ?? ($m['direccion'] ?? ''),
        'calle' => $m['calle'] ?? '',
        'latitud' => $m['latitud'] ?? null,
        'longitud' => $m['longitud'] ?? null,
        'foto_url' => $m['foto_url'] ?? ($m['foto'] ?? ''),
        'foto' => $m['foto'] ?? ($m['foto_url'] ?? ''),
        'created_at' => $m['created_at'] ?? ($m['fecha_extravio'] ?? ''),
        'fecha_extravio' => $m['fecha_extravio'] ?? '',
      ];
    }, $mascotas)), JSON_UNESCAPED_UNICODE) ?>;
    window.RUPE_GPS_BOOT_HIST = <?= json_encode(array_values(is_array($historial_seguimiento ?? null) ? $historial_seguimiento : []), JSON_UNESCAPED_UNICODE) ?>;
    window.RUPE_GPS_BOOT_BIT = <?= json_encode(array_values($bitacoras), JSON_UNESCAPED_UNICODE) ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="/assets/js/rupe-responsive-nav.js?v=20261003menu"></script>
  <script src="/assets/js/rupe-panels.js?v=20261003menu"></script>
  <script src="/assets/js/tutor-gpsqr-page.js?v=20261003msg"></script>
  <script>
  /* Fallback hamburguesa Bootstrap 5 si collapse no abre */
  (function () {
    var btn = document.getElementById('rupeNavToggler');
    var col = document.getElementById('rupeNavCollapse');
    if (!btn || !col) return;
    btn.addEventListener('click', function () {
      if (window.innerWidth >= 992) return;
      // Dar tiempo a Bootstrap; si no hay .show, forzar
      setTimeout(function () {
        var expanded = btn.getAttribute('aria-expanded') === 'true';
        var shown = col.classList.contains('show');
        if (expanded && !shown) col.classList.add('show');
        if (!expanded && shown) col.classList.remove('show');
        var open = col.classList.contains('show');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        var bd = document.getElementById('rupeNavBackdrop');
        if (bd) {
          bd.classList.toggle('show', open);
          bd.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
        document.body.classList.toggle('rupe-nav-open', open);
      }, 30);
    });
    // Cerrar al elegir enlace en móvil
    col.querySelectorAll('a.rupe-nav-link').forEach(function (a) {
      a.addEventListener('click', function () {
        if (window.innerWidth >= 992) return;
        col.classList.remove('show');
        btn.setAttribute('aria-expanded', 'false');
        var bd = document.getElementById('rupeNavBackdrop');
        if (bd) { bd.classList.remove('show'); bd.setAttribute('aria-hidden', 'true'); }
        document.body.classList.remove('rupe-nav-open');
      });
    });
  })();
  </script>
</body>
</html>
