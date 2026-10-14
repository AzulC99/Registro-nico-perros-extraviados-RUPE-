<?php
/**
 * RUPE CDMX — Blog / Guías
 * app/views/home/blog.php
 */
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Blog RUPE CDMX: guías para localizar mascotas extraviadas, uso de QR, GPS, ficha PDF y protocolos de avistamiento en la Ciudad de México.">
  <title>Blog y Guías — RUPE CDMX</title>
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
    .navbar-rupe.scrolled .btn-outline-guinda { border-color: #fff; color: #fff; }
    .navbar-rupe.scrolled .btn-outline-guinda:hover { background: #fff; color: var(--guinda); }
    .nav-link { color: var(--guinda) !important; font-weight: 600; }
    .nav-link.active { text-decoration: underline; text-underline-offset: 4px; }
    .logo-inst { height: 40px; width: auto; object-fit: contain; }
    .btn-guinda {
      background: var(--guinda); color: #fff; border: none; border-radius: 25px;
      padding: .55rem 1.4rem; font-weight: 600;
    }
    .btn-guinda:hover { background: var(--guinda-hover); color: #fff; }
    .btn-outline-guinda {
      border: 2px solid var(--guinda); color: var(--guinda); border-radius: 25px;
      padding: .4rem 1.1rem; font-weight: 600; background: transparent;
    }
    .btn-outline-guinda:hover { background: var(--guinda); color: #fff; }
    .hero-blog {
      background: linear-gradient(135deg, var(--guinda) 0%, #8B2E42 55%, #A33D52 100%);
      color: #fff; padding: 7rem 0 4rem; position: relative; overflow: hidden;
    }
    .hero-blog::after {
      content: ''; position: absolute; inset: 0;
      background: radial-gradient(circle at 20% 80%, rgba(255,255,255,.1), transparent 50%);
      pointer-events: none;
    }
    .badge-rupe {
      background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.35);
      border-radius: 20px; padding: .35rem .9rem; font-size: .85rem; font-weight: 600;
    }
    .section-title {
      font-weight: 800; color: var(--guinda); position: relative; display: inline-block;
    }
    .section-title::after {
      content: ''; display: block; width: 48px; height: 3px; background: var(--guinda);
      margin: .4rem auto 0; border-radius: 2px;
    }
    .card-post {
      background: #fff; border: 1px solid rgba(107,29,47,.1); border-radius: 16px;
      box-shadow: 0 8px 24px rgba(107,29,47,.06); height: 100%;
      transition: transform .2s, box-shadow .2s; overflow: hidden;
    }
    .card-post:hover {
      transform: translateY(-6px);
      box-shadow: 0 14px 32px rgba(107,29,47,.12);
    }
    .card-post .post-icon {
      width: 56px; height: 56px; border-radius: 14px; background: var(--guinda-soft);
      color: var(--guinda); display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem;
    }
    .tag {
      display: inline-block; font-size: .75rem; font-weight: 600;
      background: var(--guinda-soft); color: var(--guinda);
      border-radius: 20px; padding: .2rem .65rem;
    }
    .alert-banner {
      background: linear-gradient(135deg, #B02A37 0%, var(--guinda) 100%);
      color: #fff; border-radius: 14px; border: none;
    }
    .accordion-button:not(.collapsed) {
      background: #F8E9EC; color: var(--guinda); font-weight: 700;
    }
    .accordion-button:focus {
      box-shadow: 0 0 0 .2rem rgba(107, 29, 47, 0.2);
    }
    .footer-rupe {
      background: #2b1016; color: #fff; padding: 3rem 0 1.5rem; margin-top: 3rem;
    }

    .navbar-toggler { border: 1px solid rgba(107,29,47,.25) !important; border-radius: 10px; padding: .4rem .55rem; }
    .navbar-toggler:focus { box-shadow: 0 0 0 .2rem rgba(107,29,47,.2); }
    .navbar-rupe.scrolled .navbar-toggler { border-color: rgba(255,255,255,.45) !important; }
    .navbar-rupe.scrolled .navbar-toggler-icon { filter: brightness(0) invert(1); }
    @media (max-width: 991.98px) {
      #navBlogUnico {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 12px 32px rgba(0,0,0,.15);
        padding: .75rem;
        margin-top: .5rem;
      }
      .navbar-rupe.scrolled #navBlogUnico .nav-link { color: var(--guinda) !important; }
      #navBlogUnico .btn { display: inline-block; margin: .25rem 0; width: 100%; text-align: center; }
    }

    .tip-card {
      background: #fff; border-left: 4px solid var(--guinda); border-radius: 0 12px 12px 0;
      box-shadow: 0 4px 16px rgba(0,0,0,.06); padding: 1.25rem 1.5rem; height: 100%;
    }
  </style>
</head>
<body>

  <!-- Menú principal (clicks activos) -->
  <nav class="navbar navbar-expand-lg fixed-top navbar-rupe py-2" id="navbarBlog">
    <div class="container">
      <a class="navbar-brand fw-bold text-uppercase d-flex align-items-center gap-2" href="/" style="color: var(--guinda);">
        <img src="/assets/img/logo-unadm.png" alt="SEP · UnADM" class="logo-inst" onerror="this.style.display='none'">
        <span class="d-none d-sm-inline">RUPE CDMX</span>
      </a>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navBlogUnico"
              aria-controls="navBlogUnico" aria-expanded="false" aria-label="Abrir menú">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navBlogUnico">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
          <li class="nav-item"><a class="nav-link" href="/">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="/#quienes-somos">Quiénes Somos</a></li>
          <li class="nav-item"><a class="nav-link" href="/conocer">Conocer el Sistema</a></li>
          <li class="nav-item"><a class="nav-link" href="/conocer#herramientas">Herramientas del tutor</a></li>
          <li class="nav-item"><a class="nav-link" href="/#busqueda">Mascotas Extraviadas</a></li>
          <li class="nav-item"><a class="nav-link active" href="/blog" aria-current="page">Blog</a></li>
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

  <section class="hero-blog text-center">
    <div class="container position-relative" style="z-index:1">
      <span class="badge-rupe mb-3 d-inline-block"><i class="bi bi-journal-richtext me-1"></i> Blog y guías</span>
      <h1 class="display-5 fw-bold mb-3">Información útil para encontrar a tu mascota</h1>
      <p class="lead mx-auto mb-0" style="max-width:640px;opacity:.95">
        Protocolos de búsqueda, uso de ficha QR, GPS y buenas prácticas para tutores y ciudadanos en la CDMX.
      </p>
    </div>
  </section>

  <div class="container py-5">

    <!-- Aviso operativo -->
    <div class="alert alert-banner d-flex flex-column flex-md-row align-items-md-center gap-3 mb-5 p-4">
      <div class="flex-grow-1">
        <h5 class="fw-bold mb-1"><i class="bi bi-megaphone me-2"></i>¿Perdiste a tu perro?</h5>
        <p class="mb-0 small opacity-90">Regístralo en RUPE, genera la ficha con QR y comparte el folio. Los avistamientos llegan directo a tu panel de tutor.</p>
      </div>
      <div class="d-flex gap-2 flex-shrink-0">
        <a href="/?open=registro#inicio" class="btn btn-light fw-semibold" style="border-radius:25px;color:var(--guinda)"><i class="bi bi-person-plus me-1"></i>Registro</a>
        <a href="/?open=login#inicio" class="btn btn-outline-light fw-semibold" style="border-radius:25px"><i class="bi bi-box-arrow-in-right me-1"></i>Ingresar</a>
        <a href="/conocer" class="btn btn-outline-light fw-semibold" style="border-radius:25px">Cómo funciona</a>
      </div>
    </div>

    <!-- Artículos -->
    <div class="text-center mb-4">
      <h2 class="section-title">Guías recomendadas</h2>
      <p class="text-muted">Pasos prácticos alineados con el sistema RUPE CDMX</p>
    </div>
    <div class="row g-4 mb-5">
      <div class="col-md-6 col-lg-4">
        <article class="card-post p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="post-icon"><i class="bi bi-clipboard2-check"></i></div>
            <span class="tag">Registro</span>
          </div>
          <h3 class="h5 fw-bold">Cómo registrar un extravío en minutos</h3>
          <p class="text-muted small">
            Completa nombre, raza, alcaldía, colonia, calle y foto. El sistema asigna un
            <strong>folio RUPE</strong> y prepara la ficha oficial lista para compartir.
          </p>
          <a href="/conocer" class="text-decoration-none fw-semibold" style="color:var(--guinda)">
            Ver flujo del sistema <i class="bi bi-arrow-right"></i>
          </a>
        </article>
      </div>
      <div class="col-md-6 col-lg-4">
        <article class="card-post p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="post-icon"><i class="bi bi-qr-code-scan"></i></div>
            <span class="tag">QR y ficha</span>
          </div>
          <h3 class="h5 fw-bold">Para qué sirve el código QR de la ficha</h3>
          <p class="text-muted small">
            Cualquier persona puede escanearlo y abrir la ficha pública: datos de la mascota,
            último GPS y opción de reportar un avistamiento sin crear cuenta.
          </p>
          <a href="/#busqueda" class="text-decoration-none fw-semibold" style="color:var(--guinda)">
            Ver mascotas en búsqueda <i class="bi bi-arrow-right"></i>
          </a>
        </article>
      </div>
      <div class="col-md-6 col-lg-4">
        <article class="card-post p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="post-icon"><i class="bi bi-geo-alt"></i></div>
            <span class="tag">GPS</span>
          </div>
          <h3 class="h5 fw-bold">Reportar ubicación con el mapa</h3>
          <p class="text-muted small">
            Desde GPS/QR o un avistamiento ciudadano se guardan latitud y longitud.
            El tutor recibe el aviso y puede seguir la trazabilidad en su panel.
          </p>
          <a href="/conocer#pasos" class="text-decoration-none fw-semibold" style="color:var(--guinda)">
            Ver los 4 pasos <i class="bi bi-arrow-right"></i>
          </a>
        </article>
      </div>
      <div class="col-md-6 col-lg-4">
        <article class="card-post p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="post-icon"><i class="bi bi-shield-check"></i></div>
            <span class="tag">Privacidad</span>
          </div>
          <h3 class="h5 fw-bold">Tus datos están protegidos</h3>
          <p class="text-muted small">
            La ficha pública muestra lo necesario para identificar a la mascota.
            Teléfono y correo del tutor se gestionan con control de privacidad en tu cuenta.
          </p>
          <a href="/aviso-privacidad" class="text-decoration-none fw-semibold" style="color:var(--guinda)">
            Aviso de privacidad <i class="bi bi-arrow-right"></i>
          </a>
        </article>
      </div>
      <div class="col-md-6 col-lg-4">
        <article class="card-post p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="post-icon"><i class="bi bi-house-heart"></i></div>
            <span class="tag">Resguardo</span>
          </div>
          <h3 class="h5 fw-bold">Albergues y casos en resguardo</h3>
          <p class="text-muted small">
            Si tu mascota está en resguardo, puedes asignar un albergue del directorio
            y mantener el estatus actualizado en el módulo de seguimiento.
          </p>
          <a href="/conocer#herramientas" class="text-decoration-none fw-semibold" style="color:var(--guinda)">
            Herramientas del tutor <i class="bi bi-arrow-right"></i>
          </a>
        </article>
      </div>
      <div class="col-md-6 col-lg-4">
        <article class="card-post p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="post-icon"><i class="bi bi-share"></i></div>
            <span class="tag">Difusión</span>
          </div>
          <h3 class="h5 fw-bold">Cómo difundir el cartel con eficacia</h3>
          <p class="text-muted small">
            Comparte el PDF o la ficha en redes de tu colonia, grupos vecinales y
            cuentas oficiales. Incluye siempre el folio y el QR.
          </p>
          <div class="d-flex gap-2 mt-2">
            <a href="https://www.facebook.com/gobmexico" target="_blank" rel="noopener" class="btn btn-sm btn-outline-guinda" title="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="https://x.com/GobiernoMX" target="_blank" rel="noopener" class="btn btn-sm btn-outline-guinda" title="X"><i class="bi bi-twitter-x"></i></a>
          </div>
        </article>
      </div>
    </div>

    <!-- Tips rápidos -->
    <div class="text-center mb-4">
      <h2 class="section-title">Consejos rápidos</h2>
    </div>
    <div class="row g-3 mb-5">
      <div class="col-md-4">
        <div class="tip-card">
          <h6 class="fw-bold" style="color:var(--guinda)"><i class="bi bi-1-circle me-1"></i> Actúa en las primeras horas</h6>
          <p class="small text-muted mb-0">Registra el caso de inmediato: fecha, hora, zona y foto actualizan las posibilidades de reencuentro.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="tip-card">
          <h6 class="fw-bold" style="color:var(--guinda)"><i class="bi bi-2-circle me-1"></i> Revisa el panel a diario</h6>
          <p class="small text-muted mb-0">Avistamientos, GPS y cambios de estatus quedan en la bitácora de seguimiento del tutor.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="tip-card">
          <h6 class="fw-bold" style="color:var(--guinda)"><i class="bi bi-3-circle me-1"></i> Mantén datos al día</h6>
          <p class="small text-muted mb-0">Collar, microchip, color y señas particulares ayudan a validar coincidencias.</p>
        </div>
      </div>
    </div>

    <!-- FAQ del blog -->
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="text-center mb-4">
          <h2 class="section-title">Preguntas frecuentes</h2>
        </div>
        <div class="accordion shadow-sm rounded-3 overflow-hidden bg-white" id="accordionBlogFaq">
          <div class="accordion-item border-0">
            <h2 class="accordion-header">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#bfaq1">
                1. ¿El registro en RUPE tiene costo?
              </button>
            </h2>
            <div id="bfaq1" class="accordion-collapse collapse show" data-bs-parent="#accordionBlogFaq">
              <div class="accordion-body text-secondary small">
                No. El registro de tutor y de reportes de extravío es <strong>gratuito</strong>. El objetivo es facilitar la localización y el reencuentro.
              </div>
            </div>
          </div>
          <div class="accordion-item border-0 border-top">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bfaq2">
                2. ¿Qué hago si encuentro un perro con QR de RUPE?
              </button>
            </h2>
            <div id="bfaq2" class="accordion-collapse collapse" data-bs-parent="#accordionBlogFaq">
              <div class="accordion-body text-secondary small">
                Escanea el código con la cámara del celular. Verás la ficha pública y podrás reportar la ubicación (GPS) y, si aplica, una fotografía del avistamiento.
              </div>
            </div>
          </div>
          <div class="accordion-item border-0 border-top">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bfaq3">
                3. ¿Mis datos personales se publican?
              </button>
            </h2>
            <div id="bfaq3" class="accordion-collapse collapse" data-bs-parent="#accordionBlogFaq">
              <div class="accordion-body text-secondary small">
                No se publican de forma abierta. Puedes configurar preferencias de privacidad en tu cuenta. Consulta el
                <a href="/aviso-privacidad" class="text-decoration-none" style="color:var(--guinda)">Aviso de Privacidad</a>.
              </div>
            </div>
          </div>
          <div class="accordion-item border-0 border-top">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bfaq4">
                4. ¿Cómo actualizo el estatus o la ubicación?
              </button>
            </h2>
            <div id="bfaq4" class="accordion-collapse collapse" data-bs-parent="#accordionBlogFaq">
              <div class="accordion-body text-secondary small">
                Inicia sesión como tutor y usa los módulos <strong>Estatus</strong>, <strong>GPS / QR</strong> y <strong>Seguimiento</strong> para mantener el expediente al día.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA final -->
    <div class="text-center mt-5 pt-4">
      <h2 class="h4 fw-bold" style="color:var(--guinda)">¿Listo para generar tu ficha?</h2>
      <p class="text-muted mb-3">Crea tu cuenta de tutor y registra el caso en pocos pasos.</p>
      <a href="/?open=registro#inicio" class="btn btn-guinda btn-lg me-2"><i class="bi bi-person-plus me-1"></i>Registro</a>
      <a href="/?open=login#inicio" class="btn btn-outline-guinda btn-lg me-2"><i class="bi bi-box-arrow-in-right me-1"></i>Ingresar</a>
      <a href="/conocer" class="btn btn-outline-guinda btn-lg">Conocer el sistema</a>
    </div>
  </div>

  <footer class="footer-rupe">
    <div class="container">
      <div class="row g-4 mb-4">
        <div class="col-lg-4">
          <h6 class="fw-bold mb-2">RUPE CDMX</h6>
          <p class="small text-white-50 mb-0">Proyecto académico UnADM · SEP. Plataforma de apoyo para la localización de mascotas en la Ciudad de México.</p>
        </div>
        <div class="col-lg-4 col-md-6">
          <h6 class="fw-bold mb-2">Navegación</h6>
          <ul class="list-unstyled small mb-0">
            <li><a href="/" class="text-white-50 text-decoration-none">Inicio</a></li>
            <li><a href="/conocer" class="text-white-50 text-decoration-none">Conocer el Sistema</a></li>
            <li><a href="/blog" class="text-white-50 text-decoration-none">Blog</a></li>
            <li><a href="/#contacto" class="text-white-50 text-decoration-none">Contacto</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-3">
          <h6 class="fw-bold mb-2">Legales</h6>
          <ul class="list-unstyled small mb-0">
            <li><a href="/aviso-privacidad" class="text-white-50 text-decoration-none">Aviso de Privacidad</a></li>
            <li><a href="/terminos-servicio" class="text-white-50 text-decoration-none">Términos del Servicio</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-3">
          <h6 class="fw-bold mb-2">Redes</h6>
          <div class="d-flex gap-3 fs-5">
            <a href="https://www.facebook.com/gobmexico" target="_blank" rel="noopener" class="text-white" title="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="https://x.com/GobiernoMX" target="_blank" rel="noopener" class="text-white" title="X"><i class="bi bi-twitter-x"></i></a>
          </div>
        </div>
      </div>
      <hr class="border-secondary opacity-25">
      <p class="text-center small text-white-50 mb-0">&copy; <?= date('Y') ?> RUPE CDMX · UnADM · SEP</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      var nav = document.getElementById('navbarBlog');
      var collapseEl = document.getElementById('navBlogUnico');

      window.addEventListener('scroll', function () {
        if (!nav) return;
        nav.classList.toggle('scrolled', window.scrollY > 40);
      });

      function closeMenu() {
        if (!collapseEl || !window.bootstrap) return;
        try {
          var inst = bootstrap.Collapse.getInstance(collapseEl);
          if (inst) inst.hide();
          else collapseEl.classList.remove('show');
        } catch (_) {
          collapseEl.classList.remove('show');
        }
      }

      // Cada click de menú cierra el collapse en móvil y navega
      if (collapseEl) {
        collapseEl.querySelectorAll('a.nav-link, a.btn').forEach(function (a) {
          a.addEventListener('click', function () {
            closeMenu();
          });
        });
      }
    })();
  </script>
</body>
</html>
