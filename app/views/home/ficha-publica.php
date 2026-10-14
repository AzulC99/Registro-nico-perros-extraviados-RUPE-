<?php
/** Ficha pública de mascota — solo datos reales de BD */
declare(strict_types=1);
$m = is_array($mascota ?? null) ? $mascota : [];
$folio = htmlspecialchars((string) ($m['folio'] ?? ''), ENT_QUOTES, 'UTF-8');
$nombre = htmlspecialchars((string) ($m['nombre'] ?? 'Sin nombre'), ENT_QUOTES, 'UTF-8');
$raza = htmlspecialchars((string) ($m['raza_nombre'] ?? 'Sin especificar'), ENT_QUOTES, 'UTF-8');
$especie = htmlspecialchars((string) ($m['especie'] ?? ''), ENT_QUOTES, 'UTF-8');
$sexo = htmlspecialchars((string) ($m['sexo'] ?? ''), ENT_QUOTES, 'UTF-8');
$color = htmlspecialchars((string) ($m['color'] ?? ''), ENT_QUOTES, 'UTF-8');
$edad = htmlspecialchars((string) ($m['edad_aprox'] ?? ''), ENT_QUOTES, 'UTF-8');
$senas = htmlspecialchars((string) ($m['senas_particulares'] ?? ''), ENT_QUOTES, 'UTF-8');
$alcaldia = htmlspecialchars((string) ($m['alcaldia_nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
$colonia = htmlspecialchars((string) ($m['colonia'] ?? ''), ENT_QUOTES, 'UTF-8');
$fecha = htmlspecialchars((string) ($m['fecha_extravio'] ?? ''), ENT_QUOTES, 'UTF-8');
$estatus = htmlspecialchars((string) ($m['estatus'] ?? ''), ENT_QUOTES, 'UTF-8');
$latRaw = $m['latitud'] ?? null;
$lngRaw = $m['longitud'] ?? null;
$lat = (is_numeric($latRaw) ? (float) $latRaw : null);
$lng = (is_numeric($lngRaw) ? (float) $lngRaw : null);
$gpsTxt = ($lat !== null && $lng !== null)
    ? htmlspecialchars(number_format($lat, 5, '.', '') . ', ' . number_format($lng, 5, '.', ''), ENT_QUOTES, 'UTF-8')
    : '';
$especieRaw = (string) ($m['especie'] ?? 'perro');
$foto = function_exists('mascota_foto_src')
    ? mascota_foto_src(isset($m['foto_url']) ? (string) $m['foto_url'] : null, $especieRaw)
    : (string) ($m['foto_url'] ?? '/assets/img/placeholder-mascota.svg');
$fotoEsc = htmlspecialchars($foto, ENT_QUOTES, 'UTF-8');
$qrUrl = htmlspecialchars((string) ($qr_url ?? ''), ENT_QUOTES, 'UTF-8');
$idMascota = (int) ($m['id_mascota'] ?? $m['id'] ?? 0);
$tokenPub = htmlspecialchars((string) ($m['qr_token'] ?? $token ?? ''), ENT_QUOTES, 'UTF-8');
$avistar = isset($_GET['avistar']) && (string)$_GET['avistar'] === '1';
$badge = 'bg-danger';
if ($estatus === 'Localizado') $badge = 'bg-success';
elseif ($estatus === 'En Resguardo') $badge = 'bg-warning text-dark';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ficha <?= $folio ?> · <?= $nombre ?> — RUPE CDMX</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root { --guinda: #6B1D2F; --dorado: #E8A33D; }
    body { background: #F4F6F8; font-family: system-ui, sans-serif; }
    .navbar-ficha { background: var(--guinda); }
    .ficha-card { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 28px rgba(0,0,0,.08); }
    .ficha-foto { width: 100%; max-height: 360px; object-fit: cover; background: #e9ecef; }
    .badge-guinda { background: var(--guinda); }
    .btn-guinda { background: var(--guinda); color: #fff; border: none; }
    .btn-guinda:hover { background: #4A1521; color: #fff; }

    .avist-ok { color: #198754; font-size: .9rem; display: none; margin-left: .35rem; }
    .form-control.is-valid ~ .avist-ok,
    .form-select.is-valid ~ .avist-ok,
    .input-group .form-control.is-valid ~ .avist-ok,
    .is-valid + .avist-ok { display: inline-block; }
    .form-label .avist-ok { display: none; }
    .form-control.is-valid + .form-text .avist-ok,
    .was-validated .form-control:valid ~ .avist-ok { display: inline-block; }

    .qr-box { width: 160px; height: 160px; background: #fff; border: 1px solid #dee2e6; border-radius: 12px; display:flex; align-items:center; justify-content:center; }
    .qr-box img { max-width: 148px; max-height: 148px; }
  </style>
</head>
<body>
  <nav class="navbar navbar-dark navbar-ficha py-3">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/">
        <img src="/assets/img/logo-unadm.png" alt="SEP · UnADM" style="height:40px;width:auto;object-fit:contain;max-width:140px;" onerror="this.style.display='none'">
        <span>RUPE CDMX</span>
      </a>
      <a href="/" class="btn btn-sm btn-outline-light">Volver al inicio</a>
    </div>
  </nav>

  <main class="container py-4 py-md-5">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="card ficha-card">
          <img src="<?= $fotoEsc ?>" class="ficha-foto" alt="<?= $nombre ?>" onerror="this.onerror=null;this.src='/assets/img/placeholder-mascota.svg'">

          <div class="card-body p-4">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start mb-3">
              <div>
                <span class="badge badge-guinda mb-2">#<?= $folio ?></span>
                <h1 class="h3 fw-bold mb-1"><?= $nombre ?></h1>
                <p class="text-muted mb-0"><?= $raza ?><?= $especie !== '' ? ' · ' . $especie : '' ?></p>
              </div>
              <span class="badge <?= $badge ?> fs-6"><?= $estatus !== '' ? $estatus : 'En Búsqueda' ?></span>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <ul class="list-unstyled mb-0">
                  <?php if ($sexo !== ''): ?><li class="mb-2"><i class="bi bi-gender-ambiguous text-danger me-2"></i><strong>Sexo:</strong> <?= $sexo ?></li><?php endif; ?>
                  <?php if ($color !== ''): ?><li class="mb-2"><i class="bi bi-palette text-danger me-2"></i><strong>Color:</strong> <?= $color ?></li><?php endif; ?>
                  <?php if ($edad !== ''): ?><li class="mb-2"><i class="bi bi-hourglass text-danger me-2"></i><strong>Edad:</strong> <?= $edad ?></li><?php endif; ?>
                  <?php if ($fecha !== ''): ?><li class="mb-2"><i class="bi bi-calendar-event text-danger me-2"></i><strong>Extravío:</strong> <?= $fecha ?></li><?php endif; ?>
                </ul>
              </div>
              <div class="col-md-6">
                <ul class="list-unstyled mb-0">
                  <?php if ($alcaldia !== ''): ?><li class="mb-2"><i class="bi bi-geo-alt-fill text-danger me-2"></i><strong>Alcaldía:</strong> <?= $alcaldia ?></li><?php endif; ?>
                  <?php if ($colonia !== ''): ?><li class="mb-2"><i class="bi bi-pin-map text-danger me-2"></i><strong>Colonia:</strong> <?= $colonia ?></li><?php endif; ?>
                  <?php if ($gpsTxt !== ''): ?><li class="mb-2"><i class="bi bi-crosshair text-danger me-2"></i><strong>GPS:</strong> <?= $gpsTxt ?></li><?php endif; ?>
                </ul>
              </div>
            </div>

            <?php if ($lat !== null && $lng !== null): ?>
              <div class="mb-4">
                <strong class="d-block mb-2"><i class="bi bi-map text-danger me-1"></i> Última ubicación GPS</strong>
                <div id="publicMapaGps" style="height:240px;border-radius:12px;overflow:hidden;border:1px solid #dee2e6;"></div>
                <p class="small text-muted mt-1 mb-0"><?= $gpsTxt ?></p>
              </div>
              <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
              <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
              <script>
                (function () {
                  var lat = <?= json_encode($lat) ?>, lng = <?= json_encode($lng) ?>;
                  var el = document.getElementById('publicMapaGps');
                  if (!el || typeof L === 'undefined') return;
                  var map = L.map(el).setView([lat, lng], 15);
                  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
                  L.marker([lat, lng]).addTo(map);
                  setTimeout(function () { try { map.invalidateSize(); } catch (e) {} }, 200);
                })();
              </script>
            <?php endif; ?>

            <?php if ($senas !== ''): ?>
              <div class="alert alert-light border mb-4">
                <strong><i class="bi bi-info-circle me-1"></i> Señas particulares</strong>
                <p class="mb-0 mt-1"><?= nl2br($senas) ?></p>
              </div>
            <?php endif; ?>

            <div class="d-flex flex-wrap gap-4 align-items-center">
              <div>
                <p class="small text-muted mb-2">Código QR de esta ficha</p>
                <div class="qr-box" id="qrBox">
                  <?php
                    $qrData = $qrUrl !== '' ? $qrUrl : ((defined('APP_URL') ? rtrim(APP_URL,'/') : '') . '/mascota/' . rawurlencode((string)($folio ?? '')));
                    if ($qrData === '' || str_starts_with($qrData, '/')) {
                      $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                      $host = $_SERVER['HTTP_HOST'] ?? 'buscandoahuellitasrupe.com.mx';
                      $qrData = $scheme . '://' . $host . '/mascota/' . rawurlencode((string)($folio ?? ($mascota['folio'] ?? '')));
                    }
                  ?>
                  <img src="/api/public/qr?size=148&data=<?= rawurlencode($qrData) ?>"
                       onerror="this.onerror=null;this.src='https://api.qrserver.com/v1/create-qr-code/?size=148x148&amp;data=<?= rawurlencode($qrData) ?>';"
                       alt="QR <?= $folio ?>" width="148" height="148">
                </div>
              </div>
              <div class="flex-grow-1">
                <p class="small text-muted mb-2">Si viste a esta mascota, reporta el avistamiento aquí mismo (el QR de la ficha te trajo a esta captura).</p>
                <a href="#reportar-avistamiento" class="btn btn-guinda me-2"><i class="bi bi-geo-alt-fill me-1"></i> Reportar avistamiento</a>
                <a href="/" class="btn btn-outline-secondary me-2"><i class="bi bi-search me-1"></i> Inicio</a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
              </div>
            </div>
          </div>
        </div>

        <?php if (!empty($seguimientos) && is_array($seguimientos)): ?>
        <div class="card ficha-card mt-4">
          <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Bitácora pública</h2>
            <ul class="list-group list-group-flush">
              <?php foreach (array_slice($seguimientos, 0, 10) as $s): ?>
                <li class="list-group-item px-0">
                  <div class="d-flex justify-content-between">
                    <span class="badge bg-secondary"><?= htmlspecialchars((string) ($s['tipo'] ?? 'nota'), ENT_QUOTES, 'UTF-8') ?></span>
                    <small class="text-muted"><?= htmlspecialchars((string) ($s['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                  </div>
                  <p class="mb-0 mt-1"><?= htmlspecialchars((string) ($s['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>



        <!-- Captura de avistamiento (destino del QR de la ficha) -->
        <div class="card ficha-card mt-4" id="reportar-avistamiento">
          <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-1"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Reportar avistamiento</h2>
            <p class="small text-muted mb-3">Completa el formulario. El tutor recibirá el reporte en su bitácora de seguimiento.</p>
            <div id="avistAlert" class="alert d-none py-2 small" role="alert"></div>
            <form id="formAvistPublico" novalidate>
              <input type="hidden" name="qr_token" value="<?= $tokenPub ?? '' ?>">
              <input type="hidden" name="folio" value="<?= $folio ?>">
              <input type="hidden" id="avistLat" name="latitud" value="">
              <input type="hidden" id="avistLng" name="longitud" value="">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label small fw-semibold">Condición del animal * <span class="avist-ok" id="okCondicion"><i class="bi bi-check-circle-fill"></i></span></label>
                  <select class="form-select" id="avistCondicion" name="condicion" required>
                    <option value="">— Selecciona —</option>
                    <option value="Observado">Observado</option>
                    <option value="Resguardado">Resguardado</option>
                    <option value="En albergue">En albergue</option>
                    <option value="Herido">Herido</option>
                    <option value="Localizado">Localizado</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-semibold">Referencia del lugar * <span class="avist-ok" id="okRef"><i class="bi bi-check-circle-fill"></i></span></label>
                  <input type="text" class="form-control" id="avistRef" name="referencia" maxlength="60" required placeholder="Parque, esquina, fachada…">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-semibold">Nombre * <span class="avist-ok" id="okNombre"><i class="bi bi-check-circle-fill"></i></span></label>
                  <input type="text" class="form-control" id="avistNombre" name="nombre" maxlength="20" required placeholder="Nombre(s)">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-semibold">Apellidos * <span class="avist-ok" id="okApellidos"><i class="bi bi-check-circle-fill"></i></span></label>
                  <input type="text" class="form-control" id="avistApellidos" name="apellidos" maxlength="40" required placeholder="Apellido(s)">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-semibold">Teléfono (10 dígitos) * <span class="avist-ok" id="okTel"><i class="bi bi-check-circle-fill"></i></span></label>
                  <input type="tel" class="form-control" id="avistTel" name="telefono" maxlength="10" inputmode="numeric" required placeholder="55XXXXXXXX">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-semibold">Correo * <span class="avist-ok" id="okCorreo"><i class="bi bi-check-circle-fill"></i></span></label>
                  <input type="email" class="form-control" id="avistCorreo" name="correo" maxlength="30" required placeholder="correo@ejemplo.com">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-semibold">Foto del avistamiento <span class="avist-ok" id="okFoto"><i class="bi bi-check-circle-fill"></i></span></label>
                  <input type="file" class="form-control" id="avistFoto" accept="image/jpeg,image/png,image/webp">
                  <div class="form-text">JPG, PNG o WEBP (opcional)</div>
                </div>
                <div class="col-md-8">
                  <label class="form-label small fw-semibold">Buscar dirección en el mapa <span class="avist-ok" id="okDir"><i class="bi bi-check-circle-fill"></i></span></label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="avistDireccionBuscar" placeholder="Calle, colonia, alcaldía CDMX…">
                    <button type="button" class="btn btn-outline-secondary" id="btnBuscarDirMapa" title="Buscar en el mapa">
                      <i class="bi bi-search"></i>
                    </button>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-semibold">GPS / mapa * <span class="avist-ok" id="okGps"><i class="bi bi-check-circle-fill"></i></span></label>
                  <div class="d-flex flex-column gap-1">
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btnCapturarGpsPub">
                      <i class="bi bi-crosshair me-1"></i> Capturar mi ubicación
                    </button>
                    <span class="small text-muted" id="avistGpsTxt">Sin coordenadas</span>
                  </div>
                </div>
                <div class="col-12">
                  <div id="mapaAvistPublico" style="height:240px;border-radius:12px;overflow:hidden;border:1px solid #dee2e6;background:#e9ecef;"></div>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-guinda" id="btnEnviarAvistPub">
                    <i class="bi bi-send-fill me-1"></i> Enviar avistamiento
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Modal éxito avistamiento -->
        <div class="modal fade" id="modalAvistOk" tabindex="-1" aria-labelledby="modalAvistOkLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-success" id="modalAvistOkLabel">
                  <i class="bi bi-check-circle-fill me-2"></i>Avistamiento guardado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <p class="mb-1" id="modalAvistOkMsg">Tu reporte se guardó correctamente.</p>
                <p class="small text-muted mb-0">El tutor verá el avistamiento en su módulo de Seguimiento.</p>
              </div>
              <div class="modal-footer border-0">
                <button type="button" class="btn btn-guinda" data-bs-dismiss="modal">Entendido</button>
              </div>
            </div>
          </div>
        </div>

  <footer class="text-center text-muted py-4 small">
    RUPE CDMX · Buscando a Huellitas · Ficha pública
  </footer>


  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  (function () {
    var map = null, marker = null;
    var gpsOk = false;

    function showAlert(msg, type) {
      var el = document.getElementById('avistAlert');
      if (!el) return;
      el.className = 'alert alert-' + (type || 'info') + ' py-2 small';
      el.textContent = msg;
      el.classList.remove('d-none');
    }

    function setOk(id, ok) {
      var el = document.getElementById(id);
      if (el) el.style.display = ok ? 'inline-block' : 'none';
    }

    function mark(el, ok) {
      if (!el) return;
      el.classList.toggle('is-valid', !!ok);
      el.classList.toggle('is-invalid', !ok && String(el.value || '').trim() !== '');
    }

    function validarCondicion() {
      var el = document.getElementById('avistCondicion');
      var ok = !!(el && el.value);
      mark(el, ok);
      setOk('okCondicion', ok);
      return ok;
    }
    function validarRef() {
      var el = document.getElementById('avistRef');
      var v = (el && el.value || '').trim();
      var ok = v.length >= 2 && v.length <= 60;
      mark(el, ok);
      setOk('okRef', ok);
      return ok;
    }
    function validarNombre() {
      var el = document.getElementById('avistNombre');
      var v = (el && el.value || '').trim();
      var ok = /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü ]{2,20}$/.test(v);
      mark(el, ok);
      setOk('okNombre', ok);
      return ok;
    }
    function validarApellidos() {
      var el = document.getElementById('avistApellidos');
      var v = (el && el.value || '').trim();
      var ok = /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü ]{2,40}$/.test(v);
      mark(el, ok);
      setOk('okApellidos', ok);
      return ok;
    }
    function validarTel() {
      var el = document.getElementById('avistTel');
      var v = (el && el.value || '').replace(/\D+/g, '');
      if (el) el.value = v;
      var ok = v.length === 10;
      mark(el, ok);
      setOk('okTel', ok);
      return ok;
    }
    function validarCorreo() {
      var el = document.getElementById('avistCorreo');
      var v = (el && el.value || '').trim();
      var ok = v.length >= 5 && v.length <= 30 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
      mark(el, ok);
      setOk('okCorreo', ok);
      return ok;
    }
    function validarFoto() {
      var el = document.getElementById('avistFoto');
      var f = el && el.files && el.files[0];
      if (!f) {
        if (el) { el.classList.remove('is-valid', 'is-invalid'); }
        setOk('okFoto', false);
        return true; // opcional
      }
      var ok = /^image\/(jpeg|jpg|png|webp)$/i.test(f.type) && f.size <= 3.5 * 1024 * 1024;
      mark(el, ok);
      setOk('okFoto', ok);
      return ok;
    }
    function validarGps() {
      var lat = document.getElementById('avistLat').value;
      var lng = document.getElementById('avistLng').value;
      var ok = lat !== '' && lng !== '' && isFinite(Number(lat)) && isFinite(Number(lng));
      gpsOk = ok;
      setOk('okGps', ok);
      setOk('okDir', ok);
      return ok;
    }

    function setGps(lat, lng, fromSearch) {
      document.getElementById('avistLat').value = lat;
      document.getElementById('avistLng').value = lng;
      document.getElementById('avistGpsTxt').textContent =
        Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5) + (fromSearch ? ' (dirección)' : '');
      if (typeof L === 'undefined') { validarGps(); return; }
      var el = document.getElementById('mapaAvistPublico');
      if (!el) return;
      if (!map) {
        map = L.map(el).setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', function () {
          var p = marker.getLatLng();
          setGps(p.lat, p.lng, false);
        });
      } else {
        map.setView([lat, lng], 16);
        marker.setLatLng([lat, lng]);
      }
      setTimeout(function () { try { map.invalidateSize(); } catch (_) {} }, 200);
      validarGps();
    }

    document.getElementById('btnCapturarGpsPub')?.addEventListener('click', function () {
      if (!navigator.geolocation) {
        showAlert('Geolocalización no disponible.', 'warning');
        return;
      }
      navigator.geolocation.getCurrentPosition(function (pos) {
        setGps(pos.coords.latitude, pos.coords.longitude, false);
        showAlert('Ubicación capturada. Puedes arrastrar el marcador.', 'success');
      }, function () {
        showAlert('No se pudo obtener GPS. Activa el permiso de ubicación.', 'danger');
      }, { enableHighAccuracy: true, timeout: 12000 });
    });

    document.getElementById('btnBuscarDirMapa')?.addEventListener('click', async function () {
      var q = (document.getElementById('avistDireccionBuscar').value || '').trim();
      if (q.length < 3) {
        showAlert('Escribe una dirección para buscar (mín. 3 caracteres).', 'warning');
        return;
      }
      var btn = document.getElementById('btnBuscarDirMapa');
      if (btn) btn.disabled = true;
      try {
        var url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=mx&q='
          + encodeURIComponent(q + ', Ciudad de México');
        var res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        var arr = await res.json().catch(function () { return []; });
        if (!arr || !arr.length) {
          showAlert('No se encontró la dirección. Prueba con otra referencia.', 'warning');
          return;
        }
        var lat = parseFloat(arr[0].lat);
        var lng = parseFloat(arr[0].lon);
        setGps(lat, lng, true);
        var ref = document.getElementById('avistRef');
        if (ref && !ref.value.trim()) {
          ref.value = (arr[0].display_name || q).slice(0, 60);
          validarRef();
        }
        showAlert('Coordenadas generadas desde la dirección.', 'success');
      } catch (err) {
        showAlert('No se pudo buscar la dirección. Intenta de nuevo.', 'danger');
      } finally {
        if (btn) btn.disabled = false;
      }
    });

    ['avistCondicion'].forEach(function (id) {
      document.getElementById(id)?.addEventListener('change', validarCondicion);
    });
    document.getElementById('avistRef')?.addEventListener('input', validarRef);
    document.getElementById('avistNombre')?.addEventListener('input', validarNombre);
    document.getElementById('avistApellidos')?.addEventListener('input', validarApellidos);
    document.getElementById('avistTel')?.addEventListener('input', validarTel);
    document.getElementById('avistCorreo')?.addEventListener('input', validarCorreo);
    document.getElementById('avistFoto')?.addEventListener('change', validarFoto);

    document.addEventListener('DOMContentLoaded', function () {
      try { setGps(19.4326, -99.1332, false); document.getElementById('avistGpsTxt').textContent = 'Ajusta el punto en el mapa o busca una dirección'; gpsOk = false; setOk('okGps', false); } catch (_) {}
      if (new URLSearchParams(location.search).get('avistar') === '1') {
        var box = document.getElementById('reportar-avistamiento');
        if (box) {
          setTimeout(function () {
            box.scrollIntoView({ behavior: 'smooth', block: 'start' });
            box.classList.add('border', 'border-danger');
          }, 300);
        }
      }
    });

    document.getElementById('formAvistPublico')?.addEventListener('submit', async function (e) {
      e.preventDefault();
      var ok =
        validarCondicion() &
        validarRef() &
        validarNombre() &
        validarApellidos() &
        validarTel() &
        validarCorreo() &
        validarFoto() &
        validarGps();
      if (!ok) {
        showAlert('Corrige los campos marcados (palomita verde = correcto).', 'warning');
        return;
      }

      var nom = (document.getElementById('avistNombre').value || '').trim();
      var ape = (document.getElementById('avistApellidos').value || '').trim();
      var contacto = (nom + ' ' + ape).trim().slice(0, 80);

      var payload = {
        qr_token: (document.querySelector('[name=qr_token]') || {}).value || '',
        folio: (document.querySelector('[name=folio]') || {}).value || '',
        condicion: document.getElementById('avistCondicion').value,
        referencia: (document.getElementById('avistRef').value || '').trim().slice(0, 60),
        contacto: contacto,
        nombre: nom,
        apellidos: ape,
        telefono: (document.getElementById('avistTel').value || '').replace(/\D+/g, ''),
        correo: (document.getElementById('avistCorreo').value || '').trim().slice(0, 30),
        latitud: document.getElementById('avistLat').value,
        longitud: document.getElementById('avistLng').value,
        foto_base64: ''
      };

      var file = document.getElementById('avistFoto').files[0];
      if (file) {
        payload.foto_base64 = await new Promise(function (resolve) {
          var r = new FileReader();
          r.onload = function () { resolve(String(r.result || '')); };
          r.onerror = function () { resolve(''); };
          r.readAsDataURL(file);
        });
      }

      var btn = document.getElementById('btnEnviarAvistPub');
      if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando…'; }
      try {
        var res = await fetch('/api/public/avistamiento', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(payload)
        });
        var data = await res.json().catch(function () { return {}; });
        if (!res.ok || !data.success) {
          showAlert((data && data.message) || 'No se pudo guardar. Intenta de nuevo.', 'danger');
          return;
        }
        var msg = (data.message || 'Avistamiento registrado. Gracias.') +
          (data.data && data.data.folio ? ' Folio: ' + data.data.folio : '');
        showAlert(msg, 'success');
        var modalMsg = document.getElementById('modalAvistOkMsg');
        if (modalMsg) modalMsg.textContent = msg;
        var modalEl = document.getElementById('modalAvistOk');
        if (modalEl && typeof bootstrap !== 'undefined') {
          bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
        document.getElementById('formAvistPublico').reset();
        document.getElementById('avistGpsTxt').textContent = 'Sin coordenadas';
        document.getElementById('avistLat').value = '';
        document.getElementById('avistLng').value = '';
        ['okCondicion','okRef','okNombre','okApellidos','okTel','okCorreo','okFoto','okGps','okDir'].forEach(function (id) {
          setOk(id, false);
        });
        document.querySelectorAll('#formAvistPublico .is-valid, #formAvistPublico .is-invalid').forEach(function (el) {
          el.classList.remove('is-valid', 'is-invalid');
        });
      } catch (err) {
        showAlert('Error de red. Verifica tu conexión.', 'danger');
      } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Enviar avistamiento'; }
      }
    });
  })();
  </script>

</body>
</html>
