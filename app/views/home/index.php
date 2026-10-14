<?php
/**
 * RUPE CDMX — Portada
 * app/views/home/index.php
 */
declare(strict_types=1);
$recaptchaSiteKey = defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '';
$appUrl = defined('APP_URL') ? APP_URL : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RUPE - Registro Único de Perros Extraviados</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons & Animate.css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --guinda-principal: #6B1D2F;
            --guinda-hover: #4A1320;
            --guinda-soft: rgba(107, 29, 47, 0.08);
            --blanco-puro: #FFFFFF;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(107, 29, 47, 0.15);
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #F8F9FA;
            color: #333333;
            overflow-x: hidden;
        }

        /* Navbar Transparente con Glassmorphism */
        .navbar-rupe {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            transition: all 0.4s ease;
        }

        .navbar-rupe.scrolled {
            background: rgba(107, 29, 47, 0.95);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .navbar-rupe.scrolled .nav-link,
        .navbar-rupe.scrolled .navbar-brand {
            color: var(--blanco-puro) !important;
        }

        .nav-link {
            color: var(--guinda-principal) !important;
            font-weight: 600;
            margin: 0 5px;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: var(--guinda-principal);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .navbar-rupe.scrolled .nav-link::after {
            background-color: var(--blanco-puro);
        }

        .nav-link:hover::after {
            width: 80%;
        }

        /* Botones personalizados Guinda */
        .btn-guinda {
            background-color: var(--guinda-principal);
            color: var(--blanco-puro);
            border: none;
            border-radius: 25px;
            padding: 8px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-guinda:hover {
            background-color: var(--guinda-hover);
            color: var(--blanco-puro);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(107, 29, 47, 0.3);
        }

        .btn-outline-guinda {
            border: 2px solid var(--guinda-principal);
            color: var(--guinda-principal);
            border-radius: 25px;
            padding: 6px 22px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-guinda:hover {
            background-color: var(--guinda-principal);
            color: var(--blanco-puro);
        }

        /* Carrusel Hero */
        .carousel-item {
            height: 85vh;
            min-height: 500px;
            background-size: cover;
            background-position: center;
        }

        .carousel-overlay {
            background: linear-gradient(135deg, rgba(107, 29, 47, 0.85) 0%, rgba(0, 0, 0, 0.6) 100%);
            height: 100%;
            display: flex;
            align-items: center;
        }

        /* Tarjetas de Mascotas con Glassmorphism */
        .card-mascota {
            border: none;
            border-radius: 16px;
            background: var(--glass-bg);
            backdrop-filter: blur(8px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card-mascota:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(107, 29, 47, 0.15);
        }

        .card-mascota img {
            height: 220px;
            object-fit: cover;
        }

        .badge-guinda {
            background-color: var(--guinda-principal);
            color: var(--blanco-puro);
        }

        /* Acordeón FAQ Personalizado */
        .accordion-button:not(.collapsed) {
            background-color: var(--guinda-soft);
            color: var(--guinda-principal);
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(107, 29, 47, 0.25);
        }

        /* Footer */
        .footer-rupe {
            background-color: var(--guinda-hover);
            color: var(--blanco-puro);
            padding: 50px 0 20px 0;
        }

        /* Clases de animación scroll ES6 */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    
    #formRegistro .form-control.is-valid,
    #formLogin .form-control.is-valid {
      border-color: #198754 !important;
      padding-right: calc(1.5em + 0.75rem);
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e") !important;
      background-repeat: no-repeat !important;
      background-position: right calc(0.375em + 0.1875rem) center !important;
      background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
    }
    #formRegistro .form-control.is-invalid,
    #formLogin .form-control.is-invalid {
      border-color: #dc3545 !important;
    }
    .reg-field-ok {
      color: #198754;
      font-size: 0.75rem;
      display: none;
    }
    #formRegistro .form-control.is-valid ~ .reg-field-ok,
    #formRegistro .form-control.is-valid + .form-text .reg-field-ok {
      display: inline-block !important;
    }

    </style>
  <style>
    /* Badge reCAPTCHA v3 SIEMPRE visible (esquina inferior derecha) */
    .grecaptcha-badge {
      visibility: visible !important;
      opacity: 1 !important;
      display: block !important;
      z-index: 2147483647 !important;
      position: fixed !important;
      bottom: 14px !important;
      right: 14px !important;
      width: 256px !important;
      height: 60px !important;
      box-shadow: 0 0 5px rgba(0,0,0,.2) !important;
      pointer-events: auto !important;
    }
    .rupe-recaptcha-status {
      font-size: 11px;
      color: #6c757d;
      display: flex;
      align-items: center;
      gap: 0.35rem;
      margin-top: 0.35rem;
    }
    .rupe-recaptcha-status.ok { color: #198754; }
    .rupe-recaptcha-status.err { color: #dc3545; }
  </style>

    <!-- reCAPTCHA v3: cargar en <head> para que el badge aparezca -->
    <script>
      window.__RECAPTCHA_SITE_KEY__ = <?= json_encode($recaptchaSiteKey, JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <?php if ($recaptchaSiteKey !== ''): ?>
    <script src="/assets/js/recaptcha.js?v=20260927rc2"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars($recaptchaSiteKey, ENT_QUOTES, 'UTF-8') ?>" async defer data-rupe-recaptcha-head="1"></script>
    <?php endif; ?>

<style>html{scroll-behavior:smooth;}</style>
</head>
<body>

    <!-- NAVBAR TRANSPARENTE -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-rupe py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-uppercase d-flex align-items-center gap-2" href="/" style="color: var(--guinda-principal);">
                <img src="/assets/img/logo-unadm.png" alt="SEP · UnADM" style="height:44px;width:auto;object-fit:contain;max-width:160px;" onerror="this.style.display='none'">
                <span class="d-none d-md-inline">RUPE CDMX</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navRUPE">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navRUPE">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#quienes-somos">Quiénes Somos</a></li>
                    <li class="nav-item"><a class="nav-link" href="/conocer">Conocer el Sistema</a></li>
                    <li class="nav-item"><a class="nav-link" href="#busqueda">Mascotas Extraviadas</a></li>
                    <li class="nav-item"><a class="nav-link" href="/blog">Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
                </ul>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-guinda" data-bs-toggle="modal" data-bs-target="#modalLogin">
                        <i class="bi bi-box-arrow-in-right"></i> Ingresar
                    </button>
                    <button class="btn btn-guinda" data-bs-toggle="modal" data-bs-target="#modalRegistro">
                        <i class="bi bi-person-plus"></i> Registro
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- CARRUSEL HERO -->
    <section id="inicio">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active" style="background-image: url('https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=1350&q=80');">
                    <div class="carousel-overlay">
                        <div class="container text-white">
                            <div class="row">
                                <div class="col-lg-7 animate__animated animate__fadeInLeft">
                                    <span class="badge bg-warning text-dark mb-2 px-3 py-2 fw-semibold">Red Oficial CDMX</span>
                                    <h1 class="display-3 fw-bold mb-3">Red de Localización de Caninos Extraviados</h1>
                                    <p class="lead mb-4">Unimos la tecnología de códigos QR, reportes geolocalizados y colaboración ciudadana para traer a tu mascota de vuelta a casa.</p>
                                    <a href="#busqueda" class="btn btn-guinda btn-lg me-2"><i class="bi bi-search"></i> Buscar Perro</a>
                                    <button class="btn btn-outline-light btn-lg rounded-pill" data-bs-toggle="modal" data-bs-target="#modalRegistro">Reportar Extravío</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item" style="background-image: url('https://images.unsplash.com/photo-1534361960057-19889db9621e?auto=format&fit=crop&w=1350&q=80');">
                    <div class="carousel-overlay">
                        <div class="container text-white">
                            <div class="row">
                                <div class="col-lg-7 animate__animated animate__fadeInDown">
                                    <h1 class="display-3 fw-bold mb-3">Identificación QR Inteligente</h1>
                                    <p class="lead mb-4">Genera una ficha oficial en PDF y un código QR único para el collar de tu perro. Cualquiera que lo escanee podrá enviarte un avistamiento en tiempo real.</p>
                                    <a href="/conocer" class="btn btn-guinda btn-lg"><i class="bi bi-qr-code-scan"></i> Conocer el Sistema</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>

    <!-- SECCIÓN QUIÉNES SOMOS -->
    <section id="quienes-somos" class="py-5">
        <div class="container py-4">
            <div class="row align-items-center reveal">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?auto=format&fit=crop&w=800&q=80" alt="Nosotros RUPE" class="img-fluid rounded-4 shadow-lg">
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <span class="text-uppercase fw-bold text-danger" style="color: var(--guinda-principal) !important;">Plataforma Institucional</span>
                    <h2 class="fw-bold mb-4" style="color: var(--guinda-principal);">Comprometidos con el Bienestar Animal</h2>
                    <p class="text-muted">El <strong>Registro Único de Perros Extraviados (RUPE)</strong> es una iniciativa tecnológica diseñada para resolver de manera ágil el extravío de caninos mediante un expediente estructurado, folio único, ficha de búsqueda con código QR y seguimiento de avistamientos, resguardando los datos personales del tutor.</p>
                    
                    <div class="row g-3 mt-3">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border-start border-4" style="border-color: var(--guinda-principal) !important;">
                                <i class="bi bi-shield-check fs-2" style="color: var(--guinda-principal);"></i>
                                <div>
                                    <h6 class="mb-0 fw-bold">Seguridad Cifrada</h6>
                                    <small class="text-muted">Protección estricta de datos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border-start border-4" style="border-color: var(--guinda-principal) !important;">
                                <i class="bi bi-geo-alt fs-2" style="color: var(--guinda-principal);"></i>
                                <div>
                                    <h6 class="mb-0 fw-bold">Geolocalización</h6>
                                    <small class="text-muted">Avistamientos por Alcaldía</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards Misión y Visión -->
            <div class="row g-4 mt-4 reveal">
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header border-0 text-white py-3" style="background-color: var(--guinda-principal, #6B1D2F);">
                            <h3 class="h5 mb-0 fw-bold"><i class="bi bi-bullseye me-2"></i>Misión</h3>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-0" style="text-align: justify;">
                                Facilitar la localización y recuperación de perros extraviados en la Ciudad de México mediante una plataforma web gratuita que asigne folios únicos, genere fichas de búsqueda con código QR accesible al público y permita el seguimiento en tiempo real de avistamientos ciudadanos, protegiendo en todo momento los datos personales de los tutores conforme a la legislación aplicable.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header border-0 text-dark py-3" style="background-color: var(--dorado-acento, #E8A33D);">
                            <h3 class="h5 mb-0 fw-bold"><i class="bi bi-eye me-2"></i>Visión</h3>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-0" style="text-align: justify;">
                                Ser la referencia digital confiable en la Ciudad de México para la reunificación de perros extraviados con sus familias, integrando tecnología accesible (QR, geolocalización y bitácora de seguimiento) y una comunidad solidaria de ciudadanos y tutores, con impacto medible en la reducción de animales en situación de calle por extravío.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BÚSQUEDA GENERAL Y CARDS MASCOTAS -->
    <section id="busqueda" class="py-5 bg-light">
        <div class="container py-4">
            <div class="text-center mb-5 reveal">
                <h2 class="fw-bold" style="color: var(--guinda-principal);">Mascotas Extraviadas Recientemente</h2>
                <p class="text-muted">Usa los filtros para agilizar la búsqueda de caninos reportados en la Ciudad de México</p>
            </div>

            <!-- Filtros de Búsqueda -->
            <div class="row g-3 mb-5 p-4 bg-white rounded-4 shadow-sm reveal">
                <div class="col-md-4">
                    <input type="text" id="inputBuscar" class="form-control" placeholder="Buscar por Folio, Raza o Nombre...">
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="selectAlcaldia">
                        <option value="">Todas las Alcaldías</option>
                        <option value="Iztapalapa">Iztapalapa</option>
                        <option value="Cuauhtémoc">Cuauhtémoc</option>
                        <option value="Coyoacán">Coyoacán</option>
                        <option value="Gustavo A. Madero">Gustavo A. Madero</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-guinda w-100" onclick="filtrarMascotas()"><i class="bi bi-funnel"></i> Aplicar Filtros</button>
                </div>
            </div>

            <!-- Grid de Tarjetas -->
            <div class="row g-4" id="gridMascotas">
                <?php
                $listaHome = is_array($mascotas ?? null) ? $mascotas : [];
                if (count($listaHome) === 0): ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="bi bi-search fs-1 d-block mb-2"></i>
                    No hay reportes activos en este momento.
                </div>
                <?php else:
                    foreach ($listaHome as $m):
                        $folio = htmlspecialchars((string)($m['folio'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $nombre = htmlspecialchars((string)($m['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $raza = htmlspecialchars((string)($m['raza'] ?? 'Sin especificar'), ENT_QUOTES, 'UTF-8');
                        $alc = htmlspecialchars((string)($m['alcaldia'] ?? 'CDMX'), ENT_QUOTES, 'UTF-8');
                        $fecha = htmlspecialchars((string)($m['fecha_extravio'] ?? '—'), ENT_QUOTES, 'UTF-8');
                        $est = htmlspecialchars((string)($m['estatus'] ?? 'En Búsqueda'), ENT_QUOTES, 'UTF-8');
                        $token = (string)($m['qr_token'] ?? '');
                        if ($token === '') { $token = (string)($m['folio'] ?? ''); }
                        $href = '/mascota/' . rawurlencode($token);
                        $especieM = (string)($m['especie'] ?? 'perro');
                        $foto = function_exists('mascota_foto_src')
                            ? mascota_foto_src(isset($m['foto_url']) ? (string)$m['foto_url'] : null, $especieM)
                            : (string)($m['foto_url'] ?? '/assets/img/placeholder-mascota.svg');
                        $fotoEsc = htmlspecialchars($foto, ENT_QUOTES, 'UTF-8');
                        $badge = 'bg-danger';
                        if ($est === 'Localizado') { $badge = 'bg-success'; }
                        elseif ($est === 'En Resguardo') { $badge = 'bg-warning text-dark'; }
                ?>
                <div class="col-md-4 reveal">
                    <div class="card card-mascota h-100">
                        <img src="<?= $fotoEsc ?>" class="card-img-top" alt="<?= $nombre ?>" style="height:220px;object-fit:cover;background:#f4f6f8;" onerror="this.onerror=null;this.src='/assets/img/placeholder-mascota.svg'">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge badge-guinda"><?= $folio ?></span>
                                <span class="badge <?= $badge ?>"><?= $est ?></span>
                            </div>
                            <h5 class="card-title fw-bold"><?= $nombre ?></h5>
                            <p class="card-text text-muted mb-1"><i class="bi bi-tag-fill text-danger"></i> Raza: <?= $raza ?></p>
                            <p class="card-text text-muted mb-1"><i class="bi bi-geo-alt-fill text-danger"></i> Alcaldía: <?= $alc ?></p>
                            <p class="card-text text-muted"><i class="bi bi-calendar-event"></i> Extraviado: <?= $fecha ?></p>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3">
                            <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-guinda w-100">
                                <i class="bi bi-eye"></i> Ver Ficha Completa
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </section>

        <!-- PREGUNTAS FRECUENTES (ACORDEÓN) -->
    <section id="faq" class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5 reveal">
                <h2 class="fw-bold" style="color: var(--guinda-principal);">Preguntas Frecuentes</h2>
                <p class="text-muted">Resuelve tus dudas sobre el registro y reporte en la plataforma RUPE</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-9 reveal">
                    <div class="accordion" id="accordionFAQ">
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    ¿Tiene algún costo registrar a mi mascota o generar la ficha?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-muted">
                                    No, la plataforma RUPE es un servicio completamente gratuito diseñado para apoyar a la ciudadanía en la pronta localización de sus mascotas.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    ¿Cómo funciona el código QR del reporte?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-muted">
                                    Al registrar un reporte de extravío, el sistema genera automáticamente un código QR único asociado al folio. Puedes imprimirlo en la ficha PDF para colocarlo en carteles o descargarlo para compartirlo en redes sociales.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    ¿Qué ocurre si alguien escanea el código QR de mi perro?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-muted">
                                    La persona podrá ver la ficha de búsqueda y enviar un formulario de avistamiento con la ubicación exacta (GPS) y fotografías. El sistema te enviará una notificación inmediata a tu correo electrónico.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FORMULARIO DE CONTACTO -->
    <section id="contacto" class="py-5 bg-light">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8 reveal">
                    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold" style="color: var(--guinda-principal);">Contacto y Soporte</h3>
                            <p class="text-muted">¿Tienes alguna duda o inconveniente técnico? Escríbenos.</p>
                        </div>
                        <form id="formContacto" onsubmit="enviarContacto(event)" novalidate>
                                <div id="contactoAlert" class="alert d-none py-2 small" role="alert"></div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="contactoNombre">Nombre completo *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="contactoNombre" name="nombre" required maxlength="80"
                                                   placeholder="Nombre y apellidos" autocomplete="name">
                                            <span class="input-group-text d-none text-success" id="okContactoNombre"><i class="bi bi-check-circle-fill"></i></span>
                                        </div>
                                        <div class="form-text">Nombre y apellidos (mín. 2 palabras, solo letras).</div>
                                        <div class="invalid-feedback">Incluye nombre y al menos un apellido (sin números).</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="contactoCorreo">Correo electrónico *</label>
                                        <div class="input-group">
                                            <input type="email" class="form-control" id="contactoCorreo" name="correo" required maxlength="80"
                                                   placeholder="correo@ejemplo.com" autocomplete="email">
                                            <span class="input-group-text d-none text-success" id="okContactoCorreo"><i class="bi bi-check-circle-fill"></i></span>
                                        </div>
                                        <div class="invalid-feedback">Correo electrónico no válido.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="contactoTelefono">Teléfono *</label>
                                        <div class="input-group">
                                            <input type="tel" class="form-control" id="contactoTelefono" name="telefono" required maxlength="10"
                                                   placeholder="5512345678" inputmode="numeric" pattern="[0-9]{10}" autocomplete="tel">
                                            <span class="input-group-text d-none text-success" id="okContactoTel"><i class="bi bi-check-circle-fill"></i></span>
                                        </div>
                                        <div class="form-text">Exactamente 10 dígitos (obligatorio).</div>
                                        <div class="invalid-feedback">El teléfono debe tener 10 dígitos.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="contactoAsunto">Asunto *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="contactoAsunto" name="asunto" required maxlength="150"
                                                   placeholder="Motivo del mensaje">
                                            <span class="input-group-text d-none text-success" id="okContactoAsunto"><i class="bi bi-check-circle-fill"></i></span>
                                        </div>
                                        <div class="form-text">Mínimo 3 caracteres.</div>
                                        <div class="invalid-feedback">Escribe un asunto (mín. 3 caracteres).</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold d-flex justify-content-between align-items-center" for="contactoMensaje">
                                            <span>Mensaje *</span>
                                            <span class="text-muted fw-normal small" id="contactoMensajeContador">0 / 100 palabras</span>
                                        </label>
                                        <div class="input-group">
                                            <textarea class="form-control" id="contactoMensaje" name="mensaje" rows="4" required
                                                      maxlength="1200" minlength="20"
                                                      placeholder="Escribe tu mensaje aquí (máximo 100 palabras)…"
                                                      oninput="if(window.actualizarContadorMensajeContacto)actualizarContadorMensajeContacto(); if(window.rupeValidarContacto)rupeValidarContacto(false);"></textarea>
                                            <span class="input-group-text d-none text-success align-items-start pt-2" id="okContactoMensaje"><i class="bi bi-check-circle-fill"></i></span>
                                        </div>
                                        <div class="form-text small">Mínimo 5 palabras · Máximo <strong>100 palabras</strong>.</div>
                                        <div class="form-text small text-danger d-none" id="contactoMensajeError"></div>
                                        <div class="invalid-feedback">El mensaje debe tener entre 5 y 100 palabras.</div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" id="btnContacto" class="btn btn-guinda btn-lg px-5">
                                            <i class="bi bi-send"></i> Enviar Mensaje
                                        </button>
                                        <p id="rupeRecaptchaNoteContacto" class="text-muted mt-2 mb-0" style="font-size:11px;">
                                            <img src="https://www.gstatic.com/recaptcha/api2/logo_48.png" alt="" width="18" height="18" class="me-1 align-text-bottom">
                                            Protegido por <strong>reCAPTCHA v3</strong> de Google
                                        </p>
                                    </div>
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER INSTITUCIONAL -->
    <!-- Aviso reCAPTCHA v3 (requerido por Google) -->
    <div class="container pb-2">
        <p class="text-center text-muted mb-0" style="font-size:11px;">
            Este sitio está protegido por reCAPTCHA v3 y se aplican la
            <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" class="text-muted">Política de Privacidad</a>
            y los
            <a href="https://policies.google.com/terms" target="_blank" rel="noopener" class="text-muted">Términos de Servicio</a>
            de Google.
        </p>
    </div>

    <footer class="footer-rupe">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3 text-uppercase"><i class="bi bi-patch-check"></i> RUPE CDMX</h5>
                    <p class="small text-white-50">Plataforma tecnológica para el Registro Único de Perros Extraviados. Uniendo tecnología, responsabilidad y comunidad.</p>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold mb-3">Navegación</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#inicio" class="text-white-50 text-decoration-none">Inicio</a></li>
                        <li><a href="/conocer" class="text-white-50 text-decoration-none">Conocer el Sistema</a></li>
                        <li><a href="/blog" class="text-white-50 text-decoration-none">Blog</a></li>
                        <li><a href="#busqueda" class="text-white-50 text-decoration-none">Buscar Perro</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-bold mb-3">Legales</h6>
                    <ul class="list-unstyled small">
                        <li><a href="/aviso-privacidad" class="text-white-50 text-decoration-none">Aviso de Privacidad</a></li>
                        <li><a href="/terminos-servicio" class="text-white-50 text-decoration-none">Términos del Servicio</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-bold mb-3">Redes y Difusión</h6>
                    <div class="d-flex gap-3 fs-5">
                        <a href="https://www.facebook.com/gobmexico" target="_blank" rel="noopener noreferrer" class="text-white" title="Facebook Gobierno de México"><i class="bi bi-facebook"></i></a>
                        <a href="https://x.com/GobiernoMX" target="_blank" rel="noopener noreferrer" class="text-white" title="X Gobierno de México"><i class="bi bi-twitter-x"></i></a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center small text-white-50">
                <p class="mb-0">&copy; 2026 RUPE CDMX. Todos los derechos reservados. Desarrollado para UnADM.</p>
            </div>
        </div>
    </footer>

    <!-- MODAL LOGIN -->
    <div class="modal fade" id="modalLogin" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0" style="background-color: var(--guinda-principal); color: white;">
                    <div class="d-flex align-items-center gap-2">
                        <img src="/assets/img/logo-unadm.png" alt="SEP · UnADM" style="height:28px;width:auto;object-fit:contain;background:#fff;border-radius:6px;padding:2px 6px;max-width:120px;" onerror="this.style.display='none'">
                        <h5 class="modal-header-title fw-bold mb-0"><i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formLogin" onsubmit="ejecutarLogin(event)">
                        <div id="loginAlert" class="alert d-none py-2 small" role="alert"></div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="loginCorreo">Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="loginCorreo" name="correo" required autocomplete="email"
                                   placeholder="usuario@correo.com" maxlength="120" minlength="6"
                                   pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}"
                                   title="Correo válido: letras, números, . _ % + - y dominio"
                                   oninput="if(window.filtrarCorreoLogin)filtrarCorreoLogin(this)">
                            <div class="form-text small text-muted">Solo caracteres de correo válidos (sin espacios ni acentos).</div>
                            <div class="form-text small text-danger d-none" id="loginCorreoError"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="loginPassword">Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                              <input type="password" class="form-control" id="loginPassword" name="password" required
                                     autocomplete="current-password" placeholder="Exacto 8: mayúscula, minúscula, número y especial"
                                     minlength="8" maxlength="8"
                                     title="Exactamente 8 caracteres: mayúscula, minúscula, número y especial">
                              <button type="button" class="btn btn-outline-secondary btn-toggle-pass" data-target="loginPassword" aria-label="Mostrar u ocultar contraseña" title="Mostrar / ocultar">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                              </button>
                            </div>
                            <div class="form-text small text-muted" id="loginPassHint">Exactamente 8 caracteres · mayúscula · minúscula · número · especial</div>
                            <div class="form-text small text-danger d-none" id="loginPasswordError"></div>
                        </div>
                        <div class="d-flex justify-content-between mb-3 small">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="checkRecordar">
                                <label class="form-check-label" for="checkRecordar">Recordarme</label>
                            </div>
                            <a href="/auth/forgot" class="text-decoration-none" style="color: var(--guinda-principal);">¿Olvidaste tu contraseña?</a>
                        </div>
                        <button type="submit" class="btn btn-guinda w-100 py-2">Ingresar al Sistema</button>
                        <p id="rupeRecaptchaNoteLogin" class="text-center text-muted mt-3 mb-0" style="font-size:11px;">
                            <img src="https://www.gstatic.com/recaptcha/api2/logo_48.png" alt="" width="18" height="18" class="me-1 align-text-bottom">
                            Protegido por <strong>reCAPTCHA v3</strong>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL REGISTRO -->
    <div class="modal fade" id="modalRegistro" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0" style="background-color: var(--guinda-principal); color: white;">
                    <div class="d-flex align-items-center gap-2">
                        <img src="/assets/img/logo-unadm.png" alt="SEP · UnADM" style="height:28px;width:auto;object-fit:contain;background:#fff;border-radius:6px;padding:2px 6px;max-width:120px;" onerror="this.style.display='none'">
                        <h5 class="modal-header-title fw-bold mb-0"><i class="bi bi-person-plus"></i> Crear Cuenta de Usuario</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formRegistro" onsubmit="ejecutarRegistro(event)">
                        <div id="registroAlert" class="alert d-none py-2 small" role="alert"></div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="regNombreCompleto">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="regNombreCompleto" name="nombre" required
                                   placeholder="Uno o dos nombres (ej. María o María Fernanda)"
                                   minlength="2" maxlength="60"
                                   pattern="[A-Za-zÁÉÍÓÚÜáéíóúüÑñ ]{2,60}"
                                   title="Uno o dos nombres, solo letras (sin números)"
                                   autocomplete="given-name"
                                   oninput="if(window.filtrarNombreRegistro)filtrarNombreRegistro(this,'regNombreError',2,60)">
                            <div class="form-text small d-flex justify-content-between">
                              <span class="reg-field-ok" id="regNombreOk"><i class="bi bi-check-circle-fill me-1"></i>Correcto</span>
                              <span class="text-muted">Uno o dos nombres · solo letras</span>
                            </div>
                            <div class="form-text small text-danger d-none" id="regNombreError"></div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold" for="regApellido1">Primer Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="regApellido1" name="apellido_paterno" required
                                       placeholder="Apellido Paterno" minlength="2" maxlength="50"
                                       pattern="[A-Za-zÁÉÍÓÚÜáéíóúüÑñ .'\-]{2,50}"
                                       title="Solo letras (2–50 caracteres)"
                                       autocomplete="family-name"
                                       oninput="if(window.filtrarNombreRegistro)filtrarNombreRegistro(this,'regApellido1Error',2,50)">
                                <div class="form-text small"><span class="reg-field-ok" id="regApellido1Ok"><i class="bi bi-check-circle-fill me-1"></i>Correcto</span></div>
                                <div class="form-text small text-danger d-none" id="regApellido1Error"></div>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold" for="regApellido2">Segundo Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="regApellido2" name="apellido_materno" required
                                       placeholder="Apellido Materno" minlength="2" maxlength="50"
                                       pattern="[A-Za-zÁÉÍÓÚÜáéíóúüÑñ .'\-]{2,50}"
                                       title="Solo letras (2–50 caracteres)"
                                       autocomplete="additional-name"
                                       oninput="if(window.filtrarNombreRegistro)filtrarNombreRegistro(this,'regApellido2Error',2,50)">
                                <div class="form-text small"><span class="reg-field-ok" id="regApellido2Ok"><i class="bi bi-check-circle-fill me-1"></i>Correcto</span></div>
                                <div class="form-text small text-danger d-none" id="regApellido2Error"></div>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="regCorreo">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="regCorreo" name="correo" required
                                       placeholder="correo@ejemplo.com" maxlength="120" minlength="6"
                                       pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}"
                                       title="Correo válido, máx. 120 caracteres"
                                       autocomplete="email"
                                       oninput="if(window.filtrarCorreoRegistro)filtrarCorreoRegistro(this)">
                                <div class="form-text small d-flex justify-content-between">
                                  <span class="reg-field-ok" id="regCorreoOk"><i class="bi bi-check-circle-fill me-1"></i>Correo válido</span>
                                  <span class="text-muted">Sin espacios ni acentos</span>
                                </div>
                                <div class="form-text small text-danger d-none" id="regCorreoError"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="regTelefono">Teléfono <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="regTelefono" name="telefono" required
                                       placeholder="5512345678" inputmode="numeric" maxlength="10" minlength="10"
                                       pattern="[0-9]{10}"
                                       title="Exactamente 10 dígitos (sin espacios ni guiones)"
                                       autocomplete="tel"
                                       oninput="if(window.filtrarTelefonoRegistro)filtrarTelefonoRegistro(this)">
                                <div class="form-text small d-flex justify-content-between">
                                  <span class="reg-field-ok" id="regTelefonoOk"><i class="bi bi-check-circle-fill me-1"></i>10 dígitos OK</span>
                                  <span class="text-muted">10 dígitos, solo números</span>
                                </div>
                                <div class="form-text small text-danger d-none" id="regTelefonoError"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="regPassword">Contraseña</label>
                            <div class="input-group">
                              <input type="password" class="form-control" name="password" id="regPassword" required minlength="8" maxlength="8"
                                   autocomplete="new-password" placeholder="Ej. Abcdef1!"
                                   title="Exactamente 8 caracteres: mayúscula, minúscula, número y especial"
                                   oninput="if(window.actualizarFuerzaPassRegistro)actualizarFuerzaPassRegistro()">
                              <button type="button" class="btn btn-outline-secondary btn-toggle-pass" data-target="regPassword" aria-label="Mostrar u ocultar contraseña" title="Mostrar / ocultar">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                              </button>
                            </div>
                            <div class="form-text small" id="regPasswordHint">Exactamente 8 caracteres: mayúscula, minúscula, número y un especial.</div>
                            <ul class="list-unstyled small mb-0 mt-1" id="regPasswordRules">
                                <li data-rule="len"><i class="bi bi-circle me-1 text-muted"></i>Exactamente 8 caracteres</li>
                                <li data-rule="upper"><i class="bi bi-circle me-1 text-muted"></i>Una mayúscula</li>
                                <li data-rule="lower"><i class="bi bi-circle me-1 text-muted"></i>Una minúscula</li>
                                <li data-rule="num"><i class="bi bi-circle me-1 text-muted"></i>Un número</li>
                                <li data-rule="spec"><i class="bi bi-circle me-1 text-muted"></i>Un carácter especial</li>
                            </ul>
                            <div class="form-text small text-danger d-none" id="regPasswordError"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="regPasswordConfirm">Confirmar contraseña</label>
                            <div class="input-group">
                              <input type="password" class="form-control" name="password_confirmation" id="regPasswordConfirm" required minlength="8" maxlength="8"
                                   autocomplete="new-password" placeholder="Repite la contraseña"
                                   oninput="if(window.actualizarConfirmPassRegistro)actualizarConfirmPassRegistro()">
                              <button type="button" class="btn btn-outline-secondary btn-toggle-pass" data-target="regPasswordConfirm" aria-label="Mostrar u ocultar contraseña" title="Mostrar / ocultar">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                              </button>
                            </div>
                            <div class="form-text small text-danger d-none" id="regPasswordConfirmError"></div>
                        </div>
                        <div class="form-check mb-3 small">
                            <input type="checkbox" class="form-check-input" id="checkPrivacidad" required>
                            <label class="form-check-label" for="checkPrivacidad">Acepto los <a href="/terminos-servicio" target="_blank">Términos</a> y el <a href="/aviso-privacidad" target="_blank">Aviso de Privacidad</a></label>
                        </div>
                        <button type="submit" class="btn btn-guinda w-100 py-2">Registrarme</button>
                        <p id="rupeRecaptchaNoteRegistro" class="text-center text-muted mt-3 mb-0" style="font-size:11px;">
                            <img src="https://www.gstatic.com/recaptcha/api2/logo_48.png" alt="" width="18" height="18" class="me-1 align-text-bottom">
                            Protegido por <strong>reCAPTCHA v3</strong>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- reCAPTCHA v3 (Google, gratis) — site key desde config.php -->
    <script>
      window.__RECAPTCHA_SITE_KEY__ = <?= json_encode($recaptchaSiteKey, JSON_UNESCAPED_UNICODE) ?>;
      window.__CSRF__ = '';
      window.__RECAPTCHA_READY__ = false;
    </script>
    <?php if ($recaptchaSiteKey !== ''): ?>
    <script src="/assets/js/recaptcha.js?v=20260926rc1"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars($recaptchaSiteKey, ENT_QUOTES, 'UTF-8') ?>" async defer></script>
    <?php endif; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="/assets/js/rupe-sanitize.js"></script>
  <script defer src="/assets/js/rupe-alerts.js"></script>

    <!-- Scripts ES6 Personalizados -->
    <script>
        // Efecto Navbar Transparente a Solido en Scroll
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar-rupe');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Animación de Revelado en Scroll (Intersection Observer API - ES6)
        const observerOptions = {
            root: null,
            threshold: 0.15
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // reCAPTCHA v3 helper (login | register | contacto)
        async function getRecaptchaToken(action) {
            const siteKey = window.__RECAPTCHA_SITE_KEY__ || '';
            if (!siteKey) {
                console.warn('[RUPE] reCAPTCHA site key no configurada');
                return '';
            }
            // Esperar a que el script de Google cargue (async/defer)
            const waitGrecaptcha = () => new Promise((resolve) => {
                if (typeof grecaptcha !== 'undefined' && grecaptcha.execute) {
                    resolve(true);
                    return;
                }
                let tries = 0;
                const t = setInterval(() => {
                    tries++;
                    if (typeof grecaptcha !== 'undefined' && grecaptcha.execute) {
                        clearInterval(t);
                        resolve(true);
                    } else if (tries > 40) {
                        clearInterval(t);
                        resolve(false);
                    }
                }, 100);
            });
            const ok = await waitGrecaptcha();
            if (!ok) {
                console.warn('[RUPE] reCAPTCHA no cargó a tiempo');
                return '';
            }
            return new Promise((resolve) => {
                try {
                    grecaptcha.ready(() => {
                        grecaptcha.execute(siteKey, { action: action || 'submit' })
                            .then((token) => {
                                window.__RECAPTCHA_READY__ = true;
                                resolve(token || '');
                            })
                            .catch((err) => {
                                console.warn('[RUPE] reCAPTCHA execute error', err);
                                resolve('');
                            });
                    });
                } catch (e) {
                    console.warn('[RUPE] reCAPTCHA exception', e);
                    resolve('');
                }
            });
        }


        /** Validación ES6 de contraseña fuerte (registro / cambio) */
        const validarPasswordFuerte = (pwd) => {
            const p = String(pwd || '');
            // Exactamente 8 caracteres + mayúscula + minúscula + número + especial
            const rules = [
                { ok: p.length === 8, msg: 'Exactamente 8 caracteres' },
                { ok: /[A-Z]/.test(p), msg: 'Mayúscula' },
                { ok: /[a-z]/.test(p), msg: 'Minúscula' },
                { ok: /\d/.test(p), msg: 'Número' },
                { ok: /[^A-Za-z0-9]/.test(p), msg: 'Carácter especial' },
            ];
            const fails = rules.filter(r => !r.ok).map(r => r.msg);
            return { ok: fails.length === 0, errors: fails, message: fails.length ? ('Falta: ' + fails.join(', ')) : '' };
        };
        window.validarPasswordFuerte = validarPasswordFuerte;

        const filtrarNombreRegistro = (el, errId, minLen, maxLen) => {
            if (!el) return;
            // Quitar números y símbolos; solo letras y espacios
            let v = String(el.value || '').replace(/[^A-Za-zÁÉÍÓÚÜáéíóúüÑñ .'\-]/g, '');
            v = v.replace(/\s+/g, ' ');
            if (maxLen > 0 && v.length > maxLen) v = v.slice(0, maxLen);
            if (v !== el.value) el.value = v;
            const err = errId ? document.getElementById(errId) : null;
            const okId = el.id === 'regNombreCompleto' ? 'regNombreOk'
                : (el.id === 'regApellido1' ? 'regApellido1Ok'
                : (el.id === 'regApellido2' ? 'regApellido2Ok' : null));
            const okEl = okId ? document.getElementById(okId) : null;
            if (!v && minLen === 0) {
                el.classList.remove('is-invalid', 'is-valid');
                if (err) { err.textContent = ''; err.classList.add('d-none'); }
                if (okEl) okEl.style.display = 'none';
                return;
            }
            const esNombre = (el.id === 'regNombreCompleto');
            const r = validarNombrePersona(v, Math.max(minLen, 1), maxLen || 60, { nombres: esNombre });
            el.classList.toggle('is-invalid', v.length > 0 && !r.ok);
            el.classList.toggle('is-valid', r.ok);
            if (okEl) okEl.style.display = r.ok ? 'inline-block' : 'none';
            if (err) {
                if (v.length > 0 && !r.ok) { err.textContent = r.msg; err.classList.remove('d-none'); }
                else { err.textContent = ''; err.classList.add('d-none'); }
            }
        };
        window.filtrarNombreRegistro = filtrarNombreRegistro;

        const filtrarCorreoRegistro = (el) => {
            if (!el) return;
            let v = String(el.value || '').replace(/[^A-Za-z0-9._%+\-@]/g, '');
            const at = v.indexOf('@');
            if (at >= 0) v = v.slice(0, at + 1) + v.slice(at + 1).replace(/@/g, '');
            if (v.length > 120) v = v.slice(0, 120);
            if (v !== el.value) el.value = v;
            const r = validarCorreoAuth(v);
            el.classList.toggle('is-invalid', v.length > 0 && !r.ok);
            el.classList.toggle('is-valid', r.ok);
            const okC = document.getElementById('regCorreoOk');
            if (okC) okC.style.display = r.ok ? 'inline-block' : 'none';
            const err = document.getElementById('regCorreoError');
            if (err) {
                if (v.length > 0 && !r.ok) { err.textContent = r.msg; err.classList.remove('d-none'); }
                else { err.textContent = ''; err.classList.add('d-none'); }
            }
        };
        window.filtrarCorreoRegistro = filtrarCorreoRegistro;

        const validarTelefonoMx = (v) => {
            const d = String(v || '').replace(/\D/g, '');
            if (!d) return { ok: false, msg: 'El teléfono es obligatorio.' };
            if (d.length !== 10) return { ok: false, msg: 'El teléfono debe tener exactamente 10 dígitos.' };
            return { ok: true, value: d };
        };
        window.validarTelefonoMx = validarTelefonoMx;

        const filtrarTelefonoRegistro = (el) => {
            if (!el) return;
            let d = String(el.value || '').replace(/\D/g, '').slice(0, 10);
            if (d !== el.value) el.value = d;
            const r = validarTelefonoMx(d);
            el.classList.toggle('is-invalid', d.length > 0 && !r.ok);
            el.classList.toggle('is-valid', r.ok);
            const okT = document.getElementById('regTelefonoOk');
            if (okT) okT.style.display = r.ok ? 'inline-block' : 'none';
            const err = document.getElementById('regTelefonoError');
            if (err) {
                if (d.length > 0 && !r.ok) { err.textContent = r.msg; err.classList.remove('d-none'); }
                else { err.textContent = ''; err.classList.add('d-none'); }
            }
        };
        window.filtrarTelefonoRegistro = filtrarTelefonoRegistro;


        const actualizarFuerzaPassRegistro = () => {
            const el = document.getElementById('regPassword');
            const ul = document.getElementById('regPasswordRules');
            const err = document.getElementById('regPasswordError');
            if (!el) return;
            const p = el.value || '';
            const rules = {
                len: p.length === 8,
                upper: /[A-Z]/.test(p),
                lower: /[a-z]/.test(p),
                num: /\d/.test(p),
                spec: /[^A-Za-z0-9]/.test(p)
            };
            if (ul) {
                ul.querySelectorAll('[data-rule]').forEach((li) => {
                    const k = li.getAttribute('data-rule');
                    const ok = !!rules[k];
                    const icon = li.querySelector('i');
                    if (icon) icon.className = ok ? 'bi bi-check-circle-fill text-success me-1' : 'bi bi-circle me-1 text-muted';
                    li.classList.toggle('text-success', ok);
                    li.classList.toggle('text-muted', !ok);
                });
            }
            const r = validarPasswordFuerte(p);
            el.classList.toggle('is-invalid', p.length > 0 && !r.ok);
            el.classList.toggle('is-valid', r.ok);
            if (err) {
                if (p.length > 0 && !r.ok) { err.textContent = r.message; err.classList.remove('d-none'); }
                else { err.textContent = ''; err.classList.add('d-none'); }
            }
            if (typeof actualizarConfirmPassRegistro === 'function') actualizarConfirmPassRegistro();
        };
        window.actualizarFuerzaPassRegistro = actualizarFuerzaPassRegistro;

        const actualizarConfirmPassRegistro = () => {
            const a = document.getElementById('regPassword');
            const b = document.getElementById('regPasswordConfirm');
            const err = document.getElementById('regPasswordConfirmError');
            if (!b) return;
            const va = (a && a.value) || '';
            const vb = b.value || '';
            if (!vb) {
                b.classList.remove('is-invalid', 'is-valid');
                if (err) { err.textContent = ''; err.classList.add('d-none'); }
                return;
            }
            const ok = va === vb && validarPasswordFuerte(va).ok;
            b.classList.toggle('is-invalid', !ok);
            b.classList.toggle('is-valid', ok);
            if (err) {
                if (!ok) {
                    err.textContent = va !== vb ? 'La confirmación no coincide.' : 'La contraseña aún no cumple las reglas.';
                    err.classList.remove('d-none');
                } else {
                    err.textContent = '';
                    err.classList.add('d-none');
                }
            }
        };
        window.actualizarConfirmPassRegistro = actualizarConfirmPassRegistro;



        const RE_NOMBRE = /^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ .'\-]{2,60}$/;
        const RE_APELLIDO = /^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ .'\-]{2,50}$/;
        const RE_EMAIL = /^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/;

        const validarNombrePersona = (v, min = 2, max = 60, opts = {}) => {
            const s = String(v || '').trim().replace(/\s+/g, ' ');
            if (s.length < min || s.length > max) return { ok: false, msg: `Debe tener entre ${min} y ${max} caracteres.` };
            // Sin números
            if (/\d/.test(s)) return { ok: false, msg: 'No se permiten números.' };
            if (!/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ .'\-]+$/.test(s)) return { ok: false, msg: 'Solo letras y espacios (sin números ni símbolos raros).' };
            // Nombre completo: uno o dos nombres (palabras)
            if (opts.nombres === true) {
                const parts = s.split(' ').filter(Boolean);
                if (parts.length < 1 || parts.length > 2) {
                    return { ok: false, msg: 'Escribe un nombre o dos nombres (ej. María o María Fernanda).' };
                }
                for (const p of parts) {
                    if (p.length < 2) return { ok: false, msg: 'Cada nombre debe tener al menos 2 letras.' };
                    if (!/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ]+$/.test(p)) {
                        return { ok: false, msg: 'Solo letras en cada nombre (sin números).' };
                    }
                }
            }
            return { ok: true };
        };
        const validarCorreoAuth = (v) => {
            const s = String(v || '').trim().toLowerCase();
            if (!s) return { ok: false, msg: 'El correo es obligatorio.' };
            if (s.length < 6) return { ok: false, msg: 'Correo demasiado corto (mín. 6 caracteres).' };
            if (s.length > 120) return { ok: false, msg: 'Correo máximo 120 caracteres.' };
            if (/\s/.test(s)) return { ok: false, msg: 'El correo no debe contener espacios.' };
            if (/[ÁÉÍÓÚÜáéíóúüÑñ]/.test(s)) return { ok: false, msg: 'El correo no debe llevar acentos ni ñ.' };
            if (!RE_EMAIL.test(s)) return { ok: false, msg: 'Formato de correo no válido (ej. usuario@dominio.com).' };
            const parts = s.split('@');
            if (parts.length !== 2 || !parts[0] || !parts[1] || !parts[1].includes('.')) {
                return { ok: false, msg: 'Correo incompleto: falta usuario o dominio.' };
            }
            return { ok: true };
        };
        const filtrarCorreoLogin = (el) => {
            if (!el) return;
            // Solo caracteres permitidos en email
            let v = String(el.value || '').replace(/[^A-Za-z0-9._%+\-@]/g, '');
            // Un solo @
            const at = v.indexOf('@');
            if (at >= 0) {
                v = v.slice(0, at + 1) + v.slice(at + 1).replace(/@/g, '');
            }
            if (v.length > 120) v = v.slice(0, 120);
            if (v !== el.value) el.value = v;
            const r = validarCorreoAuth(v);
            el.classList.toggle('is-invalid', v.length > 0 && !r.ok);
            el.classList.toggle('is-valid', r.ok);
            const err = document.getElementById('loginCorreoError');
            if (err) {
                if (v.length > 0 && !r.ok) { err.textContent = r.msg; err.classList.remove('d-none'); }
                else { err.textContent = ''; err.classList.add('d-none'); }
            }
        };
        window.filtrarCorreoLogin = filtrarCorreoLogin;

        const validarPasswordLogin = (pwd) => {
            return validarPasswordFuerte(pwd);
        };
        window.validarPasswordLogin = validarPasswordLogin;

        const actualizarHintPassLogin = () => {
            const el = document.getElementById('loginPassword');
            const err = document.getElementById('loginPasswordError');
            if (!el) return;
            const p = el.value || '';
            const r = validarPasswordFuerte(p);
            el.classList.toggle('is-invalid', p.length > 0 && !r.ok);
            el.classList.toggle('is-valid', r.ok);
            if (err) {
                if (p.length > 0 && !r.ok) { err.textContent = r.message || r.errors.join(' '); err.classList.remove('d-none'); }
                else { err.textContent = ''; err.classList.add('d-none'); }
            }
        };
        window.actualizarHintPassLogin = actualizarHintPassLogin;
        const filtrarSoloLetras = (el) => {
            if (!el || el._rupeLetras) return;
            el._rupeLetras = true;
            el.addEventListener('input', () => {
                const cleaned = el.value.replace(/[^A-Za-zÁÉÍÓÚÜáéíóúüÑñ .'\-]/g, '');
                if (cleaned !== el.value) el.value = cleaned;
                if (el.maxLength > 0 && el.value.length > el.maxLength) el.value = el.value.slice(0, el.maxLength);
            });
        };
        window.validarNombrePersona = validarNombrePersona;
        window.validarCorreoAuth = validarCorreoAuth;


        async function ensureCsrf() {
            if (window.__CSRF__) return window.__CSRF__;
            try {
                const r = await fetch('/api/csrf', { credentials: 'same-origin' });
                const d = await r.json();
                window.__CSRF__ = d.data?.csrf || d.csrf || '';
            } catch (_) {}
            return window.__CSRF__ || '';
        }

        // Login real con reCAPTCHA v3
        const ejecutarLogin = async (e) => {
            e.preventDefault();
            const form = e.target;
            const alertEl = document.getElementById('loginAlert');
            const correo = (form.querySelector('[name="correo"]')?.value
                || document.getElementById('loginCorreo')?.value || '').trim().toLowerCase();
            const password = (form.querySelector('[name="password"]')?.value
                || document.getElementById('loginPassword')?.value || '').trim();
            if (alertEl) { alertEl.classList.add('d-none'); }
            const loginCorreoErr = document.getElementById('loginCorreoError');
            if (loginCorreoErr) { loginCorreoErr.classList.add('d-none'); loginCorreoErr.textContent = ''; }

            if (!correo || !password) {
                if (typeof rupeFormAlert === 'function') rupeFormAlert(alertEl || 'loginAlert', 'Ingresa correo y contraseña.', 'warning');
                else if (alertEl) {
                    alertEl.className = 'alert alert-warning py-2 small';
                    alertEl.textContent = 'Ingresa correo y contraseña.';
                    alertEl.classList.remove('d-none');
                }
                return;
            }
            const vcLogin = validarCorreoAuth(correo);
            if (!vcLogin.ok) {
                if (loginCorreoErr) { loginCorreoErr.textContent = vcLogin.msg; loginCorreoErr.classList.remove('d-none'); }
                document.getElementById('loginCorreo')?.classList.add('is-invalid');
                if (typeof rupeFormAlert === 'function') rupeFormAlert(alertEl || 'loginAlert', vcLogin.msg, 'warning');
                else if (alertEl) {
                    alertEl.className = 'alert alert-warning py-2 small';
                    alertEl.textContent = vcLogin.msg;
                    alertEl.classList.remove('d-none');
                }
                return;
            }
            document.getElementById('loginCorreo')?.classList.remove('is-invalid');
            document.getElementById('loginCorreo')?.classList.add('is-valid');

            // Contraseña: mínimo 8, mayúscula, minúscula, número y carácter especial
            const loginPassErr = document.getElementById('loginPasswordError');
            if (loginPassErr) { loginPassErr.classList.add('d-none'); loginPassErr.textContent = ''; }
            const vpLogin = (typeof validarPasswordFuerte === 'function')
                ? validarPasswordFuerte(password)
                : { ok: password.length === 8, message: 'Exactamente 8 caracteres.' };
            if (!vpLogin.ok) {
                const msgP = 'Contraseña: ' + (vpLogin.message || (vpLogin.errors || []).join(' ') || 'mín. 8 caracteres con letras, números y un especial.');
                if (loginPassErr) { loginPassErr.textContent = msgP; loginPassErr.classList.remove('d-none'); }
                document.getElementById('loginPassword')?.classList.add('is-invalid');
                if (typeof rupeFormAlert === 'function') rupeFormAlert(alertEl || 'loginAlert', msgP, 'warning');
                else if (alertEl) {
                    alertEl.className = 'alert alert-warning py-2 small';
                    alertEl.textContent = msgP;
                    alertEl.classList.remove('d-none');
                }
                return;
            }
            document.getElementById('loginPassword')?.classList.remove('is-invalid');
            document.getElementById('loginPassword')?.classList.add('is-valid');

            const btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
            try {
                const csrf = await ensureCsrf();
                const recaptcha_token = await getRecaptchaToken('login');
                const res = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': csrf
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ correo, password, recaptcha_token, _csrf: csrf })
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || data.success === false) {
                    const code = data.code
                        || (data.errors && data.errors.code)
                        || (data.data && data.data.code)
                        || '';
                    const msgRaw = String(data.message || '');
                    const isInactive = res.status === 403
                        || code === 'cuenta_inactiva'
                        || /inactiva/i.test(msgRaw)
                        || /no puedes iniciar sesi/i.test(msgRaw);
                    const msgErr = isInactive
                        ? (msgRaw || 'Tu cuenta está inactiva. No puedes iniciar sesión. Contacta al administrador de RUPE CDMX.')
                        : (msgRaw || 'Credenciales incorrectas.');
                    const alertType = isInactive ? 'warning' : 'danger';
                    // Alerta visible en el formulario de login
                    if (alertEl) {
                        alertEl.className = 'alert alert-' + alertType + ' py-2 small';
                        alertEl.innerHTML = isInactive
                            ? '<i class="bi bi-exclamation-triangle me-1"></i><strong>Cuenta inactiva.</strong> ' + msgErr.replace(/^Tu cuenta está inactiva\.\s*/i, '')
                            : msgErr;
                        alertEl.classList.remove('d-none');
                    }
                    if (typeof rupeFormAlert === 'function') {
                        try { rupeFormAlert(alertEl || 'loginAlert', msgErr, alertType); } catch (_) {}
                    }
                    if (typeof rupeToast === 'function') {
                        rupeToast(isInactive ? 'Cuenta inactiva: no puedes iniciar sesión.' : msgErr, alertType === 'warning' ? 'warning' : 'danger', 5000);
                    }
                    return;
                }
                if (data.data?.csrf) window.__CSRF__ = data.data.csrf;
                const rol = data.data?.user?.rol || '';
                const estado = data.data?.estado || (data.data?.activo == 1 ? 'Activo' : '');
                const msgOk = data.message || 'Sesión iniciada. Tu cuenta está activa.';
                if (typeof rupeFormAlert === 'function') {
                    rupeFormAlert(alertEl || 'loginAlert', msgOk, 'success');
                } else if (alertEl) {
                    alertEl.className = 'alert alert-success py-2 small';
                    alertEl.textContent = msgOk;
                    alertEl.classList.remove('d-none');
                }
                if (typeof rupeToast === 'function') {
                    rupeToast(msgOk + (estado ? ' (' + estado + ')' : ''), 'success', 2800);
                }
                if (rol === 'administrador') {
                    window.location.href = '/admin';
                } else {
                    window.location.href = '/tutor';
                }
            } catch (err) {
                if (alertEl) {
                    alertEl.className = 'alert alert-danger py-2 small';
                    alertEl.textContent = 'Error de red. Intenta de nuevo.';
                    alertEl.classList.remove('d-none');
                } else {
                    if (typeof rupeToast === 'function') rupeToast('Error de red. Intenta de nuevo.', 'danger');
                }
            } finally {
                if (btn) btn.disabled = false;
            }
        };

        // Registro real con reCAPTCHA v3
        const ejecutarRegistro = async (e) => {
            e.preventDefault();
            if (typeof rupeHideFormAlert === 'function') rupeHideFormAlert('registroAlert');
            const form = e.target;
            const nombreSolo = (document.getElementById('regNombreCompleto')?.value
                || form.querySelector('[name="nombre"]')?.value || '').trim();
            const apellido1 = (document.getElementById('regApellido1')?.value
                || form.querySelector('[name="apellido_paterno"]')?.value || '').trim();
            const apellido2 = (document.getElementById('regApellido2')?.value
                || form.querySelector('[name="apellido_materno"]')?.value || '').trim();
            // Nombre completo = nombre + apellidos (como se guarda en BD)
            const nombre = [nombreSolo, apellido1, apellido2].filter(Boolean).join(' ').replace(/\s+/g, ' ').trim();
            const correo = (document.getElementById('regCorreo')?.value
                || form.querySelector('[name="correo"]')?.value
                || form.querySelector('input[type="email"]')?.value || '').trim().toLowerCase();
            const telefono = (document.getElementById('regTelefono')?.value
                || form.querySelector('[name="telefono"]')?.value || '').replace(/\D/g, '').trim();
            const passwords = form.querySelectorAll('input[type="password"]');
            const password = passwords[0]?.value || '';
            const password_confirmation = passwords[1]?.value || password;
            const acepta = form.querySelector('#checkPrivacidad')?.checked
                || form.querySelector('[name="acepta_privacidad"]')?.checked || false;

            // Validaciones de caracteres
            const showErr = (id, msg) => {
                const el = document.getElementById(id);
                if (el) { el.textContent = msg || ''; el.classList.toggle('d-none', !msg); }
            };
            showErr('regNombreError', '');
            showErr('regApellido1Error', '');
            showErr('regApellido2Error', '');
            showErr('regCorreoError', '');
            showErr('regTelefonoError', '');

            const vn = validarNombrePersona(nombreSolo, 2, 60, { nombres: true });
            if (!vn.ok) {
                showErr('regNombreError', vn.msg);
                document.getElementById('regNombreCompleto')?.classList.add('is-invalid');
                if (typeof rupeFormAlert === 'function') rupeFormAlert('registroAlert', 'Nombre completo: ' + vn.msg, 'warning');
                else if (typeof rupeToast === 'function') rupeToast('Nombre completo: ' + vn.msg, 'warning');
                return;
            }
            document.getElementById('regNombreCompleto')?.classList.remove('is-invalid');
            document.getElementById('regNombreCompleto')?.classList.add('is-valid');

            const va1 = validarNombrePersona(apellido1, 2, 50);
            if (!va1.ok) {
                showErr('regApellido1Error', va1.msg);
                document.getElementById('regApellido1')?.classList.add('is-invalid');
                if (typeof rupeFormAlert === 'function') rupeFormAlert('registroAlert', 'Primer apellido: ' + va1.msg, 'warning');
                else if (typeof rupeToast === 'function') rupeToast('Primer apellido: ' + va1.msg, 'warning');
                return;
            }
            document.getElementById('regApellido1')?.classList.remove('is-invalid');
            document.getElementById('regApellido1')?.classList.add('is-valid');

            if (!apellido2) {
                showErr('regApellido2Error', 'El segundo apellido es obligatorio.');
                document.getElementById('regApellido2')?.classList.add('is-invalid');
                if (typeof rupeFormAlert === 'function') rupeFormAlert('registroAlert', 'Completa el segundo apellido (máx. 50 caracteres).', 'warning');
                else if (typeof rupeToast === 'function') rupeToast('Completa el segundo apellido.', 'warning');
                return;
            }
            const va2 = validarNombrePersona(apellido2, 2, 50);
            if (!va2.ok) {
                showErr('regApellido2Error', va2.msg);
                document.getElementById('regApellido2')?.classList.add('is-invalid');
                if (typeof rupeFormAlert === 'function') rupeFormAlert('registroAlert', 'Segundo apellido: ' + va2.msg, 'warning');
                else if (typeof rupeToast === 'function') rupeToast('Segundo apellido: ' + va2.msg, 'warning');
                return;
            }
            document.getElementById('regApellido2')?.classList.remove('is-invalid');
            document.getElementById('regApellido2')?.classList.add('is-valid');

            const vc = validarCorreoAuth(correo);
            if (!vc.ok) {
                showErr('regCorreoError', vc.msg);
                document.getElementById('regCorreo')?.classList.add('is-invalid');
                if (typeof rupeFormAlert === 'function') rupeFormAlert('registroAlert', vc.msg, 'warning');
                else if (typeof rupeToast === 'function') rupeToast(vc.msg, 'warning');
                return;
            }
            document.getElementById('regCorreo')?.classList.remove('is-invalid');
            document.getElementById('regCorreo')?.classList.add('is-valid');

            const vt = (typeof validarTelefonoMx === 'function')
                ? validarTelefonoMx(telefono)
                : { ok: /^\d{10}$/.test(telefono), msg: 'El teléfono debe tener 10 dígitos.' };
            if (!vt.ok) {
                showErr('regTelefonoError', vt.msg || 'Teléfono inválido (10 dígitos).');
                document.getElementById('regTelefono')?.classList.add('is-invalid');
                if (typeof rupeFormAlert === 'function') rupeFormAlert('registroAlert', vt.msg || 'Teléfono: 10 dígitos.', 'warning');
                else if (typeof rupeToast === 'function') rupeToast(vt.msg || 'Teléfono inválido.', 'warning');
                return;
            }
            document.getElementById('regTelefono')?.classList.remove('is-invalid');
            document.getElementById('regTelefono')?.classList.add('is-valid');
            showErr('regTelefonoError', '');

            if (!password) {
                if (typeof rupeFormAlert === 'function') rupeFormAlert('registroAlert', 'La contraseña es obligatoria.', 'warning');
                else if (typeof rupeToast === 'function') rupeToast('La contraseña es obligatoria.', 'warning');
                return;
            }
            const checkPwd = validarPasswordFuerte(password);
            if (!checkPwd.ok) {
                const errEl = document.getElementById('regPasswordError');
                if (errEl) {
                    errEl.textContent = checkPwd.message;
                    errEl.classList.remove('d-none');
                }
                document.getElementById('regPassword')?.classList.add('is-invalid');
                document.getElementById('regPassword')?.classList.remove('is-valid');
                if (typeof rupeFormAlert === 'function') rupeFormAlert('registroAlert', checkPwd.message, 'warning');
                else if (typeof rupeToast === 'function') rupeToast(checkPwd.message, 'warning');
                return;
            } else {
                const errEl = document.getElementById('regPasswordError');
                if (errEl) { errEl.textContent = ''; errEl.classList.add('d-none'); }
                document.getElementById('regPassword')?.classList.remove('is-invalid');
                document.getElementById('regPassword')?.classList.add('is-valid');
            }
            if (!password_confirmation || password !== password_confirmation) {
                const errC = document.getElementById('regPasswordConfirmError');
                if (errC) { errC.textContent = 'La confirmación de contraseña no coincide.'; errC.classList.remove('d-none'); }
                document.getElementById('regPasswordConfirm')?.classList.add('is-invalid');
                if (typeof rupeFormAlert === 'function') rupeFormAlert('registroAlert', 'La confirmación de contraseña no coincide.', 'warning');
                else if (typeof rupeToast === 'function') rupeToast('La confirmación de contraseña no coincide.', 'warning');
                return;
            }
            document.getElementById('regPasswordConfirm')?.classList.remove('is-invalid');
            document.getElementById('regPasswordConfirm')?.classList.add('is-valid');
            const errCok = document.getElementById('regPasswordConfirmError');
            if (errCok) { errCok.textContent = ''; errCok.classList.add('d-none'); }
            if (!acepta) {
                if (typeof rupeFormAlert === 'function') rupeFormAlert('registroAlert', 'Debes aceptar el Aviso de Privacidad.', 'warning'); else if (typeof rupeToast === 'function') rupeToast('Debes aceptar el Aviso de Privacidad.', 'warning');
                return;
            }

            const btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
            try {
                const csrf = await ensureCsrf();
                const recaptcha_token = await getRecaptchaToken('register');
                const res = await fetch('/api/auth/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': csrf
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        nombre, correo, telefono, password, password_confirmation,
                        acepta_privacidad: acepta ? 1 : 0,
                        recaptcha_token, _csrf: csrf
                    })
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || data.success === false) {
                    let msg = data.message || 'No se pudo crear la cuenta.';
                    if (data.errors) {
                        msg = Object.values(data.errors).flat().join(' ');
                    }
                    if (typeof rupeFormAlert === 'function') rupeFormAlert('registroAlert', msg, 'danger');
                    else if (typeof rupeToast === 'function') rupeToast(msg, 'danger');
                    return;
                }
                if (data.data?.csrf) window.__CSRF__ = data.data.csrf;
                if (typeof rupeFormAlert === 'function') rupeFormAlert('registroAlert', 'Cuenta creada correctamente. Redirigiendo…', 'success');
                else if (typeof rupeToast === 'function') rupeToast('Cuenta creada correctamente. Redirigiendo…', 'success', 2500);
                setTimeout(() => { window.location.href = '/tutor'; }, 600);
            } catch (err) {
                if (typeof rupeToast === 'function') rupeToast('Error de red. Intenta de nuevo.', 'danger');
            } finally {
                if (btn) btn.disabled = false;
            }
        };

        // ---- Catálogo alcaldías (select) ----
        async function cargarAlcaldiasHome() {
            const sel = document.getElementById('selectAlcaldia');
            if (!sel) return;
            try {
                const res = await fetch('/api/public/alcaldias', { credentials: 'same-origin' });
                const data = await res.json();
                const list = data.data || data || [];
                if (!Array.isArray(list)) return;
                const current = sel.value;
                sel.innerHTML = '<option value="">Todas las Alcaldías</option>';
                list.forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a.id_alcaldia;
                    opt.textContent = a.nombre;
                    sel.appendChild(opt);
                });
                if (current) sel.value = current;
            } catch (e) {
                console.warn('No se pudieron cargar alcaldías', e);
            }
        }

        function badgeEstatus(estatus) {
            if (estatus === 'Localizado') return 'bg-success';
            if (estatus === 'En Resguardo') return 'bg-info text-dark';
            return 'bg-danger';
        }

        function renderMascotasCards(mascotas) {
            const grid = document.getElementById('gridMascotas');
            if (!grid) return;
            if (!mascotas || mascotas.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="bi bi-search fs-1 d-block mb-2"></i>No se encontraron mascotas con esos filtros.</div>';
                return;
            }
            grid.innerHTML = mascotas.map(m => {
                const folio = m.folio || '';
                const token = m.qr_token || m.folio || '';
                const href = '/mascota/' + encodeURIComponent(token);
                let foto = (m.foto_url && String(m.foto_url) !== 'null' && String(m.foto_url).trim() !== '')
                    ? String(m.foto_url)
                    : '';
                if (!foto) {
                  const esp = String(m.especie || 'perro').toLowerCase();
                  foto = esp.indexOf('gato') >= 0 ? '/assets/img/placeholder-gato.svg' : '/assets/img/placeholder-perro.svg';
                }
                const raza = m.raza || 'Mestizo';
                const alc = m.alcaldia || 'CDMX';
                const fecha = m.fecha_extravio || '—';
                const est = m.estatus || 'En Búsqueda';
                let badge = 'bg-danger';
                if (est === 'Localizado') badge = 'bg-success';
                else if (est === 'En Resguardo') badge = 'bg-warning text-dark';
                const imgHtml = `<img src="${foto}" class="card-img-top" alt="${m.nombre || ''}" style="height:220px;object-fit:cover;background:#f4f6f8;" onerror="this.onerror=null;this.src='/assets/img/placeholder-mascota.svg'">`;
                return `
                <div class="col-md-4 reveal active">
                    <div class="card card-mascota h-100">
                        ${imgHtml}
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge badge-guinda">${folio}</span>
                                <span class="badge ${badge}">${est}</span>
                            </div>
                            <h5 class="card-title fw-bold">${m.nombre || ''}</h5>
                            <p class="card-text text-muted mb-1"><i class="bi bi-tag-fill text-danger"></i> Raza: ${raza}</p>
                            <p class="card-text text-muted mb-1"><i class="bi bi-geo-alt-fill text-danger"></i> ${alc}</p>
                            <p class="card-text text-muted mb-0"><i class="bi bi-calendar3 text-danger"></i> ${fecha}</p>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3">
                            <a href="${href}" class="btn btn-outline-guinda w-100"><i class="bi bi-eye"></i> Ver Ficha Completa</a>
                        </div>
                    </div>
                </div>`;
            }).join('');
        }

        async function verFichaPublica(folio) {
            try {
                const res = await fetch('/api/public/mascota/' + encodeURIComponent(folio), { credentials: 'same-origin' });
                const data = await res.json();
                if (!data.success) { if (typeof rupeToast === 'function') rupeToast(data.message || 'No encontrado', 'warning'); else alert(data.message || 'No encontrado'); return; }
                const m = data.data;
                if (typeof rupeToast === 'function') {
                    rupeToast('Folio ' + (m.folio || '') + ' · ' + (m.nombre || '') + ' · ' + (m.estatus || '') + (m.alcaldia ? ' · ' + m.alcaldia : ''), 'info', 6000);
                } else {
                    alert('Folio: ' + m.folio + '\nNombre: ' + m.nombre);
                }
            } catch (e) {
                if (typeof rupeToast === 'function') rupeToast('No se pudo cargar la ficha.', 'danger');
            }
        }

        const filtrarMascotas = async () => {
            const q = (document.getElementById('inputBuscar')?.value || '').trim();
            const idAlcaldia = document.getElementById('selectAlcaldia')?.value || '';
            const params = new URLSearchParams();
            if (q) params.set('q', q);
            if (idAlcaldia) params.set('id_alcaldia', idAlcaldia);
            params.set('limit', '12');
            // Si hay búsqueda libre, no forzar estatus
            if (!q) params.set('estatus', 'En Búsqueda');

            const grid = document.getElementById('gridMascotas');
            if (grid) {
                grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-danger" role="status"></div><p class="mt-2 text-muted">Buscando…</p></div>';
            }
            try {
                const res = await fetch('/api/public/mascotas?' + params.toString(), { credentials: 'same-origin' });
                const data = await res.json();
                const list = data.data?.mascotas || data.mascotas || [];
                renderMascotasCards(list);
            } catch (e) {
                if (grid) {
                    grid.innerHTML = '<div class="col-12 text-center text-danger py-4">Error al cargar resultados. Intenta de nuevo.</div>';
                }
            }
        };


        function contarPalabrasContacto(texto) {
            const s = String(texto || '').trim();
            if (!s) return 0;
            return s.split(/\s+/).filter(Boolean).length;
        }
        function actualizarContadorMensajeContacto() {
            const el = document.getElementById('contactoMensaje');
            const cont = document.getElementById('contactoMensajeContador');
            const err = document.getElementById('contactoMensajeError');
            if (!el) return;
            let n = contarPalabrasContacto(el.value);
            if (n > 100) {
                const words = el.value.trim().split(/\s+/).filter(Boolean).slice(0, 100);
                el.value = words.join(' ');
                n = 100;
            }
            if (cont) {
                cont.textContent = n + ' / 100 palabras';
                cont.classList.toggle('text-danger', n >= 100);
                cont.classList.toggle('text-success', n >= 5 && n < 100);
                cont.classList.toggle('text-muted', n < 5);
            }
            if (err && n <= 100) {
                err.classList.add('d-none');
                err.textContent = '';
            }
            el.classList.toggle('is-invalid', n > 0 && (n < 5 || n > 100));
            el.classList.toggle('is-valid', n >= 5 && n <= 100);
        }
        window.contarPalabrasContacto = contarPalabrasContacto;
        window.actualizarContadorMensajeContacto = actualizarContadorMensajeContacto;

        const enviarContacto = async (e) => {
            e.preventDefault();
            const form = e.target;
            const alertEl = document.getElementById('contactoAlert');
            const btn = document.getElementById('btnContacto') || form.querySelector('button[type="submit"]');
            const showAlert = (msg, tipo) => {
                if (alertEl) {
                    alertEl.className = 'alert alert-' + (tipo || 'danger') + ' py-2 small';
                    alertEl.innerHTML = msg;
                    alertEl.classList.remove('d-none');
                } else if (typeof rupeToast === 'function') {
                    rupeToast(msg.replace(/<[^>]+>/g, ''), tipo || 'danger');
                } else {
                    alert(msg.replace(/<[^>]+>/g, ''));
                }
            };
            if (typeof window.rupeValidarContacto === 'function' && !window.rupeValidarContacto(true)) {
                showAlert('<i class="bi bi-exclamation-triangle me-1"></i>Completa nombre con apellidos, correo, teléfono de 10 dígitos, asunto y mensaje válido.', 'warning');
                return;
            }
            const payload = {
                nombre: (document.getElementById('contactoNombre')?.value || '').trim().replace(/\s+/g, ' '),
                correo: (document.getElementById('contactoCorreo')?.value || '').trim(),
                telefono: String(document.getElementById('contactoTelefono')?.value || '').replace(/\D/g, ''),
                asunto: (document.getElementById('contactoAsunto')?.value || '').trim(),
                mensaje: (document.getElementById('contactoMensaje')?.value || '').trim(),
            };
            if (alertEl) alertEl.classList.add('d-none');
            const reNom = /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+(?:\s+[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+){1,4}$/;
            if (!reNom.test(payload.nombre)) {
                showAlert('<i class="bi bi-exclamation-triangle me-1"></i>Nombre completo: incluye nombre y apellidos (solo letras).', 'warning');
                return;
            }
            if (!payload.correo || !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(payload.correo)) {
                showAlert('<i class="bi bi-exclamation-triangle me-1"></i>Correo electrónico no válido.', 'warning');
                return;
            }
            if (!/^\d{10}$/.test(payload.telefono)) {
                showAlert('<i class="bi bi-exclamation-triangle me-1"></i>Teléfono: exactamente 10 dígitos (obligatorio).', 'warning');
                return;
            }
            if (!payload.asunto || payload.asunto.length < 3) {
                showAlert('<i class="bi bi-exclamation-triangle me-1"></i>Escribe un asunto (mín. 3 caracteres).', 'warning');
                return;
            }
            if (!payload.mensaje) {
                showAlert('<i class="bi bi-exclamation-triangle me-1"></i>Escribe el mensaje.', 'warning');
                return;
            }
            const palabras = contarPalabrasContacto(payload.mensaje);
            const msgEl = document.getElementById('contactoMensaje');
            const msgErr = document.getElementById('contactoMensajeError');
            if (msgErr) { msgErr.textContent = ''; msgErr.classList.add('d-none'); }
            if (msgEl) msgEl.classList.remove('is-invalid', 'is-valid');
            if (palabras < 5) {
                const m = 'El mensaje debe tener al menos 5 palabras (ahora: ' + palabras + ').';
                if (msgErr) { msgErr.textContent = m; msgErr.classList.remove('d-none'); }
                if (msgEl) msgEl.classList.add('is-invalid');
                showAlert('<i class="bi bi-exclamation-triangle me-1"></i>' + m, 'warning');
                return;
            }
            if (palabras > 100) {
                const m = 'El mensaje no puede superar 100 palabras (ahora: ' + palabras + ').';
                if (msgErr) { msgErr.textContent = m; msgErr.classList.remove('d-none'); }
                if (msgEl) msgEl.classList.add('is-invalid');
                showAlert('<i class="bi bi-exclamation-triangle me-1"></i>' + m, 'warning');
                return;
            }
            if (msgEl) msgEl.classList.add('is-valid');

            if (btn) {
                btn.disabled = true;
                btn.dataset._old = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando…';
            }
            try {
                const csrf = await ensureCsrf();
                let recaptcha_token = '';
                try {
                    recaptcha_token = await getRecaptchaToken('contacto');
                } catch (_) {}
                const res = await fetch('/api/public/contacto', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': csrf || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(Object.assign({}, payload, {
                        recaptcha_token: recaptcha_token || '',
                        _csrf: csrf || ''
                    }))
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || data.success === false) {
                    let msg = data.message || 'No se pudo enviar el mensaje.';
                    if (data.errors) {
                        try {
                            msg = Object.values(data.errors).flat().join(' ');
                        } catch (_) {}
                    }
                    showAlert('<i class="bi bi-x-circle me-1"></i>' + msg, 'danger');
                    return;
                }
                showAlert('<i class="bi bi-check-circle me-1"></i>' + (data.message || 'Mensaje enviado correctamente. Revisa tu correo de confirmación.'), 'success');
                form.reset();
                if (typeof actualizarContadorMensajeContacto === 'function') actualizarContadorMensajeContacto();
                ['contactoNombre','contactoCorreo','contactoTelefono','contactoAsunto','contactoMensaje'].forEach(function (id) {
                    var el = document.getElementById(id);
                    if (el) el.classList.remove('is-valid', 'is-invalid');
                });
                ['okContactoNombre','okContactoCorreo','okContactoTel','okContactoAsunto','okContactoMensaje'].forEach(function (id) {
                    var el = document.getElementById(id);
                    if (el) el.classList.add('d-none');
                });
                if (typeof rupeToast === 'function') {
                    rupeToast('Mensaje enviado a soporte RUPE CDMX', 'success');
                }
            } catch (err) {
                console.warn('[RUPE] contacto', err);
                showAlert('<i class="bi bi-x-circle me-1"></i>Error de red al enviar. Intenta de nuevo.', 'danger');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    if (btn.dataset._old) btn.innerHTML = btn.dataset._old;
                }
            }
        };
        window.enviarContacto = enviarContacto;

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof actualizarContadorMensajeContacto === 'function') actualizarContadorMensajeContacto();
            const lp = document.getElementById('loginPassword');
            if (lp && !lp._rupePassBound) {
                lp._rupePassBound = true;
                lp.addEventListener('input', () => { if (window.actualizarHintPassLogin) actualizarHintPassLogin(); });
            }
            const lc = document.getElementById('loginCorreo');
            if (lc && !lc._rupeMailBound) {
                lc._rupeMailBound = true;
                lc.addEventListener('input', () => { if (window.filtrarCorreoLogin) filtrarCorreoLogin(lc); });
            }

            // Registro: filtros en vivo nombre / apellidos / correo / contraseña
            [
                ['regNombreCompleto', 'regNombreError', 2, 60],
                ['regApellido1', 'regApellido1Error', 2, 50],
                ['regApellido2', 'regApellido2Error', 2, 50]
            ].forEach(([id, errId, minL, maxL]) => {
                const el = document.getElementById(id);
                if (!el || el._rupeNomBound) return;
                el._rupeNomBound = true;
                el.addEventListener('input', () => {
                    if (window.filtrarNombreRegistro) filtrarNombreRegistro(el, errId, minL, maxL);
                });
            });
            const regMail = document.getElementById('regCorreo');
            if (regMail && !regMail._rupeMailBound) {
                regMail._rupeMailBound = true;
                regMail.addEventListener('input', () => {
                    if (window.filtrarCorreoRegistro) filtrarCorreoRegistro(regMail);
                });
            }
            const pwd = document.getElementById('regPassword');
            if (pwd && !pwd._rupePassBound) {
                pwd._rupePassBound = true;
                pwd.addEventListener('input', () => {
                    if (window.actualizarFuerzaPassRegistro) actualizarFuerzaPassRegistro();
                });
            }
            const pwdC = document.getElementById('regPasswordConfirm');
            if (pwdC && !pwdC._rupePassBound) {
                pwdC._rupePassBound = true;
                pwdC.addEventListener('input', () => {
                    if (window.actualizarConfirmPassRegistro) actualizarConfirmPassRegistro();
                });
            }
        });


        // Cargar datos al iniciar + activar badge reCAPTCHA v3
        document.addEventListener('DOMContentLoaded', () => {
            cargarAlcaldiasHome();
            filtrarMascotas();
            try {
                const q = new URLSearchParams(window.location.search || '');
                const open = (q.get('open') || '').toLowerCase();
                if (open === 'login') {
                    const el = document.getElementById('modalLogin');
                    if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show();
                }
                if (open === 'registro' || open === 'register') {
                    const el = document.getElementById('modalRegistro');
                    if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show();
                }
            } catch (_) {}

            // Ejecutar reCAPTCHA al cargar para que el badge de Google aparezca
            
        // Forzar aparición del badge reCAPTCHA v3
        (function initRupeRecaptchaUi() {
            function setStatus(ok, msg) {
                document.querySelectorAll('.rupe-recaptcha-status').forEach(function (el) {
                    el.classList.toggle('ok', !!ok);
                    el.classList.toggle('err', !ok);
                    el.innerHTML = ok
                        ? '<i class="bi bi-shield-check"></i> ' + (msg || 'reCAPTCHA activo')
                        : '<i class="bi bi-shield-exclamation"></i> ' + (msg || 'reCAPTCHA no cargó');
                });
            }
            var key = window.__RECAPTCHA_SITE_KEY__ || '';
            if (!key) {
                setStatus(false, 'Falta RECAPTCHA_SITE_KEY en config.php');
                return;
            }
            function tryExec() {
                if (typeof grecaptcha === 'undefined' || !grecaptcha.execute) {
                    return false;
                }
                try {
                    grecaptcha.ready(function () {
                        grecaptcha.execute(key, { action: 'homepage' }).then(function () {
                            window.__RECAPTCHA_READY__ = true;
                            setStatus(true, 'Protegido con reCAPTCHA v3');
                            // Asegurar badge visible
                            var badge = document.querySelector('.grecaptcha-badge');
                            if (badge) {
                                badge.style.visibility = 'visible';
                                badge.style.opacity = '1';
                                badge.style.display = 'block';
                                badge.style.zIndex = '2147483647';
                            }
                        }).catch(function () {
                            setStatus(false, 'Error al ejecutar reCAPTCHA (revisa dominio en Google)');
                        });
                    });
                    return true;
                } catch (e) {
                    return false;
                }
            }
            var n = 0;
            var timer = setInterval(function () {
                n += 1;
                if (tryExec() || n > 50) {
                    clearInterval(timer);
                    if (n > 50 && !window.__RECAPTCHA_READY__) {
                        setStatus(false, 'No se cargó el script de Google (bloqueador o dominio no autorizado)');
                    }
                }
            }, 200);
        })();

            if (window.__RECAPTCHA_SITE_KEY__) {
                getRecaptchaToken('homepage').then((t) => {
                    if (t) console.info('[RUPE] reCAPTCHA v3 listo');
                    else console.warn('[RUPE] reCAPTCHA v3 sin token (revisa dominio y site key)');
                });
            } else {
                console.warn('[RUPE] Falta RECAPTCHA_SITE_KEY en config.php');
            }
        });
    </script>

<script>
(function () {
  function validNombreCompleto(v) {
    v = (v || '').trim().replace(/\s+/g, ' ');
    return /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+(?:\s+[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+){1,4}$/.test(v);
  }
  function validCorreo(v) {
    v = (v || '').trim();
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) && v.length <= 80;
  }
  function validTel(v) {
    return /^\d{10}$/.test(String(v || '').replace(/\D/g, ''));
  }
  function validAsunto(v) {
    return (v || '').trim().length >= 3;
  }
  function validMensaje(v) {
    var words = String(v || '').trim().split(/\s+/).filter(Boolean);
    return words.length >= 5 && words.length <= 100;
  }
  function setOk(el, okSpan, ok, force) {
    if (!el) return ok;
    el.classList.toggle('is-valid', ok);
    el.classList.toggle('is-invalid', !ok && (force || (el.value || '').trim() !== ''));
    if (okSpan) okSpan.classList.toggle('d-none', !ok);
    return ok;
  }
  function rupeValidarContacto(force) {
    var nom = document.getElementById('contactoNombre');
    var cor = document.getElementById('contactoCorreo');
    var tel = document.getElementById('contactoTelefono');
    var asu = document.getElementById('contactoAsunto');
    var msg = document.getElementById('contactoMensaje');
    if (tel) {
      var d = String(tel.value || '').replace(/\D/g, '').slice(0, 10);
      if (tel.value !== d) tel.value = d;
    }
    var a = setOk(nom, document.getElementById('okContactoNombre'), validNombreCompleto(nom && nom.value), force);
    var b = setOk(cor, document.getElementById('okContactoCorreo'), validCorreo(cor && cor.value), force);
    var c = setOk(tel, document.getElementById('okContactoTel'), validTel(tel && tel.value), force);
    var d2 = setOk(asu, document.getElementById('okContactoAsunto'), validAsunto(asu && asu.value), force);
    var e = setOk(msg, document.getElementById('okContactoMensaje'), validMensaje(msg && msg.value), force);
    return a && b && c && d2 && e;
  }
  window.rupeValidarContacto = rupeValidarContacto;
  document.addEventListener('DOMContentLoaded', function () {
    ['contactoNombre','contactoCorreo','contactoTelefono','contactoAsunto','contactoMensaje'].forEach(function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.addEventListener('input', function () { rupeValidarContacto(false); });
      el.addEventListener('blur', function () { rupeValidarContacto(false); });
    });
  });
})();
</script>


<script>
(function () {
  function togglePass(btn) {
    var id = btn.getAttribute('data-target');
    var input = id ? document.getElementById(id) : null;
    if (!input) return;
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    var icon = btn.querySelector('i');
    if (icon) {
      icon.classList.toggle('bi-eye', !show);
      icon.classList.toggle('bi-eye-slash', show);
    }
    btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
    try { input.focus(); } catch (e) {}
  }
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-toggle-pass');
    if (!btn) return;
    e.preventDefault();
    togglePass(btn);
  });
})();
</script>

</body>
</html>