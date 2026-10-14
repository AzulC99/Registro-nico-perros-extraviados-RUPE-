<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Conoce cómo funciona RUPE CDMX: registro de mascotas extraviadas, ficha PDF, QR, GPS y avistamientos en la Ciudad de México.">
  <title>Conocer el Sistema — RUPE CDMX</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root {
      --guinda: #6B1D2F;
      --guinda-hover: #4A1320;
      --guinda-soft: rgba(107, 29, 47, 0.08);
      --cream: #F8F5F2;
    }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background: #F8F9FA; color: #333; }
    .navbar-rupe {
      background: rgba(255,255,255,.92);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(107,29,47,.12);
      transition: background .3s;
    }
    .navbar-rupe.scrolled { background: var(--guinda); box-shadow: 0 4px 20px rgba(0,0,0,.12); }
    .navbar-rupe.scrolled .nav-link,
    .navbar-rupe.scrolled .navbar-brand { color: #fff !important; }
    .nav-link { color: var(--guinda) !important; font-weight: 600; }

    /* Evitar menús duplicados / overflow del collapse */
    #navbarConocer .navbar-collapse.show { background: rgba(255,255,255,.98); border-radius: 0 0 12px 12px; }
    #navbarConocer .navbar-nav .nav-link.active { text-decoration: underline; text-underline-offset: 4px; }
    @media (max-width: 991.98px) {
      #navbarConocer .navbar-nav { padding: .75rem 0; }
      #navbarConocer .btn { margin: .25rem 0; display: inline-block; }
    }

    .logo-inst { height: 40px; width: auto; object-fit: contain; }
    .btn-guinda {
      background: var(--guinda); color: #fff; border: none; border-radius: 25px;
      padding: .55rem 1.4rem; font-weight: 600;
    }
    .btn-guinda:hover { background: var(--guinda-hover); color: #fff; }
    .btn-outline-guinda {
      border: 2px solid var(--guinda); color: var(--guinda); border-radius: 25px;
      padding: .45rem 1.2rem; font-weight: 600; background: transparent;
    }
    .btn-outline-guinda:hover { background: var(--guinda); color: #fff; }
    .hero-conocer {
      background: linear-gradient(135deg, var(--guinda) 0%, #8B2E42 55%, #A33D52 100%);
      color: #fff; padding: 7rem 0 4.5rem; position: relative; overflow: hidden;
    }
    .hero-conocer::after {
      content: ''; position: absolute; inset: 0;
      background: radial-gradient(circle at 80% 20%, rgba(255,255,255,.12), transparent 50%);
      pointer-events: none;
    }
    .step-card, .tool-card {
      background: #fff; border-radius: 16px; border: 1px solid rgba(107,29,47,.1);
      box-shadow: 0 8px 24px rgba(107,29,47,.06); height: 100%;
      transition: transform .2s, box-shadow .2s;
    }
    .step-card:hover, .tool-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 14px 32px rgba(107,29,47,.12);
    }
    .step-num {
      width: 48px; height: 48px; border-radius: 50%;
      background: var(--guinda); color: #fff;
      display: inline-flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 1.2rem;
    }
    .icon-circle {
      width: 56px; height: 56px; border-radius: 14px;
      background: var(--guinda-soft); color: var(--guinda);
      display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem;
    }
    .section-title { color: var(--guinda); font-weight: 700; }
    .badge-rupe {
      background: rgba(255,255,255,.2); color: #fff; border-radius: 20px;
      padding: .35rem .9rem; font-weight: 600; font-size: .85rem;
    }
    .ficha-preview {
      background: var(--cream); border-radius: 16px; border: 1px dashed rgba(107,29,47,.25);
      padding: 1.5rem;
    }
    .footer-rupe { background: var(--guinda); color: #fff; padding: 2.5rem 0 1.5rem; margin-top: 3rem; }
    .footer-rupe a { transition: opacity .2s; }
    .footer-rupe a:hover { opacity: .85; color: #fff !important; }

    .navbar-toggler { border: 1px solid rgba(107,29,47,.25) !important; border-radius: 10px; padding: .4rem .55rem; }
    .navbar-toggler:focus { box-shadow: 0 0 0 .2rem rgba(107,29,47,.2); }
    .navbar-rupe.scrolled .navbar-toggler { border-color: rgba(255,255,255,.45) !important; }
    .navbar-rupe.scrolled .navbar-toggler-icon { filter: brightness(0) invert(1); }
    @media (max-width: 991.98px) {
      #navConocerUnico {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 12px 32px rgba(0,0,0,.15);
        padding: .75rem;
        margin-top: .5rem;
      }
      .navbar-rupe.scrolled #navConocerUnico .nav-link { color: var(--guinda) !important; }
      #navConocerUnico .btn { display: inline-block; margin: .25rem 0; width: 100%; text-align: center; }
    }
    html { scroll-behavior: smooth; }
    #pasos, #herramientas { scroll-margin-top: 88px; }

  </style>
</head>
<body>
  <!-- Menú principal (cada click funcional) -->
  <nav class="navbar navbar-expand-lg fixed-top navbar-rupe py-2" id="navbarConocer">
    <div class="container">
      <a class="navbar-brand fw-bold text-uppercase d-flex align-items-center gap-2" href="/" style="color: var(--guinda);">
        <img src="/assets/img/logo-unadm.png" alt="SEP · UnADM" class="logo-inst" onerror="this.style.display='none'">
        <span class="d-none d-sm-inline">RUPE CDMX</span>
      </a>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navConocerUnico"
              aria-controls="navConocerUnico" aria-expanded="false" aria-label="Abrir menú">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navConocerUnico">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
          <li class="nav-item"><a class="nav-link" href="/">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="/#quienes-somos">Quiénes Somos</a></li>
          <li class="nav-item"><a class="nav-link active" href="/conocer" aria-current="page">Conocer el Sistema</a></li>
          <li class="nav-item"><a class="nav-link" href="#pasos">Flujo de localización</a></li>
          <li class="nav-item"><a class="nav-link" href="#herramientas">Herramientas del tutor</a></li>
          <li class="nav-item"><a class="nav-link" href="/#busqueda">Mascotas Extraviadas</a></li>
          <li class="nav-item"><a class="nav-link" href="/blog">Blog</a></li>
          <li class="nav-item"><a class="nav-link" href="/#faq">FAQ</a></li>
          <li class="nav-item"><a class="nav-link" href="/#contacto">Contacto</a></li>
          <li class="nav-item ms-lg-2">
            <a class="btn btn-outline-guinda" href="/?open=login#inicio" role="button">
              <i class="bi bi-box-arrow-in-right"></i> Ingresar
            </a>
          </li>
          <li class="nav-item">
            <a class="btn btn-guinda" href="/?open=registro#inicio" role="button">
              <i class="bi bi-person-plus"></i> Registro
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <section class="hero-conocer text-center">
    <div class="container position-relative" style="z-index:1">
      <span class="badge-rupe mb-3 d-inline-block"><i class="bi bi-info-circle me-1"></i> Guía del sistema</span>
      <h1 class="display-5 fw-bold mb-3">¿Cómo funciona RUPE CDMX?</h1>
      <p class="lead mx-auto mb-4" style="max-width:640px;opacity:.95">
        Registro Único de Mascotas Extraviadas: ficha oficial, código QR, GPS y avistamientos
        para ayudar a reunir perros con sus tutores en la Ciudad de México.
      </p>
      <a href="#pasos" class="btn btn-light btn-lg me-2 fw-semibold" style="border-radius:25px;color:var(--guinda)">
        <i class="bi bi-list-ol me-1"></i> Ver pasos
      </a>
      <a href="/#registro" class="btn btn-outline-light btn-lg fw-semibold" style="border-radius:25px">
        Crear cuenta tutor
      </a>
    </div>
  </section>

  <section id="pasos" class="py-5">
    <div class="container py-3">
      <div class="text-center mb-5">
        <h2 class="section-title">Flujo de localización</h2>
        <p class="text-muted mb-0">Cuatro pasos simples desde el registro hasta el reencuentro</p>
      </div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-3">
          <div class="step-card p-4 text-center">
            <div class="step-num mb-3">1</div>
            <h5 class="fw-bold">Registra a tu mascota</h5>
            <p class="text-muted small mb-0">Crea tu cuenta de tutor e ingresa datos, foto, raza, alcaldía y señas particulares.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="step-card p-4 text-center">
            <div class="step-num mb-3">2</div>
            <h5 class="fw-bold">Ficha PDF + QR</h5>
            <p class="text-muted small mb-0">El sistema genera un folio único, ficha imprimible y un código QR para el collar.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="step-card p-4 text-center">
            <div class="step-num mb-3">3</div>
            <h5 class="fw-bold">Avistamientos GPS</h5>
            <p class="text-muted small mb-0">Quien escanee el QR o vea la ficha puede reportar ubicación y foto. Tú recibes aviso.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="step-card p-4 text-center">
            <div class="step-num mb-3">4</div>
            <h5 class="fw-bold">Seguimiento</h5>
            <p class="text-muted small mb-0">Consulta bitácora, actualiza estatus y coordina el reencuentro con ayuda del panel.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="herramientas" class="py-5 bg-white">
    <div class="container py-3">
      <div class="text-center mb-5">
        <h2 class="section-title">Herramientas del panel tutor</h2>
        <p class="text-muted mb-0">Todo lo que necesitas en un solo lugar</p>
      </div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-4">
          <div class="tool-card p-4">
            <div class="icon-circle mb-3"><i class="bi bi-pencil-square"></i></div>
            <h5 class="fw-bold">Registro y CRUD</h5>
            <p class="text-muted small mb-0">Alta, edición y baja de reportes con catálogos de razas y alcaldías de la CDMX.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="tool-card p-4">
            <div class="icon-circle mb-3"><i class="bi bi-qr-code"></i></div>
            <h5 class="fw-bold">GPS / QR</h5>
            <p class="text-muted small mb-0">Captura coordenadas, busca dirección en el mapa y envía avisamiento al tutor.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="tool-card p-4">
            <div class="icon-circle mb-3"><i class="bi bi-file-earmark-pdf"></i></div>
            <h5 class="fw-bold">Ficha PDF</h5>
            <p class="text-muted small mb-0">Descarga carta oficial con foto, datos, QR y último punto GPS registrado.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="tool-card p-4">
            <div class="icon-circle mb-3"><i class="bi bi-journal-text"></i></div>
            <h5 class="fw-bold">Seguimiento</h5>
            <p class="text-muted small mb-0">Historial de avistamientos, fotos reportadas y referencias de ubicación.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="tool-card p-4">
            <div class="icon-circle mb-3"><i class="bi bi-bar-chart-line"></i></div>
            <h5 class="fw-bold">Analítico</h5>
            <p class="text-muted small mb-0">Indicadores, gráficos e insights de tus casos en búsqueda o localizados.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="tool-card p-4">
            <div class="icon-circle mb-3"><i class="bi bi-house-heart"></i></div>
            <h5 class="fw-bold">Albergues</h5>
            <p class="text-muted small mb-0">Directorio y asignación de casos en resguardo a albergues de la ciudad.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5">
    <div class="container py-3">
      <div class="row align-items-center g-4">
        <div class="col-lg-6">
          <h2 class="section-title mb-3">Ficha oficial y código QR</h2>
          <p class="text-muted">
            Cada reporte genera un <strong>folio RUPE</strong> único. La ficha en tamaño carta incluye
            foto, raza, tamaño, alcaldía, collar/microchip, señas y el QR que cualquier persona
            puede escanear para ver la ficha pública y reportar un avistamiento.
          </p>
          <ul class="list-unstyled mb-4">
            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Compatible con collar o placa</li>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Enlace público sin necesidad de cuenta</li>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Notificación al tutor al reportar GPS</li>
          </ul>
          <a href="/?open=login#inicio" class="btn btn-outline-guinda me-2"><i class="bi bi-box-arrow-in-right"></i> Ingresar</a>
          <a href="/?open=registro#inicio" class="btn btn-guinda"><i class="bi bi-person-plus"></i> Registro</a>
        </div>
        <div class="col-lg-6">
          <div class="ficha-preview">
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="bg-secondary bg-opacity-25 rounded" style="width:88px;height:88px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-image fs-2 text-muted"></i>
              </div>
              <div>
                <div class="small text-muted">Folio ejemplo</div>
                <div class="fw-bold" style="color:var(--guinda)">#RUPE-2026XXXX-XXXX</div>
                <div class="small">Estatus: <span class="badge bg-warning text-dark">En Búsqueda</span></div>
              </div>
              <div class="ms-auto text-center">
                <i class="bi bi-qr-code fs-1" style="color:var(--guinda)"></i>
                <div class="small text-muted">QR</div>
              </div>
            </div>
            <hr>
            <div class="row small text-muted g-2">
              <div class="col-6">Raza · Tamaño</div>
              <div class="col-6">Alcaldía / Colonia</div>
              <div class="col-6">Fecha de extravío</div>
              <div class="col-6">Último GPS</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 bg-white border-top">
    <div class="container text-center py-3">
      <h2 class="section-title mb-3">¿Listo para registrar un caso?</h2>
      <p class="text-muted mb-4 mx-auto" style="max-width:520px">
        Crea tu cuenta de tutor de forma gratuita y genera la ficha de búsqueda en minutos.
      </p>
      <a href="/?open=registro#inicio" class="btn btn-guinda btn-lg me-2"><i class="bi bi-person-plus"></i> Registro</a>
      <a href="/?open=login#inicio" class="btn btn-outline-guinda btn-lg me-2"><i class="bi bi-box-arrow-in-right"></i> Ingresar</a>
      <a href="/blog" class="btn btn-outline-guinda btn-lg">Leer el blog</a>
    </div>
  </section>

  <footer class="footer-rupe">
    <div class="container">
      <div class="row g-4 mb-4">
        <div class="col-lg-4">
          <h6 class="fw-bold mb-2">RUPE CDMX</h6>
          <p class="small text-white-50 mb-0">Proyecto académico UnADM · SEP. Plataforma de apoyo para la localización de mascotas en la Ciudad de México.</p>
        </div>
        <div class="col-lg-4 col-md-4">
          <h6 class="fw-bold mb-2">Navegación</h6>
          <ul class="list-unstyled small mb-0">
            <li><a href="/" class="text-white-50 text-decoration-none">Inicio</a></li>
            <li><a href="/conocer" class="text-white-50 text-decoration-none">Conocer el Sistema</a></li>
            <li><a href="/conocer#herramientas" class="text-white-50 text-decoration-none">Herramientas del tutor</a></li>
            <li><a href="/blog" class="text-white-50 text-decoration-none">Blog</a></li>
            <li><a href="/#contacto" class="text-white-50 text-decoration-none">Contacto</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-4">
          <h6 class="fw-bold mb-2">Legales</h6>
          <ul class="list-unstyled small mb-0">
            <li><a href="/aviso-privacidad" class="text-white-50 text-decoration-none">Aviso de Privacidad</a></li>
            <li><a href="/terminos-servicio" class="text-white-50 text-decoration-none">Términos del Servicio</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-4">
          <h6 class="fw-bold mb-2">Redes</h6>
          <div class="d-flex gap-3 fs-5">
            <a href="https://www.facebook.com/gobmexico" target="_blank" rel="noopener" class="text-white" title="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="https://x.com/GobiernoMX" target="_blank" rel="noopener" class="text-white" title="X"><i class="bi bi-twitter-x"></i></a>
          </div>
        </div>
      </div>
      <hr class="border-secondary opacity-25">
      <p class="text-center small text-white-50 mb-0">&copy; 2026 RUPE CDMX · UnADM · SEP</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      var nav = document.getElementById('navbarConocer') || document.querySelector('.navbar-rupe');
      var collapseEl = document.getElementById('navConocerUnico');

      window.addEventListener('scroll', function () {
        if (!nav) return;
        nav.classList.toggle('scrolled', window.scrollY > 40);
      });

      function closeMenu() {
        if (!collapseEl) return;
        try {
          if (window.bootstrap) {
            var inst = bootstrap.Collapse.getInstance(collapseEl);
            if (inst) inst.hide();
            else collapseEl.classList.remove('show');
          } else {
            collapseEl.classList.remove('show');
          }
        } catch (_) {
          collapseEl.classList.remove('show');
        }
      }

      // Cada click del menú cierra el collapse en móvil
      if (collapseEl) {
        collapseEl.querySelectorAll('a.nav-link, a.btn').forEach(function (a) {
          a.addEventListener('click', function (ev) {
            closeMenu();
            // Anclas internas (#pasos, #herramientas): scroll suave
            var href = a.getAttribute('href') || '';
            if (href.charAt(0) === '#' && href.length > 1) {
              var target = document.querySelector(href);
              if (target) {
                ev.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                try { history.replaceState(null, '', href); } catch (_) {}
              }
            }
          });
        });
      }

      // Si se abre con hash (#herramientas / #pasos)
      if (location.hash) {
        var el = document.querySelector(location.hash);
        if (el) {
          setTimeout(function () {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }, 120);
        }
      }
    })();
  </script>
</body>
</html>
