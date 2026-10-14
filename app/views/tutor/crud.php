<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RUPE CDMX - Sistema de Registro & Panel Analítico</title>
  
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <!-- Chart.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root {
      /* ---- Paleta RUPE CDMX: guinda / dorado / blanco / grises claros ---- */
      --color-primary: #6B1D2F;        /* guinda */
      --color-primary-dark: #4A1521;   /* guinda oscuro (hover/overlays) */
      --color-primary-light: #8C2C42;  /* guinda claro (hover) */
      --color-secondary: #206A5D;
      --color-accent: #E8A33D;         /* dorado */
      --color-accent-light: #F4C978;   /* dorado claro */
      --color-white: #FFFFFF;
      --color-bg: #F4F6F8;             /* gris muy claro (fondo) */
      --color-gray-100: #F1F3F5;
      --color-gray-200: #E9ECEF;
      --color-gray-300: #DEE2E6;
      --color-gray-600: #6C757D;
      --shadow-soft: 0 8px 24px rgba(107, 29, 47, 0.12);
      --nav-transition: 0.35s cubic-bezier(0.22, 1, 0.36, 1);
      --ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    /* Animaciones reutilizables (ES6 + CSS) */
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes popIn { 0% { opacity: 0; transform: scale(0.92); } 100% { opacity: 1; transform: scale(1); } }

    /* Micro-interacciones consistentes en tarjetas y botones (escala Bootstrap 5) */
    .card { transition: transform 0.25s ease, box-shadow 0.25s ease; }
    .card.card-hover:hover, .list-group-item-action:hover { transform: translateY(-2px); box-shadow: var(--shadow-soft); }
    .btn { transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.2s ease, color 0.2s ease; }
    .btn:active { transform: scale(0.97); }
    .badge { transition: transform 0.2s ease; }

    body {
      background-color: var(--color-bg);
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    /* ===================== MEGA MENÚ DE MÓDULOS (dropdown, sin lateral) ===================== */
    .btn-modulos {
      display: flex;
      align-items: center;
      gap: 0.55rem;
      background: rgba(255, 255, 255, 0.14);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #fff;
      font-weight: 700;
      font-size: 0.9rem;
      border-radius: 10px;
      padding: 8px 14px;
      transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .btn-modulos:hover,
    .btn-modulos:focus,
    .btn-modulos.show {
      background: rgba(255, 255, 255, 0.26);
      color: #fff;
      transform: translateY(-1px);
    }
    .btn-modulos .modulo-actual-num {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: #fff;
      color: var(--color-primary);
      font-size: 0.72rem;
      font-weight: 800;
      flex-shrink: 0;
    }
    .btn-modulos::after {
      transition: transform 0.25s ease;
    }
    .btn-modulos.show::after {
      transform: rotate(-180deg);
    }

    /* ===================== MENÚ HORIZONTAL DE MÓDULOS + HAMBURGUESA (responsivo) ===================== */
    .rupe-nav-wrapper {
      position: relative;
      flex: 1 1 auto;
      min-width: 0;
      display: flex;
      align-items: center;
    }

    /* Botón hamburguesa animado (3 líneas -> X) */
    .rupe-toggler {
      display: none;
      align-items: center;
      justify-content: center;
      width: 42px;
      height: 38px;
      flex-shrink: 0;
      background: rgba(255, 255, 255, 0.14);
      border: 1px solid rgba(255, 255, 255, 0.22);
      border-radius: 10px;
      transition: background-color 0.2s ease;
    }
    .rupe-toggler:hover { background: rgba(255, 255, 255, 0.26); }
    .rupe-toggler:focus-visible {
      outline: none;
      box-shadow: 0 0 0 3px rgba(232, 163, 61, 0.45);
    }
    .rupe-toggler-icon { position: relative; width: 20px; height: 15px; display: inline-block; }
    .rupe-toggler-icon span {
      position: absolute;
      left: 0;
      width: 100%;
      height: 2px;
      border-radius: 2px;
      background: #fff;
      transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.2s ease, top 0.3s ease;
    }
    .rupe-toggler-icon span:nth-child(1) { top: 0; }
    .rupe-toggler-icon span:nth-child(2) { top: 6.5px; }
    .rupe-toggler-icon span:nth-child(3) { top: 13px; }
    .rupe-toggler[aria-expanded="true"] .rupe-toggler-icon span:nth-child(1) { top: 6.5px; transform: rotate(45deg); }
    .rupe-toggler[aria-expanded="true"] .rupe-toggler-icon span:nth-child(2) { opacity: 0; }
    .rupe-toggler[aria-expanded="true"] .rupe-toggler-icon span:nth-child(3) { top: 6.5px; transform: rotate(-45deg); }

    .topbar-current-modulo { color: rgba(255, 255, 255, 0.9); }
    .topbar-current-modulo .modulo-actual-num {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: var(--color-accent);
      color: var(--color-primary-dark, #33101a);
      font-size: 0.68rem;
      font-weight: 800;
      flex-shrink: 0;
    }

    /* Panel de navegación (colapsable en móvil, siempre visible en escritorio) */
    .rupe-nav-collapse:not(.show) { display: none; }
    .rupe-nav-collapse.collapsing { display: block; height: 0; overflow: hidden; transition: height var(--nav-transition); }

    .rupe-nav-list {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      gap: 0.3rem;
    }

    .rupe-nav-link {
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      width: 100%;
      border: none;
      background: transparent;
      color: rgba(255, 255, 255, 0.85);
      font-weight: 600;
      font-size: 0.82rem;
      white-space: nowrap;
      padding: 0.5rem 0.75rem;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }
    .rupe-nav-link i { font-size: 0.95rem; }
    .rupe-nav-link:hover { background: rgba(255, 255, 255, 0.14); color: #fff; transform: translateY(-1px); }
    .rupe-nav-link::after {
      content: "";
      position: absolute;
      left: 0.75rem;
      right: 0.75rem;
      bottom: 3px;
      height: 2px;
      background: var(--color-accent);
      border-radius: 2px;
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .rupe-nav-link.active { background: rgba(255, 255, 255, 0.16); color: #fff; }
    .rupe-nav-link.active::after { transform: scaleX(1); }

    /* Efecto ripple al pulsar un enlace del menú (reutiliza crearRippleEnBoton) */
    .rupe-nav-link .ripple {
      position: absolute;
      border-radius: 50%;
      transform: scale(0);
      background: rgba(255, 255, 255, 0.35);
      animation: rippleAnim 0.55s ease-out;
      pointer-events: none;
    }
    @keyframes rippleAnim {
      to { transform: scale(3.2); opacity: 0; }
    }

    /* ---------- Escritorio grande (>=1200px): menú horizontal siempre visible ---------- */
    @media (min-width: 1200px) {
      .rupe-nav-collapse { display: flex !important; height: auto !important; overflow: visible !important; }
      .rupe-nav-list {
        flex-direction: row;
        align-items: center;
        overflow-x: auto;
        scrollbar-width: none;
      }
      .rupe-nav-list::-webkit-scrollbar { display: none; }
      .topbar-current-modulo { display: none !important; }
    }

    /* ---------- Móvil / tablet (<1200px): hamburguesa + panel desplegable ---------- */
    @media (max-width: 1199.98px) {
      .rupe-toggler { display: inline-flex; }

      .rupe-nav-collapse {
        position: absolute;
        top: calc(100% + 0.6rem);
        left: 0;
        right: 0;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
        padding: 0.6rem;
        max-height: 75vh;
        overflow-y: auto;
        z-index: 1050;
        pointer-events: auto;
      }
      .rupe-nav-list { flex-direction: column; gap: 0.15rem; }
      .rupe-nav-link { color: var(--color-primary); }
      .rupe-nav-link:hover { background: var(--color-bg); transform: none; }
      .rupe-nav-link.active { background: rgba(107, 29, 47, 0.08); color: var(--color-primary); }
      .rupe-nav-link::after { background: var(--color-primary); }
      .rupe-nav-link .ripple { background: rgba(107, 29, 47, 0.18); }

      .rupe-nav-collapse.show .rupe-nav-list li {
        opacity: 0;
        transform: translateY(-6px);
        animation: rupeItemIn 0.32s ease forwards;
        animation-delay: calc(var(--i, 1) * 0.045s);
      }
    }
    @keyframes rupeItemIn {
      to { opacity: 1; transform: translateY(0); }
    }

    /* ===================== BARRA SUPERIOR (móvil / general) ===================== */
    .topbar {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      background: linear-gradient(100deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
      color: #fff;
      padding: 0.7rem 1rem;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
      position: sticky;
      top: 0;
      z-index: 1030;
      transition: padding var(--nav-transition), box-shadow var(--nav-transition);
    }
    .topbar.navbar-scrolled {
      padding-top: 0.45rem;
      padding-bottom: 0.45rem;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    }
    @media (min-width: 992px) {
      .topbar { padding-left: 1.75rem; padding-right: 1.75rem; }
    }

    .topbar-brand {
      display: flex;
      align-items: center;
      gap: 0.55rem;
      color: #fff;
      font-weight: 800;
      letter-spacing: 0.01em;
      flex-shrink: 0;
    }
    .topbar-brand i {
      font-size: 1.3rem;
      transition: transform 0.5s ease;
    }
    .topbar-brand:hover i {
      transform: rotate(-10deg) scale(1.15);
    }

    .topbar-title {
      font-weight: 700;
      flex: 1;
    }

    /* ===== Menú desplegable de usuario (topbar) ===== */
    .user-dropdown .btn-user-dropdown {
      display: flex;
      align-items: center;
      gap: 0.55rem;
      background: rgba(255, 255, 255, 0.12);
      border: 1px solid rgba(255, 255, 255, 0.18);
      color: #fff;
      border-radius: 999px;
      padding: 5px 12px 5px 6px;
      font-size: 0.85rem;
      font-weight: 600;
      transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .user-dropdown .btn-user-dropdown:hover,
    .user-dropdown .btn-user-dropdown:focus,
    .user-dropdown .btn-user-dropdown.show {
      background: rgba(255, 255, 255, 0.22);
      color: #fff;
      transform: translateY(-1px);
    }
    .user-dropdown .btn-user-dropdown::after {
      transition: transform 0.25s ease;
    }
    .user-dropdown .btn-user-dropdown.show::after {
      transform: rotate(-180deg);
    }
    .user-dropdown .avatar-mini {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      background: var(--color-accent);
      color: #4A1521;
      font-weight: 800;
      font-size: 0.7rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .user-dropdown .dropdown-menu {
      border: none;
      border-radius: 12px;
      padding: 0.4rem;
      margin-top: 0.6rem !important;
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.18);
      animation: dropdownAbrir 0.18s ease;
      min-width: 210px;
    }
    @keyframes dropdownAbrir {
      from { opacity: 0; transform: translateY(-6px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .user-dropdown .dropdown-header {
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--color-primary);
    }
    .user-dropdown .dropdown-item {
      border-radius: 8px;
      padding: 8px 10px;
      font-size: 0.87rem;
      display: flex;
      align-items: center;
      transition: background-color 0.15s ease, transform 0.15s ease;
    }
    .user-dropdown .dropdown-item:hover {
      background: var(--color-bg);
      transform: translateX(2px);
    }
    .user-dropdown .dropdown-item.text-danger:hover {
      background: #fdeaec;
    }

    .app-content {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* Insignia "Plataforma Gratuita" con pulso sutil */
    .badge-pulso {
      position: relative;
    }
    .badge-pulso::before {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: inherit;
      background: inherit;
      animation: badgePulso 2.2s ease-out infinite;
      z-index: -1;
    }
    @keyframes badgePulso {
      0% { transform: scale(1); opacity: 0.55; }
      100% { transform: scale(1.6); opacity: 0; }
    }

    /* Overlay de cierre de sesión */
    .logout-overlay {
      position: fixed;
      inset: 0;
      background: linear-gradient(165deg, var(--color-primary) 0%, #33101A 100%);
      color: #fff;
      z-index: 2000;
      display: none;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      gap: 1rem;
      text-align: center;
      animation: fadeIn 0.35s ease;
    }
    .logout-overlay.visible {
      display: flex;
    }
    .logout-overlay i {
      font-size: 2.6rem;
      animation: navActivoPop 0.5s ease;
    }

    @keyframes navActivoPop {
      0% { transform: scale(0.7); opacity: 0; }
      100% { transform: scale(1); opacity: 1; }
    }

    @media (prefers-reduced-motion: reduce) {
      .rupe-nav-list li,
      .rupe-nav-collapse,
      .rupe-toggler-icon span,
      .badge-pulso::before,
      .logout-overlay,
      .logout-overlay i {
        animation: none !important;
        transition: none !important;
        opacity: 1 !important;
      }
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

    #mapaGPS {
      height: 320px;
      width: 100%;
      border-radius: 10px;
      border: 2px solid var(--color-secondary);
      z-index: 1;
    }

    .ficha-preview-pdf {
      border: 3px double var(--color-primary);
      background: #ffffff;
      padding: 20px;
      border-radius: 8px;
    }

    .qr-box {
      border: 2px solid var(--color-secondary);
      background-color: #f0f7f5;
      border-radius: 8px;
      padding: 10px;
    }

    .chart-container {
      position: relative;
      height: 250px;
      width: 100%;
    }

    /* ===================== ESTILOS ESPECÍFICOS: PANEL DEL TUTOR (Módulos 9-11) ===================== */
    .btn-guinda { background: var(--color-primary); color: #fff; border: none; border-radius: 10px; font-weight: 600; }
    .btn-guinda:hover { background: var(--color-primary-light); color: #fff; }
    .btn-outline-guinda { border: 2px solid var(--color-primary); color: var(--color-primary); border-radius: 10px; font-weight: 600; }
    .btn-outline-guinda:hover { background: var(--color-primary); color: #fff; }
    .ficha-oficial {
      border: 3px double var(--color-primary);
      background: #fff;
      padding: 1.25rem 1.5rem;
      border-radius: 10px;
      max-width: 8.5in;
      width: 100%;
      margin: 0 auto;
      box-sizing: border-box;
    }
    .ficha-datos th, .ficha-datos td {
      padding: 0.28rem 0.4rem;
      vertical-align: top;
      font-size: 0.92rem;
    }
    .ficha-datos th { white-space: nowrap; }
    .ficha-cuerpo { align-items: flex-start !important; }
    .ficha-datos th, .ficha-datos td { padding: 0.28rem 0.4rem; vertical-align: top; font-size: 0.92rem; }
    .ficha-datos th { white-space: nowrap; }
    .preview-foto {
      width: 100%; max-height: 220px; object-fit: cover; border-radius: 10px; border: 2px dashed #dee2e6;
      background: #f8f9fa;
    }
    .badge-estatus-busqueda { background: #dc3545; }
    .badge-estatus-resguardo { background: #ffc107; color: #212529; }
    .badge-estatus-localizado { background: #198754; }
    .avistamiento-card { border-left: 4px solid var(--color-primary); }

    /* ===================== MÓDULO 2: REGISTRO — grid de 4, insignias, dropzone, progreso ===================== */
    .num-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 20px;
      height: 20px;
      flex-shrink: 0;
      border-radius: 50%;
      background: var(--color-primary);
      color: #fff;
      font-size: 0.65rem;
      font-weight: 800;
      margin-right: 0.4rem;
      transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .form-control.is-valid ~ * .num-badge,
    .form-group-valid .num-badge { background: #198754; }

    .form-control:focus ~ .num-badge { transform: scale(1.1); }

    .rupe-form-seccion-titulo {
      font-size: 0.72rem;
      font-weight: 800;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: var(--color-primary);
      display: flex;
      align-items: center;
      gap: 0.4rem;
      border-bottom: 2px solid var(--color-gray-200);
      padding-bottom: 0.5rem;
    }

    #formCapturaPerro .form-control,
    #formCapturaPerro .form-select {
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    #formCapturaPerro .form-control:focus,
    #formCapturaPerro .form-select:focus {
      border-color: var(--color-primary-light);
      box-shadow: 0 0 0 0.2rem rgba(107, 29, 47, 0.15);
    }
    #formCapturaPerro .form-control.is-valid,
    #formCapturaPerro .form-select.is-valid {
      border-color: #198754;
      background-image: none;
    }

    .rupe-dropzone {
      border: 2px dashed var(--color-gray-300);
      border-radius: 12px;
      padding: 1.1rem;
      text-align: center;
      cursor: pointer;
      color: var(--color-gray-600);
      transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.15s ease;
    }
    .rupe-dropzone i { font-size: 1.6rem; color: var(--color-primary); }
    .rupe-dropzone:hover { border-color: var(--color-primary); background: rgba(107, 29, 47, 0.03); }
    .rupe-dropzone.dragover {
      border-color: var(--color-primary);
      background: rgba(107, 29, 47, 0.06);
      transform: scale(1.01);
    }
    .rupe-dropzone.tiene-archivo { border-style: solid; border-color: #198754; color: #198754; }
    .rupe-dropzone.tiene-archivo i { color: #198754; }

    .rupe-form-progreso .progress { height: 8px; border-radius: 6px; background: var(--color-gray-200); }
    .rupe-form-progreso .progress-bar {
      background-color: var(--color-primary);
      transition: width 0.35s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .rupe-form-progreso .progress-bar.completo { background-color: #198754; }
  
    /* RUPE_NAV_CLICK_FIX — menú tutor clicable */
    .rupe-nav-link {
      cursor: pointer !important;
      pointer-events: auto !important;
      min-height: 40px;
      -webkit-tap-highlight-color: rgba(107, 29, 47, 0.15);
      user-select: none;
      border: none;
      width: 100%;
      text-align: left;
    }
    .rupe-nav-wrapper { z-index: 20; position: relative; }
    .topbar { position: sticky; top: 0; z-index: 1030; }
    .user-dropdown { z-index: 1060; position: relative; }
    .user-dropdown .dropdown-menu { z-index: 1060; }

  
    /* ========== IMPRESIÓN FICHA: solo tamaño CARTA (Letter 8.5 × 11 in) ========== */
    @page {
      size: letter portrait;
      margin: 12mm 14mm;
    }
    @media print {
      html, body {
        width: 8.5in;
        height: 11in;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      /* Ocultar todo menos la ficha oficial */
      body * { visibility: hidden !important; }
      #tutorFichaParaImprimir,
      #tutorFichaParaImprimir * {
        visibility: visible !important;
      }
      #tutorFichaParaImprimir {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        max-width: 8.5in !important;
        margin: 0 !important;
        padding: 10mm 12mm !important;
        border: 2px solid #6B1D2F !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background: #fff !important;
        page-break-after: avoid;
        page-break-inside: avoid;
      }
      /* Controles, nav, otros módulos */
      .navbar, nav, footer, .sidebar, .nav-modulo,
      .btn, button, select, .rupe-form-progreso,
      #tutorContenedorFichaVacia,
      .modulo-seccion:not(:has(#tutorFichaParaImprimir)),
      .d-print-none {
        display: none !important;
      }
      /* Módulo ficha visible al imprimir */
      #modulo-ficha-pdf.modulo-seccion,
      #tutorContenedorFicha {
        display: block !important;
        visibility: visible !important;
      }
      #mapaFichaTutor { display: none !important; }
      #tutorFichaMapaStatic {
        display: block !important;
        visibility: visible !important;
        width: 100% !important;
        max-height: 200px !important;
        height: auto !important;
        break-inside: avoid;
      }
      #tutorFichaMapaWrap {
        display: block !important;
        visibility: visible !important;
      }
      #tutorFichaQR {
        width: 110px !important;
        height: 110px !important;
      }
      #tutorFichaFoto {
        max-height: 160px !important;
      }
      a[href]::after { content: none !important; }
    }

  
    /* ===== Menú módulos: acomodado, sin afectar user-dropdown ===== */
    .rupe-nav-wrapper {
      position: relative;
      flex: 1 1 auto;
      min-width: 0;
      display: flex;
      align-items: center;
      gap: 0.35rem;
      overflow: hidden;
    }
    .rupe-nav-collapse {
      flex: 1 1 auto;
      min-width: 0;
    }
    .rupe-nav-list {
      display: flex;
      flex-wrap: nowrap;
      align-items: center;
      gap: 0.2rem;
      list-style: none;
      margin: 0;
      padding: 0.15rem 0.25rem;
      overflow-x: auto;
      overflow-y: hidden;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: thin;
      scrollbar-color: rgba(255,255,255,0.35) transparent;
    }
    .rupe-nav-list::-webkit-scrollbar { height: 4px; }
    .rupe-nav-list::-webkit-scrollbar-thumb {
      background: rgba(255,255,255,0.35);
      border-radius: 4px;
    }
    .rupe-nav-list li {
      flex: 0 0 auto;
      margin: 0;
      padding: 0;
    }
    .rupe-nav-link {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      white-space: nowrap;
      border: 0;
      background: transparent;
      color: rgba(255,255,255,0.92);
      font-size: 0.78rem;
      font-weight: 600;
      line-height: 1.2;
      padding: 0.4rem 0.65rem;
      border-radius: 8px;
      cursor: pointer;
      position: relative;
      transition: background-color 0.15s ease, color 0.15s ease;
    }
    .rupe-nav-link i {
      font-size: 0.9rem;
      opacity: 0.95;
    }
    .rupe-nav-link:hover {
      background: rgba(255,255,255,0.16);
      color: #fff;
    }
    .rupe-nav-link.active {
      background: rgba(255,255,255,0.22);
      color: #fff;
      box-shadow: inset 0 -2px 0 #E8A33D;
    }
    .topbar {
      display: flex;
      align-items: center;
      flex-wrap: nowrap;
      gap: 0.35rem;
      width: 100%;
    }
    .topbar-brand {
      flex: 0 0 auto;
    }
    .user-dropdown {
      flex: 0 0 auto;
      margin-left: auto !important;
    }
    @media (max-width: 991.98px) {
      .rupe-toggler { display: inline-flex !important; }
      .rupe-nav-collapse {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 1050;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 28px rgba(0,0,0,0.18);
        padding: 0.5rem;
        max-height: min(70vh, 420px);
        overflow-y: auto;
      }
      .rupe-nav-collapse:not(.show) { display: none; }
      .rupe-nav-list {
        flex-direction: column;
        align-items: stretch;
        overflow: visible;
        gap: 0.15rem;
      }
      .rupe-nav-link {
        width: 100%;
        color: var(--color-primary);
        justify-content: flex-start;
        padding: 0.55rem 0.75rem;
        font-size: 0.9rem;
      }
      .rupe-nav-link:hover { background: rgba(107, 29, 47, 0.08); color: var(--color-primary); }
      .rupe-nav-link.active {
        background: rgba(107, 29, 47, 0.12);
        color: var(--color-primary);
        box-shadow: inset 3px 0 0 var(--color-primary);
      }
      .topbar-current-modulo { display: none !important; }
    }
    @media (min-width: 992px) {
      .rupe-toggler { display: none !important; }
      .rupe-nav-collapse {
        display: flex !important;
        height: auto !important;
        overflow: visible !important;
        position: static;
        background: transparent;
        box-shadow: none;
        padding: 0;
      }
    }

  
    /* PDF solo panel analítico */
    @media print {
      body.print-solo-analisis * { visibility: hidden !important; }
      body.print-solo-analisis #modulo-analisis,
      body.print-solo-analisis #modulo-analisis * {
        visibility: visible !important;
      }
      body.print-solo-analisis #modulo-analisis {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        display: block !important;
        background: #fff !important;
        padding: 12mm !important;
      }
      body.print-solo-analisis .d-print-none,
      body.print-solo-analisis .topbar,
      body.print-solo-analisis .navbar,
      body.print-solo-analisis .rupe-nav-wrapper,
      body.print-solo-analisis .btn,
      body.print-solo-analisis button {
        display: none !important;
        visibility: hidden !important;
      }
      body.print-solo-analisis .chart-container {
        break-inside: avoid;
        height: 220px !important;
      }
      body.print-solo-analisis canvas {
        max-width: 100% !important;
      }
    }

  
    @media print {
      body.print-ficha-oficial * { visibility: hidden !important; }
      body.print-ficha-oficial #modulo-ficha-pdf,
      body.print-ficha-oficial #modulo-ficha-pdf * {
        visibility: visible !important;
      }
      body.print-ficha-oficial #modulo-ficha-pdf {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        display: block !important;
        background: #fff !important;
      }
      body.print-ficha-oficial .d-print-none,
      body.print-ficha-oficial .topbar,
      body.print-ficha-oficial button,
      body.print-ficha-oficial .btn {
        display: none !important;
        visibility: hidden !important;
      }
      @page { size: letter; margin: 12mm; }
    }

  
    /* ===== RESPONSIVO MENÚ TUTOR (forzado, prioridad) ===== */
    .rupe-toggler { display: none; }
    .topbar {
      position: relative;
      z-index: 1040;
      flex-wrap: nowrap;
    }
    .rupe-nav-wrapper {
      position: relative;
      flex: 1 1 auto;
      min-width: 0;
      z-index: 1045;
    }
    .user-dropdown { flex: 0 0 auto; z-index: 1050; }

    @media (max-width: 1199.98px) {
      .rupe-toggler {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 38px;
        flex-shrink: 0;
        background: rgba(255,255,255,0.18) !important;
        border: 1px solid rgba(255,255,255,0.28) !important;
        border-radius: 10px !important;
        color: #fff !important;
        cursor: pointer;
      }
      .topbar-current-modulo {
        display: flex !important;
        align-items: center;
        max-width: 42vw;
        overflow: hidden;
      }
      .rupe-nav-collapse {
        display: none !important;
        position: absolute !important;
        top: calc(100% + 8px) !important;
        left: 0 !important;
        right: 0 !important;
        width: auto !important;
        min-width: min(100vw - 1.5rem, 360px);
        max-width: calc(100vw - 1rem);
        background: #fff !important;
        border-radius: 14px !important;
        box-shadow: 0 16px 40px rgba(0,0,0,0.25) !important;
        padding: 0.55rem !important;
        max-height: min(75vh, 520px) !important;
        overflow-y: auto !important;
        z-index: 2000 !important;
      }
      .rupe-nav-collapse.show {
        display: block !important;
      }
      .rupe-nav-list {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.2rem !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
        overflow: visible !important;
      }
      .rupe-nav-list li { width: 100%; margin: 0; }
      .rupe-nav-link {
        display: flex !important;
        width: 100% !important;
        align-items: center !important;
        gap: 0.5rem !important;
        color: #6B1D2F !important;
        background: transparent !important;
        border: 0 !important;
        border-radius: 10px !important;
        padding: 0.65rem 0.85rem !important;
        font-size: 0.92rem !important;
        font-weight: 600 !important;
        text-align: left !important;
      }
      .rupe-nav-link:hover,
      .rupe-nav-link.active {
        background: rgba(107, 29, 47, 0.1) !important;
        color: #6B1D2F !important;
        box-shadow: inset 3px 0 0 #6B1D2F !important;
      }
      .rupe-nav-link i { font-size: 1.05rem; }
    }
    @media (min-width: 1200px) {
      .rupe-toggler { display: none !important; }
      .rupe-nav-collapse {
        display: flex !important;
        position: static !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
        max-height: none !important;
        overflow: visible !important;
      }
      .rupe-nav-list {
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
      }
      .rupe-nav-link {
        color: rgba(255,255,255,0.95) !important;
        width: auto !important;
      }
      .rupe-nav-link.active {
        background: rgba(255,255,255,0.2) !important;
        color: #fff !important;
        box-shadow: inset 0 -2px 0 #E8A33D !important;
      }
    }

  
    #formEditarRegistro .form-control.is-valid,
    #formEditarRegistro .form-select.is-valid {
      border-color: #198754 !important;
      padding-right: calc(1.5em + 0.75rem);
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e") !important;
      background-repeat: no-repeat !important;
      background-position: right calc(0.375em + 0.1875rem) center !important;
      background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
    }
    #formEditarRegistro .form-control.is-invalid,
    #formEditarRegistro .form-select.is-invalid {
      border-color: #dc3545 !important;
    }

  </style>
  <link rel="stylesheet" href="/assets/css/rupe-responsive-nav.css?v=20261003menu">
</head>
<body>
<?php
  $seccion_activa = $seccion_activa ?? $modulo_activo ?? '';
  $secAliases = [
    'analitico' => 'analisis', 'analisis' => 'analisis',
    'gps' => 'gps-qr', 'gpsqr' => 'gps-qr', 'gps-qr' => 'gps-qr',
    'ficha' => 'ficha-pdf', 'ficha-pdf' => 'ficha-pdf',
    'miperfil' => 'perfil', 'perfil' => 'perfil',
    'cuenta' => 'configuracion', 'configuracion' => 'configuracion',
  ];
  if ($seccion_activa !== '' && isset($secAliases[$seccion_activa])) {
    $seccion_activa = $secAliases[$seccion_activa];
  }
  // Fallback por nombre de archivo
  if ($seccion_activa === '') {
    $bn = basename(__FILE__, '.php');
    $fileMap = [
      'analitico' => 'analisis', 'registro' => 'registro', 'crud' => 'crud',
      'ficha-pdf' => 'ficha-pdf', 'gpsqr' => 'gps-qr', 'seguimiento' => 'seguimiento',
      'estatus' => 'estatus', 'albergues' => 'albergues', 'miperfil' => 'perfil',
      'cuenta' => 'configuracion', 'ayuda' => 'ayuda',
    ];
    $seccion_activa = $fileMap[$bn] ?? 'analisis';
  }
  $rupeEs = static function (string $id) use ($seccion_activa): string {
    return $seccion_activa === $id ? 'modulo-seccion activo' : 'modulo-seccion';
  };
?>


  <!-- CONTENIDO PRINCIPAL: BARRA SUPERIOR + MÓDULOS (sin menú lateral) -->
  <div class="app-content">
    <header class="topbar">
      <a class="topbar-brand d-flex align-items-center text-decoration-none" href="/tutor" style="gap:0.45rem;">
        <span class="d-inline-flex align-items-center justify-content-center bg-white rounded-2 px-2 py-1" style="height:36px;">
          <img src="/assets/img/logo-unadm.png" alt="UnADM" style="height:26px;width:auto;object-fit:contain;" onerror="this.style.display='none'">
        </span>
        <span class="d-none d-sm-inline text-white fw-bold">RUPE CDMX</span>
      </a>

      <!-- MENÚ HORIZONTAL DE MÓDULOS / HAMBURGUESA EN MÓVIL -->
      <div class="rupe-nav-wrapper ms-2 ms-md-3">
        <button class="rupe-toggler" type="button" id="rupeNavToggler"
                data-bs-toggle="collapse" data-bs-target="#rupeNavCollapse"
                aria-controls="rupeNavCollapse" aria-expanded="false" aria-label="Abrir menú de módulos">
          <span class="rupe-toggler-icon"><span></span><span></span><span></span></span>
        </button>

        <span class="topbar-current-modulo d-flex align-items-center ms-2">
          <span class="modulo-actual-num" id="moduloActualNum">1</span>
          <span class="ms-2 small fw-semibold d-none d-sm-inline" id="moduloActualTexto">Panel Analítico</span>
        </span>

        <div class="collapse rupe-nav-collapse" id="rupeNavCollapse">
          <ul class="rupe-nav-list">
            <li style="--i:1"><button type="button" class="rupe-nav-link nav-modulo<?php echo (($seccion_activa ?? '') === 'analisis') ? ' active' : ''; ?>" data-modulo="analisis">
              <i class="bi bi-graph-up-arrow"></i><span>Panel Analítico</span></button></li>
            <li style="--i:2"><button type="button" class="rupe-nav-link nav-modulo<?php echo (($seccion_activa ?? '') === 'registro') ? ' active' : ''; ?>" data-modulo="registro">
              <i class="bi bi-pencil-square"></i><span>Registro</span></button></li>
            <li style="--i:3"><button type="button" class="rupe-nav-link nav-modulo<?php echo (($seccion_activa ?? '') === 'crud') ? ' active' : ''; ?>" data-modulo="crud">
              <i class="bi bi-table"></i><span>Tabla CRUD</span></button></li>
            <li style="--i:4"><button type="button" class="rupe-nav-link nav-modulo<?php echo (($seccion_activa ?? '') === 'ficha-pdf') ? ' active' : ''; ?>" data-modulo="ficha-pdf">
              <i class="bi bi-file-earmark-pdf-fill"></i><span>Ficha + QR</span></button></li>
            <li style="--i:5"><button type="button" class="rupe-nav-link nav-modulo<?php echo (($seccion_activa ?? '') === 'gps-qr') ? ' active' : ''; ?>" data-modulo="gps-qr">
              <i class="bi bi-geo-alt-fill"></i><span>GPS / QR</span></button></li>
            <li style="--i:6"><button type="button" class="rupe-nav-link nav-modulo<?php echo (($seccion_activa ?? '') === 'seguimiento') ? ' active' : ''; ?>" data-modulo="seguimiento">
              <i class="bi bi-radar"></i><span>Trazabilidad</span></button></li>
            <li style="--i:7"><button type="button" class="rupe-nav-link nav-modulo<?php echo (($seccion_activa ?? '') === 'estatus') ? ' active' : ''; ?>" data-modulo="estatus">
              <i class="bi bi-check-circle-fill"></i><span>Estatus</span></button></li>
            <li style="--i:8"><button type="button" class="rupe-nav-link nav-modulo<?php echo (($seccion_activa ?? '') === 'albergues') ? ' active' : ''; ?>" data-modulo="albergues">
              <i class="bi bi-house-heart-fill"></i><span>Albergues</span></button></li>
          </ul>
        </div>
      </div>

      <div class="dropdown user-dropdown ms-2 ms-md-3">
        <button class="btn btn-user-dropdown dropdown-toggle" type="button" id="btnUserDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Opciones de cuenta">
          <span class="avatar-mini" id="tutorAvatarMini">T</span>
          <span class="d-none d-sm-inline text-truncate" id="tutorUserName" style="max-width:110px">Tutor</span>
          <span class="d-inline d-sm-none label-opciones">Opciones</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="btnUserDropdown">
          <li><h6 class="dropdown-header"><span id="tutorUserHeader">Tutor</span> · Cuenta gratuita</h6></li>
          <li><a class="dropdown-item" href="/tutor/perfil" onclick="if(window.mostrarModuloPerfil){mostrarModuloPerfil(event);}return false;"><i class="bi bi-person-circle me-2"></i>Mi perfil</a></li>
          <li><a class="dropdown-item" href="/tutor/configuracion" onclick="if(window.mostrarModuloConfiguracion){mostrarModuloConfiguracion(event);}return false;"><i class="bi bi-gear me-2"></i>Configuración</a></li>
          <li><a class="dropdown-item" href="/tutor/ayuda" onclick="if(window.mostrarModuloAyuda){mostrarModuloAyuda(event);}return false;"><i class="bi bi-question-circle me-2"></i>Ayuda</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#modalCerrarSesion">
              <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
            </a>
          </li>
        </ul>
      </div>
    </header>

  <!-- CONTENEDOR PRINCIPAL -->
  <main class="container py-4">

    <!-- MÓDULO 1: PANEL ANALÍTICO DE MÉTRICAS (mejorado) -->
    <section id="modulo-analisis" class="<?php echo (($seccion_activa ?? '') === 'analisis') ? 'modulo-seccion activo' : 'modulo-seccion'; ?>">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
          <h3 class="fw-bold text-dark m-0">1. Panel Analítico e Indicadores Generales</h3>
          <p class="text-muted small m-0">Métricas calculadas en tiempo real, distribución territorial e impacto del programa RUPE CDMX.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-outline-primary btn-sm" type="button" onclick="if(window.actualizarAnalisisMetricas)actualizarAnalisisMetricas();">
            <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
          </button>
          <button class="btn btn-outline-secondary btn-sm" type="button" id="btnExportarCsvAnalisis" onclick="if(window.exportarResumenCSV)exportarResumenCSV();return false;">
            <i class="bi bi-filetype-csv me-1"></i> Excel / CSV
          </button>
          <button class="btn btn-outline-danger btn-sm" type="button" id="btnExportarPdfAnalisis" onclick="if(window.exportarPdfAnalitico)exportarPdfAnalitico();return false;">
            <i class="bi bi-file-earmark-pdf me-1"></i> PDF análisis
          </button>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
          <div class="card shadow-sm p-3 border-start border-4 border-primary h-100">
            <span class="text-muted small fw-bold">TOTAL REGISTRADOS</span>
            <h3 class="fw-bold my-1 text-primary" id="kpiTotalRegistros">0</h3>
            <span class="text-muted small"><i class="bi bi-folder-check text-primary me-1"></i>Base de datos general</span>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card shadow-sm p-3 border-start border-4 border-success h-100">
            <span class="text-muted small fw-bold">TASA LOCALIZACIÓN</span>
            <h3 class="fw-bold my-1 text-success" id="kpiTasaLocalizacion">0%</h3>
            <span class="text-muted small" id="kpiTasaDetalle"><i class="bi bi-graph-up text-success me-1"></i>0 de 0 casos</span>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card shadow-sm p-3 border-start border-4 border-warning h-100">
            <span class="text-muted small fw-bold">EN RESGUARDO</span>
            <h3 class="fw-bold my-1 text-warning" id="kpiEnResguardo">0</h3>
            <span class="text-muted small" id="kpiResguardoPct"><i class="bi bi-house-door text-warning me-1"></i>0% del total</span>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card shadow-sm p-3 border-start border-4 border-danger h-100">
            <span class="text-muted small fw-bold">ALERTAS ACTIVAS</span>
            <h3 class="fw-bold my-1 text-danger" id="kpiAlertasActivas">0</h3>
            <span class="text-muted small" id="kpiAlertasPct"><i class="bi bi-broadcast text-danger me-1"></i>0% del total</span>
          </div>
        </div>
      </div>

      
      <!-- Filtros analíticos -->
      <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
          <div class="row g-2 align-items-end">
            <div class="col-md-4">
              <label class="form-label small fw-semibold mb-1">Alcaldía</label>
              <select id="filtroAnaliticoAlcaldia" class="form-select form-select-sm">
                <option value="">Todas las alcaldías</option>
                <?php if (!empty($alcaldias) && is_array($alcaldias)): foreach ($alcaldias as $al):
                  $an = htmlspecialchars((string)($al['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
                  if ($an === '') continue;
                ?>
                  <option value="<?= $an ?>"><?= $an ?></option>
                <?php endforeach; endif; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold mb-1">Estatus</label>
              <select id="filtroAnaliticoEstatus" class="form-select form-select-sm">
                <option value="">Todos los estatus</option>
                <option value="En Búsqueda">En Búsqueda</option>
                <option value="En Resguardo">En Resguardo</option>
                <option value="Localizado">Localizado</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold mb-1">Especie</label>
              <select id="filtroAnaliticoEspecie" class="form-select form-select-sm">
                <option value="">Todas las especies</option>
                <option value="perro">Perro</option>
                <option value="gato">Gato</option>
                <option value="otro">Otro</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-guinda btn-sm w-100" id="btnFiltrarAnalitico" onclick="if(window.aplicarFiltrosAnalitico){aplicarFiltrosAnalitico();}return false;">
                <i class="bi bi-funnel me-1"></i> Filtrar
              </button>
            </div>
          </div>
        </div>
      </div>

<div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card shadow-sm p-3 border-start border-4 border-info h-100">
            <span class="text-muted small fw-bold">TOTAL AVISTAMIENTOS</span>
            <h3 class="fw-bold my-1 text-info" id="kpiTotalAvistamientos">0</h3>
            <span class="text-muted small" id="kpiPromAvist"><i class="bi bi-geo-alt text-info me-1"></i>Prom. 0 por caso</span>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card shadow-sm p-3 border-start border-4 h-100" style="border-color:#6f42c1 !important;">
            <span class="text-muted small fw-bold">CON MICROCHIP / TATOO</span>
            <h3 class="fw-bold my-1" style="color:#6f42c1;" id="kpiConChip">0%</h3>
            <span class="text-muted small" id="kpiChipDetalle"><i class="bi bi-cpu me-1" style="color:#6f42c1;"></i>0 de 0 identificados</span>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card shadow-sm p-3 border-start border-4 h-100" style="border-color:#fd7e14 !important;">
            <span class="text-muted small fw-bold">SIN AVISTAMIENTOS</span>
            <h3 class="fw-bold my-1" style="color:#fd7e14;" id="kpiSinAvist">0</h3>
            <span class="text-muted small"><i class="bi bi-exclamation-triangle me-1" style="color:#fd7e14;"></i>Casos en búsqueda sin reportes</span>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card shadow-sm p-3 border-start border-4 h-100" style="border-color:#20c997 !important;">
            <span class="text-muted small fw-bold">TASA DE RESGUARDO</span>
            <h3 class="fw-bold my-1" style="color:#20c997;" id="kpiTasaResguardo">0%</h3>
            <span class="text-muted small"><i class="bi bi-house-heart me-1" style="color:#20c997;"></i>Resguardo + Localizados</span>
          </div>
        </div>
      </div>

      <div class="row g-4 mb-4">
        <div class="col-lg-4">
          <div class="card shadow-sm p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart-fill text-danger me-2"></i>Distribución por Estatus</h6>
            <div class="chart-container"><canvas id="chartEstatus"></canvas></div>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="card shadow-sm p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Reportes por Alcaldía de CDMX</h6>
            <div class="chart-container"><canvas id="chartAlcaldias"></canvas></div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-gender-ambiguous text-primary me-2"></i>Distribución por Sexo</h6>
            <div class="chart-container"><canvas id="chartSexo"></canvas></div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-cpu-fill me-2" style="color:#6f42c1;"></i>Identificación (Chip / Tatuaje)</h6>
            <div class="chart-container"><canvas id="chartChip"></canvas></div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-bounding-box-circles text-success me-2"></i>Por Tamaño de Mascota</h6>
            <div class="chart-container"><canvas id="chartTamanos"></canvas></div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card shadow-sm p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-trophy-fill text-warning me-2"></i>Top razas más reportadas</h6>
            <div class="chart-container" style="height: 240px;"><canvas id="chartRazas"></canvas></div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card shadow-sm p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-calendar3 text-info me-2"></i>Extravíos por día (últimos registros)</h6>
            <div class="chart-container" style="height: 240px;"><canvas id="chartFechas"></canvas></div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-7">
          <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
              <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Ranking por alcaldía (tasa de resolución)</h6>
              <span class="badge bg-light text-dark border small">Ordenado por % localizados</span>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-sm">
                  <thead class="bg-light">
                    <tr>
                      <th class="ps-3">#</th>
                      <th>Alcaldía</th>
                      <th class="text-center">Casos</th>
                      <th class="text-center">Búsqueda</th>
                      <th class="text-center">Resguardo</th>
                      <th class="text-center">Localizados</th>
                      <th class="text-end pe-3">Tasa res.</th>
                    </tr>
                  </thead>
                  <tbody id="tbodyRankingAlcaldias"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
              <h6 class="fw-bold mb-0"><i class="bi bi-lightbulb-fill text-warning me-2"></i>Insights automáticos</h6>
            </div>
            <div class="card-body">
              <ul class="list-unstyled mb-0" id="listaInsights">
                <li class="text-muted small">Calculando indicadores…</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- MÓDULO 2: CAPTURA COMPLETA DE 15 CAMPOS -->
    <section id="modulo-registro" class="<?php echo (($seccion_activa ?? '') === 'registro') ? 'modulo-seccion activo' : 'modulo-seccion'; ?>">
      <div class="row justify-content-center">
        <div class="col-xxl-10 col-xl-11">
          <div class="card shadow-sm p-4">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <h4 class="fw-bold text-dark m-0"><i class="bi bi-pencil-square me-2 text-danger"></i>2. Registro Completo del Extravío (con Foto y Contacto del Tutor)</h4>
                <p class="text-muted small m-0">Registro 100% gratuito. Ingresa los 15 campos, sube la fotografía y tus datos de contacto para generar la ficha oficial.</p>
              </div>
              <span class="badge bg-success">Acceso Gratuito Tutor</span>
            </div>

            <!-- BARRA DE PROGRESO DEL FORMULARIO -->
            <div class="rupe-form-progreso mb-4">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-semibold text-muted"><i class="bi bi-list-check me-1"></i>Progreso del registro</span>
                <span class="small fw-bold" id="regProgresoTexto" style="color: var(--color-primary);">0 de 15 campos</span>
              </div>
              <div class="progress">
                <div class="progress-bar" id="regProgresoBarra" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>

            <form id="formCapturaPerro" onsubmit="procesarRegistroTutor(event)" novalidate>

              <!-- ===== SECCIÓN: DATOS DE LA MASCOTA (grid de 4) ===== -->
              <h6 class="rupe-form-seccion-titulo mt-0"><i class="bi bi-heart-fill"></i>Datos de la mascota</h6>
              <div class="row g-3 mb-1">
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">1</span>Nombre de la Mascota</label>
                  <input type="text" id="regNombre" name="nombre" class="form-control" placeholder="Ej. Apolo" required>
                </div>
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">2</span>Raza / Mezcla</label>
                  <select id="regRaza" name="id_raza" class="form-select" required>
                    <option value="">Selecciona raza…</option>
                    <?php if (!empty($razas) && is_array($razas)): ?>
                      <?php foreach ($razas as $rz): ?>
                        <option value="<?= (int)($rz['id_raza'] ?? 0) ?>"><?= htmlspecialchars((string)($rz['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?><?= !empty($rz['especie']) ? ' (' . htmlspecialchars((string)$rz['especie'], ENT_QUOTES, 'UTF-8') . ')' : '' ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">3</span>Especie / Animal</label>
                  <select id="regEspecie" name="especie" class="form-select" required>
                    <option value="perro">Perro</option>
                  </select>
                </div>
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">4</span>Edad Aprox. (Años)</label>
                  <input type="number" id="regEdad" name="edad_aprox" class="form-control" min="0" max="25" placeholder="Ej. 3" required>
                </div>
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">5</span>Sexo</label>
                  <select id="regSexo" name="sexo" class="form-select" required>
                    <option value="macho">Macho</option>
                    <option value="hembra">Hembra</option>
                  </select>
                </div>
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">6</span>Tamaño</label>
                  <select id="regTamano" name="tamano" class="form-select" required>
                    <option value="Pequeño">Pequeño</option>
                    <option value="Mediano">Mediano</option>
                    <option value="Grande">Grande</option>
                    <option value="Gigante">Gigante</option>
                  </select>
                </div>
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">7</span>Color Predominante</label>
                  <select id="regColor" name="color" class="form-select" required>
                    <option value="">Selecciona…</option>
                    <option value="Negro">Negro</option>
                    <option value="Blanco">Blanco</option>
                    <option value="Café">Café</option>
                    <option value="Dorado">Dorado</option>
                    <option value="Gris">Gris</option>
                    <option value="Crema">Crema</option>
                    <option value="Atigrado">Atigrado</option>
                    <option value="Manchado">Manchado</option>
                    <option value="Negro con blanco">Negro con blanco</option>
                    <option value="Blanco con negro">Blanco con negro</option>
                    <option value="Café con blanco">Café con blanco</option>
                    <option value="Dorado con blanco">Dorado con blanco</option>
                    <option value="Gris con blanco">Gris con blanco</option>
                    <option value="Rojo / Canela">Rojo / Canela</option>
                    <option value="Chocolate">Chocolate</option>
                    <option value="Beige">Beige</option>
                    <option value="Plateado">Plateado</option>
                    <option value="Merle">Merle</option>
                    <option value="Tricolor">Tricolor</option>
                    <option value="Bicolor negro-café">Bicolor negro-café</option>
                    <option value="Arena">Arena</option>
                    <option value="Naranja (gato)">Naranja (gato)</option>
                    <option value="Carey (gato)">Carey (gato)</option>
                    <option value="Calico (gato)">Calico (gato)</option>
                    <option value="Azul grisáceo">Azul grisáceo</option>
                    <option value="Carbón">Carbón</option>
                    <option value="Blanco con manchas café">Blanco con manchas café</option>
                    <option value="Negro sólido">Negro sólido</option>
                    <option value="Blanco sólido">Blanco sólido</option>
                    <option value="Otro / Mezcla de colores">Otro / Mezcla de colores</option>
                  </select>
                </div>
              </div>

              <!-- ===== SECCIÓN: DETALLES DEL EXTRAVÍO (grid de 4) ===== -->
              <h6 class="rupe-form-seccion-titulo"><i class="bi bi-geo-alt-fill"></i>Detalles del extravío</h6>
              <div class="row g-3 mb-1">
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">8</span>Fecha del Extravío</label>
                  <input type="date" id="regFecha" name="fecha_extravio" class="form-control" value="2026-08-20" required>
                </div>
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">9</span>Hora Aprox. Extravío</label>
                  <input type="time" id="regHora" class="form-control" value="10:30" required>
                </div>
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">10</span>Alcaldía del Suceso</label>
                  <select id="regAlcaldia" name="id_alcaldia" class="form-select" required>
                    <option value="">Selecciona alcaldía…</option>
                    <?php if (!empty($alcaldias) && is_array($alcaldias)): ?>
                      <?php foreach ($alcaldias as $al): ?>
                        <option value="<?= (int)($al['id_alcaldia'] ?? 0) ?>"><?= htmlspecialchars((string)($al['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                  <div class="form-text" id="regAlcaldiaAuto"></div>
                </div>
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">11</span>¿Llevaba Collar / Pechera?</label>
                  <select id="regCollar" name="collar" class="form-select" required>
                    <option value="Sí, collar rojo con placa">Sí, collar rojo con placa</option>
                    <option value="Sí, pechera negra sin placa">Sí, pechera negra sin placa</option>
                    <option value="No llevaba accesorio">No llevaba accesorio</option>
                  </select>
                </div>
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">12</span>¿Cuenta con Microchip / Tatuaje?</label>
                  <select id="regChip" name="microchip" class="form-select" required>
                    <option value="Sí, cuenta con Microchip">Sí, cuenta con Microchip</option>
                    <option value="Sí, tiene Tatuaje">Sí, tiene Tatuaje</option>
                    <option value="No posee">No posee</option>
                  </select>
                </div>
                <div class="col-xl-6 col-md-8">
                  <label class="form-label fw-semibold small"><span class="num-badge">13</span>Colonia / Calle / Referencia</label>
                  <select id="regColonia" name="colonia" class="form-select" required>
                    <option value="">Selecciona…</option>
                    <option value="Roma Norte">Roma Norte</option>
                    <option value="Roma Sur">Roma Sur</option>
                    <option value="Condesa">Condesa</option>
                    <option value="Hipódromo">Hipódromo</option>
                    <option value="Del Valle Centro">Del Valle Centro</option>
                    <option value="Narvarte">Narvarte</option>
                    <option value="Coyoacán Centro">Coyoacán Centro</option>
                    <option value="Pedregal">Pedregal</option>
                    <option value="San Ángel">San Ángel</option>
                    <option value="Polanco">Polanco</option>
                    <option value="Anzures">Anzures</option>
                    <option value="Lindavista">Lindavista</option>
                    <option value="Industrial">Industrial</option>
                    <option value="Doctores">Doctores</option>
                    <option value="Juárez">Juárez</option>
                    <option value="Centro Histórico">Centro Histórico</option>
                    <option value="Santa María la Ribera">Santa María la Ribera</option>
                    <option value="Tacubaya">Tacubaya</option>
                    <option value="Mixcoac">Mixcoac</option>
                    <option value="Portales">Portales</option>
                    <option value="Villa Coapa">Villa Coapa</option>
                    <option value="Culhuacán">Culhuacán</option>
                    <option value="San Miguel (Iztapalapa)">San Miguel (Iztapalapa)</option>
                    <option value="Agrícola Oriental">Agrícola Oriental</option>
                    <option value="Jardín Balbuena">Jardín Balbuena</option>
                    <option value="Xochimilco Centro">Xochimilco Centro</option>
                    <option value="Tlalpan Centro">Tlalpan Centro</option>
                    <option value="Copilco">Copilco</option>
                    <option value="Universidad">Universidad</option>
                    <option value="Otro / Otra colonia">Otro / Otra colonia</option>
                  </select>
                  <input type="text" id="regReferencia" name="direccion_ref" class="form-control form-control-sm mt-1" placeholder="Calle / número / referencia (opcional)">
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold small d-flex justify-content-between">
                    <span><span class="num-badge">14</span>Señas Particulares y Marcaje Único</span>
                    <span class="text-muted fw-normal" id="regSenasContador">0 / 220</span>
                  </label>
                  <textarea id="regSenas" name="senas_particulares" class="form-control" rows="2" maxlength="220"
                            placeholder="Ej. Mancha blanca en forma de estrella en la pata delantera izquierda."
                            oninput="actualizarContadorSenas()" required></textarea>
                </div>
              </div>

              <!-- ===== SECCIÓN: FOTOGRAFÍA (dropzone) ===== -->
              <h6 class="rupe-form-seccion-titulo"><i class="bi bi-camera-fill"></i>Fotografía oficial</h6>
              <div class="row g-3 mb-1">
                <div class="col-lg-6">
                  <label class="form-label fw-semibold small"><span class="num-badge">15</span>Fotografía Oficial</label>
                  <div class="rupe-dropzone" id="regDropzone" onclick="document.getElementById('regFoto').click()"
                       ondragover="event.preventDefault(); this.classList.add('dragover')"
                       ondragleave="this.classList.remove('dragover')"
                       ondrop="manejarSoltarFoto(event)">
                    <i class="bi bi-cloud-arrow-up-fill d-block mb-1"></i>
                    <div class="fw-semibold small" id="regDropzoneTexto">Arrastra una foto aquí o haz clic para elegirla</div>
                    <div class="text-muted" style="font-size: 0.72rem;">JPG o PNG. Si no subes ninguna, se usará una imagen de referencia.</div>
                  </div>
                  <input type="file" id="regFoto" name="foto" class="d-none" accept="image/*" onchange="if(window.previsualizarFotoRegistro)previsualizarFotoRegistro(this)">
                </div>
                <div class="col-lg-6">
                  <label class="form-label fw-semibold small">Vista previa de la fotografía</label>
                  <img id="regFotoPreview" class="preview-foto d-block" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200'%3E%3Crect fill='%23f0f0f0' width='400' height='200'/%3E%3Ctext fill='%23999' x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle'%3ESin foto%3C/text%3E%3C/svg%3E" alt="Vista previa">
                </div>
              </div>

              <!-- ===== SECCIÓN: DATOS DE CONTACTO DEL TUTOR (grid de 4) ===== -->
              <h6 class="rupe-form-seccion-titulo"><i class="bi bi-person-lines-fill"></i>Datos de contacto del tutor</h6>
              <div class="form-text mb-2" id="regTutorAuto"></div>
              <div class="row g-3">
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small">Nombre completo del tutor</label>
                  <input type="text" id="regTutorNombre" class="form-control" required placeholder="Nombre y apellidos">
                </div>
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small">Teléfono / WhatsApp</label>
                  <input type="tel" id="regTutorTel" class="form-control" required placeholder="55 1234 5678">
                </div>
                <div class="col-xl-3 col-md-6">
                  <label class="form-label fw-semibold small">Correo electrónico</label>
                  <input type="email" id="regTutorEmail" class="form-control" required placeholder="correo@ejemplo.com">
                </div>
                <div class="col-xl-3 col-md-6 d-flex align-items-center">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="regPrivacidad" required>
                    <label class="form-check-label small" for="regPrivacidad">
                      Acepto que mis datos de contacto se usen solo para notificaciones de avistamiento (no se publican abiertamente).
                    </label>
                  </div>
                </div>
              </div>

              <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary px-4" style="background-color: var(--color-primary); border:none;">
                  <i class="bi bi-save me-1"></i> Guardar Gratis y Generar Folio
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- MÓDULO 3: TABLA CRUD -->
    <section id="modulo-crud" class="<?php echo (($seccion_activa ?? '') === 'crud') ? 'modulo-seccion activo' : 'modulo-seccion'; ?>">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h3 class="fw-bold text-dark m-0">3. Módulo CRUD - Registros del Tutor</h3>
          <p class="text-muted small m-0">Gestión general de folios asignados con opciones para modificar, consultar o dar de baja.</p>
        </div>
        <input type="text" id="inputBuscadorCrud" class="form-control form-control-sm w-auto" placeholder="Buscar por folio o nombre..." onkeyup="filtrarTablaCrud()">
      </div>

      <div class="card shadow-sm">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaCrudTutor">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">Folio Asignado</th>
                  <th>Foto</th>
                  <th>Mascota</th>
                  <th>Raza</th>
                  <th>Alcaldía</th>
                  <th>Colonia</th>
                  <th>Calle</th>
                  <th>Referencia</th>
                  <th>Fecha Extravío</th>
                  <th>Estatus</th>
                  <th class="text-end pe-4">Acciones CRUD</th>
                </tr>
              </thead>
              <tbody id="tbodyCrud">
                <?php if (!empty($mascotas) && is_array($mascotas)): ?>
                  <?php foreach ($mascotas as $i => $m):
                    $folio  = htmlspecialchars((string)($m['folio'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $nombre = htmlspecialchars((string)(($m['nombre'] ?? '') !== '' ? $m['nombre'] : '—'), ENT_QUOTES, 'UTF-8');
                    $raza   = htmlspecialchars((string)(($m['raza'] ?? $m['raza_nombre'] ?? '') !== '' ? ($m['raza'] ?? $m['raza_nombre']) : '—'), ENT_QUOTES, 'UTF-8');
                    $alc    = htmlspecialchars((string)(($m['alcaldia'] ?? $m['alcaldia_nombre'] ?? '') !== '' ? ($m['alcaldia'] ?? $m['alcaldia_nombre']) : '—'), ENT_QUOTES, 'UTF-8');
                    $coloniaRaw = trim((string)($m['colonia'] ?? ''));
                    $dirRef = trim((string)($m['direccion_ref'] ?? $m['referencia'] ?? ''));
                    $calleRaw = trim((string)($m['calle'] ?? ''));
                    $refRaw = '';
                    if ($calleRaw === '' && $dirRef !== '') {
                        if (preg_match('/Calle:\s*([^|]+)/iu', $dirRef, $mm)) {
                            $calleRaw = trim($mm[1]);
                        }
                        if (preg_match('/Ref(?:erencia)?:\s*(.+)$/iu', $dirRef, $mm2)) {
                            $refRaw = trim($mm2[1]);
                        } elseif (strpos($dirRef, ',') !== false && $calleRaw === '') {
                            $parts = array_map('trim', explode(',', $dirRef, 2));
                            $calleRaw = $parts[0] ?? '';
                            $refRaw = $parts[1] ?? '';
                        } elseif ($calleRaw === '') {
                            $refRaw = $dirRef;
                        }
                    } elseif ($dirRef !== '' && $refRaw === '') {
                        if (preg_match('/Ref(?:erencia)?:\s*(.+)$/iu', $dirRef, $mm3)) {
                            $refRaw = trim($mm3[1]);
                        } else {
                            $refRaw = $dirRef;
                        }
                    }
                    $colonia = htmlspecialchars(($coloniaRaw !== '' && strcasecmp($coloniaRaw, 'null') !== 0) ? $coloniaRaw : '—', ENT_QUOTES, 'UTF-8');
                    $calle = htmlspecialchars(($calleRaw !== '' && strcasecmp($calleRaw, 'null') !== 0) ? $calleRaw : '—', ENT_QUOTES, 'UTF-8');
                    $referencia = htmlspecialchars(($refRaw !== '' && strcasecmp($refRaw, 'null') !== 0) ? $refRaw : '—', ENT_QUOTES, 'UTF-8');
                    $fecha  = htmlspecialchars((string)(($m['fecha'] ?? $m['fecha_extravio'] ?? '') !== '' ? ($m['fecha'] ?? $m['fecha_extravio']) : '—'), ENT_QUOTES, 'UTF-8');
                    $est    = (string)(($m['estatus'] ?? '') !== '' ? $m['estatus'] : 'En Búsqueda');
                    $badge  = $est === 'Localizado' ? 'bg-success' : ($est === 'En Resguardo' ? 'bg-warning text-dark' : 'bg-danger');
                    $estH   = htmlspecialchars($est, ENT_QUOTES, 'UTF-8');
                    $foto   = (string)($m['foto'] ?? $m['foto_url'] ?? '');
                    if ($foto === '' || strcasecmp($foto, 'null') === 0) {
                      $foto = function_exists('mascota_foto_src')
                        ? mascota_foto_src(null, (string)($m['especie'] ?? 'perro'))
                        : '/assets/img/placeholder-perro.svg';
                    }
                    $foto = htmlspecialchars($foto, ENT_QUOTES, 'UTF-8');
                    $idm = (int)($m['id'] ?? $m['id_mascota'] ?? 0);
                  ?>
                  <tr data-id="<?= $idm ?>" data-folio="<?= $folio ?>">
                    <td class="ps-4 fw-bold text-danger">#<?= $folio ?></td>
                    <td><img src="<?= $foto ?>" alt="<?= $nombre ?>" width="48" height="48" class="rounded border" style="width:48px;height:48px;object-fit:cover;background:#f0f0f0;display:block;" onerror="this.onerror=null;this.src='/assets/img/placeholder-mascota.svg'"></td>
                    <td><strong><?= $nombre ?></strong></td>
                    <td><?= $raza ?></td>
                    <td><?= $alc ?></td>
                    <td class="small"><?= $colonia ?></td>
                    <td class="small"><?= $calle ?></td>
                    <td class="small"><?= $referencia ?></td>
                    <td><?= $fecha ?></td>
                    <td><span class="badge <?= $badge ?>"><?= $estH ?></span></td>
                    <td class="text-end pe-4 text-nowrap">
                      <button type="button" class="btn btn-sm btn-outline-primary me-1" title="Ver" data-crud="ver" data-index="<?= (int)$i ?>"><i class="bi bi-eye"></i></button>
                      <button type="button" class="btn btn-sm btn-outline-secondary me-1" title="Editar" data-crud="editar" data-index="<?= (int)$i ?>"><i class="bi bi-pencil"></i></button>
                      <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" data-crud="eliminar" data-index="<?= (int)$i ?>"><i class="bi bi-trash"></i></button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="11" class="text-center text-muted py-4">Sin registros. Crea el primero en el módulo Registro.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- MÓDULO 4: FICHA PDF CON QR -->
    <section id="modulo-ficha-pdf" class="<?php echo (($seccion_activa ?? '') === 'ficha-pdf') ? 'modulo-seccion activo' : 'modulo-seccion'; ?>">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
          <h3 class="fw-bold text-dark m-0">4. Ficha Oficial de Búsqueda + Código QR</h3>
          <p class="text-muted small m-0">Muestra solo las características del animal, sin revelar datos privados del tutor. Se genera automáticamente al registrar un extravío.</p>
        </div>
        <div class="d-flex gap-2 d-print-none">
          <select id="tutorSelectFolioFicha" class="form-select form-select-sm" style="width:auto" onchange="cambiarFolioFichaTutor()">
            <option value="">— Sin registros —</option>
          </select>
          <button class="btn btn-outline-guinda btn-sm" onclick="imprimirFichaTutor()">
            <i class="bi bi-printer me-1"></i> Imprimir / PDF
          </button>
        </div>
      </div>

      <div id="tutorContenedorFichaVacia" class="card shadow-sm p-5 text-center text-muted">
        <i class="bi bi-file-earmark-pdf display-4 d-block mb-3"></i>
        <p class="mb-0">Aún no hay fichas. Registra un extravío en el módulo 2 para generar la ficha oficial con QR.</p>
      </div>

      
      <div id="tutorContenedorFicha" class="d-none">
        <div class="ficha-oficial shadow-sm" id="tutorFichaParaImprimir">
          <!-- Cabecera -->
          <div class="ficha-header d-flex justify-content-between align-items-start border-bottom border-2 pb-3 mb-3" style="border-color: var(--color-primary) !important;">
            <div>
              <h4 class="fw-bold mb-0" style="color: var(--color-primary);">REGISTRO ÚNICO DE PERROS EXTRAVIADOS</h4>
              <small class="text-muted">Ficha oficial de búsqueda · RUPE CDMX · Tamaño carta</small>
            </div>
            <div class="text-end">
              <span class="badge bg-danger fs-6" id="tutorFichaFolio">—</span>
              <div class="small text-muted mt-1" id="tutorFichaFechaGen"></div>
            </div>
          </div>

          <!-- Cuerpo alineado: foto | datos | QR -->
          <div class="row g-3 ficha-cuerpo align-items-start">
            <div class="col-md-3 text-center">
              <img id="tutorFichaFoto" class="img-fluid rounded-3 shadow-sm mb-2 ficha-foto"
                   style="max-height:200px;object-fit:cover;width:100%;background:#f0f0f0;"
                   src="/assets/img/placeholder-perro.svg" alt="Foto mascota"
                   onerror="this.onerror=null;this.src='/assets/img/placeholder-mascota.svg'">
              <h4 class="fw-bold mb-0 fs-5" id="tutorFichaNombre">—</h4>
              <div class="text-muted small" id="tutorFichaRaza">—</div>
            </div>
            <div class="col-md-6">
              <table class="table table-sm table-borderless mb-0 ficha-datos">
                <tbody>
                  <tr><th class="text-muted fw-semibold" style="width:42%">Sexo / Tamaño</th><td id="tutorFichaSexoTamano">—</td></tr>
                  <tr><th class="text-muted fw-semibold">Edad</th><td id="tutorFichaEdad">—</td></tr>
                  <tr><th class="text-muted fw-semibold">Color</th><td id="tutorFichaColor">—</td></tr>
                  <tr><th class="text-muted fw-semibold">Alcaldía / Colonia</th><td id="tutorFichaLugar">—</td></tr>
                  <tr><th class="text-muted fw-semibold">Fecha extravío</th><td id="tutorFichaFecha">—</td></tr>
                  <tr><th class="text-muted fw-semibold">Collar</th><td id="tutorFichaCollar">—</td></tr>
                  <tr><th class="text-muted fw-semibold">Microchip / tatuaje</th><td id="tutorFichaMicrochip">—</td></tr>
                  <tr><th class="text-muted fw-semibold">Estatus</th><td id="tutorFichaEstatus">—</td></tr>
                  <tr><th class="text-muted fw-semibold">GPS (último)</th><td id="tutorFichaGps">—</td></tr>
                </tbody>
              </table>
              <div class="mt-2">
                <div class="text-muted fw-semibold small">Señas particulares</div>
                <p class="small mb-0" id="tutorFichaSenas">—</p>
              </div>
              <div class="mt-2 d-print-none">
                <button type="button" class="btn btn-sm btn-outline-success" id="btnCapturarGpsFicha" onclick="capturarGpsParaFichaActual()">
                  <i class="bi bi-crosshair me-1"></i> Capturar GPS y guardar en esta ficha
                </button>
                <span class="small text-muted ms-2" id="gpsFichaStatus"></span>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <div class="qr-box border rounded-3 p-2 bg-white d-inline-block">
                <img id="tutorFichaQR" src="" alt="Código QR" width="130" height="130" class="mb-1">
                <div class="small fw-bold" style="color:#206A5D;">Escanea para avistar</div>
                <div class="small text-muted" id="tutorFichaEnlaceQR" style="word-break:break-all;font-size:0.6rem;max-width:140px;"></div>
              </div>
            </div>
          </div>

          <!-- Mapa GPS (pantalla + PDF) -->
          <div id="tutorFichaMapaWrap" class="mt-3">
            <p class="mb-1 small fw-semibold"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Mapa GPS (último)</p>
            <div id="mapaFichaTutor" class="d-print-none" style="height:220px;border-radius:10px;overflow:hidden;border:1px solid #dee2e6;background:#e9ecef;"></div>
            <!-- Imagen estática del mapa para impresión PDF (Leaflet no imprime bien) -->
            <img id="tutorFichaMapaStatic" class="d-none d-print-block img-fluid rounded border"
                 alt="Mapa GPS" style="width:100%;max-height:200px;object-fit:cover;background:#e9ecef;">
            <p class="small text-muted mt-1 mb-0" id="tutorFichaGpsNota"></p>
          </div>

          <div class="mt-3 pt-2 border-top small text-muted text-center">
            Documento generado por RUPE CDMX · Tamaño carta (Letter 8.5 × 11 in)
          </div>
        </div>
      </div>

    </section>

    <!-- MÓDULO 5: REPORTE CIUDADANO POR QR / GPS -->
    <section id="modulo-gps-qr" class="<?php echo (($seccion_activa ?? '') === 'gps-qr') ? 'modulo-seccion activo' : 'modulo-seccion'; ?>">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="card shadow-sm p-4 border-start border-4 border-success">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <span class="badge bg-success mb-1"><i class="bi bi-geo-fill me-1"></i> REPORTE CIUDADANO POR QR</span>
                <h4 class="fw-bold text-dark m-0">5. Registro de Avistamiento / Foto / Ubicación GPS</h4>
              </div>
              <button class="btn btn-sm btn-outline-success" type="button" id="btnCapturarGps" onclick="capturarUbicacionGPS(); return false;">
                <i class="bi bi-crosshair me-1"></i> Capturar mi Ubicación GPS
              </button>
            </div>

            <form id="formGpsAvistamiento" novalidate onsubmit="if(window.enviarReporteGPS){enviarReporteGPS(event);} return false;">
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold small">Folio Escaneado</label>
                  <select id="gpsFolio" class="form-select" onchange="if(window.sincronizarGpsPorFolio)sincronizarGpsPorFolio()" required>
                    <option value="">— Selecciona folio —</option>
                    <?php if (!empty($mascotas) && is_array($mascotas)): foreach ($mascotas as $gm):
                      $gf = htmlspecialchars((string)($gm['folio'] ?? ''), ENT_QUOTES, 'UTF-8');
                      $gn = htmlspecialchars((string)($gm['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
                      $ge = htmlspecialchars((string)($gm['estatus'] ?? ''), ENT_QUOTES, 'UTF-8');
                      $gid = (int)($gm['id'] ?? $gm['id_mascota'] ?? 0);
                      if ($gf === '') continue;
                    ?>
                      <option value="<?= $gf ?>" data-id="<?= $gid ?>">#<?= $gf ?> · <?= $gn ?><?= $ge !== '' ? ' · ' . $ge : '' ?></option>
                    <?php endforeach; endif; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold small">Condición del Animal</label>
                  <select id="gpsCondicion" class="form-select" required>
                    <option value="Visto en la calle">Observado / Libre en calle</option>
                    <option value="Resguardado">Resguardado en domicilio/veterinaria/albergue</option>
                  </select>
                </div>
              </div>

              <!-- Tarjeta de contexto del folio seleccionado -->
              <div id="gpsContextoFolio" class="alert alert-light border d-flex align-items-center gap-3 mb-2 d-none"></div>
              <div id="gpsAlertaLocalizado" class="alert alert-warning py-2 mb-3 d-none">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Este folio ya está marcado como <strong>Localizado</strong>. Confirma con el tutor antes de agregar otro avistamiento.
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-7">
                  <label class="form-label fw-semibold small">Fotografía del Avistamiento</label>
                  <input type="file" id="gpsFotoArchivo" class="form-control mb-2" accept="image/*" onchange="if(window.previsualizarFotoAvistamiento)previsualizarFotoAvistamiento(this)">
                  <img id="gpsFotoPreview" class="preview-foto d-block" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200'%3E%3Crect fill='%23f0f0f0' width='400' height='200'/%3E%3Ctext fill='%23999' x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle'%3ESin foto%3C/text%3E%3C/svg%3E" alt="Vista previa">
                  <div class="form-text">Opcional: si no subes foto, se usa la foto oficial registrada del animal.</div>
                </div>
                <div class="col-md-5">
                  <label class="form-label fw-semibold small">Referencia del Lugar</label>
                  <input type="text" id="gpsReferencia" class="form-control mb-3" placeholder="Ej. Parque México, cerca de la fuente principal" maxlength="200" minlength="3" title="3 a 200 caracteres">
                  <label class="form-label fw-semibold small">Contacto de quien reporta <span class="text-muted fw-normal">(opcional)</span></label>
                  <input type="text" id="gpsReporteroNombre" class="form-control mb-2" placeholder="Nombre" maxlength="80" minlength="2" title="2 a 80 letras">
                  <div class="row g-2">
                    <div class="col-6"><input type="tel" id="gpsReporteroTel" class="form-control" placeholder="Teléfono / WhatsApp" maxlength="20" inputmode="tel" pattern="[0-9+\s().\-]{8,20}" title="8 a 15 dígitos"></div>
                    <div class="col-6"><input type="email" id="gpsReporteroEmail" class="form-control" placeholder="Correo (opcional)" maxlength="120" title="Correo válido, máx. 120"></div>
                  </div>
                </div>
              </div>

              <div class="row g-3 mb-3 align-items-end">
                <div class="col-md-4">
                  <label class="form-label fw-semibold small">Buscar dirección en el mapa</label>
                  <div class="input-group">
                    <input type="text" id="gpsBuscarDireccion" class="form-control" placeholder="Ej. Roma Norte, CDMX" maxlength="120" onkeydown="if(event.key==='Enter'){event.preventDefault(); buscarDireccionGPS();}">
                    <button class="btn btn-outline-secondary" type="button" id="btnBuscarDireccionGps" onclick="if(window.buscarDireccionGPS)buscarDireccionGPS();return false;" title="Buscar dirección"><i class="bi bi-search"></i></button>
                  </div>
                  <div class="form-text" id="gpsBuscarDireccionMsg"></div>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold small">Coordenada Latitud GPS</label>
                  <input type="text" id="gpsLatitud" class="form-control bg-light" value="19.4182" readonly>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold small">Coordenada Longitud GPS</label>
                  <input type="text" id="gpsLongitud" class="form-control bg-light" value="-99.1625" readonly>
                </div>
              </div>

              <!-- MAPA LEAFLET -->
              <div class="mb-3">
                <label class="form-label fw-semibold small">
                  <i class="bi bi-map-fill me-1 text-danger"></i> Geolocalización Interactiva
                  <span class="text-muted fw-normal">— arrastra el marcador verde para ajustar el punto exacto. Los puntos rojos son avistamientos previos de este folio.</span>
                </label>
                <div id="mapaGPS"></div>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-link btn-sm text-decoration-none ps-0" onclick="verSeguimientoDesdeGPS()">
                  <i class="bi bi-clock-history me-1"></i> Ver seguimiento completo de este folio
                </button>
                <button type="submit" class="btn btn-success px-4" id="btnEnviarAvistamiento">
                  <i class="bi bi-geo-alt-fill me-1"></i> Enviar Avistamiento al Tutor
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- MÓDULO 6: TRAZABILIDAD Y SEGUIMIENTO -->
    <section id="modulo-seguimiento" class="<?php echo (($seccion_activa ?? '') === 'seguimiento') ? 'modulo-seccion activo' : 'modulo-seccion'; ?>">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
          <h3 class="fw-bold text-dark m-0">6. Trazabilidad de Avistamientos para el Tutor</h3>
          <p class="text-muted small m-0">Consulta en tiempo real de las alertas y fotografías enviadas por la comunidad.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <label class="small text-muted mb-0">Filtrar folio</label>
          <select id="selectFolioSeguimiento" class="form-select form-select-sm" style="min-width:220px;width:auto">
            <option value="">— Selecciona folio —</option>
            <?php if (!empty($mascotas) && is_array($mascotas)): foreach ($mascotas as $sm):
              $sf = htmlspecialchars((string)($sm['folio'] ?? ''), ENT_QUOTES, 'UTF-8');
              $sn = htmlspecialchars((string)($sm['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
              $se = htmlspecialchars((string)($sm['estatus'] ?? ''), ENT_QUOTES, 'UTF-8');
              $sid = (int)($sm['id'] ?? $sm['id_mascota'] ?? 0);
              if ($sf === '') continue;
            ?>
              <option value="<?= $sf ?>" data-id="<?= $sid ?>">#<?= $sf ?> · <?= $sn ?><?= $se !== '' ? ' · ' . $se : '' ?></option>
            <?php endforeach; endif; ?>
          </select>
          <span class="badge bg-light text-dark border p-2" id="badgeSeguimientoFolio">
            <i class="bi bi-shield-check text-success me-1"></i> Folio Activo: <strong>#—</strong>
          </span>
        </div>
      </div>

      <div id="mapaSeguimientoWrap" class="card shadow-sm mb-3 d-none">
        <div class="card-body p-2">
          <p class="small fw-semibold mb-2 px-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Mapa del avistamiento seleccionado</p>
          <div id="mapaSeguimiento" style="height:240px;border-radius:10px;overflow:hidden;background:#e9ecef;"></div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
          <h6 class="fw-bold m-0 text-dark" id="tituloHistorialGps"><i class="bi bi-radar me-2 text-danger"></i>Avistamientos Recibidos</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaSeguimientoGps">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">Fecha / Hora</th>
                  <th>Condición</th>
                  <th>Fotografía Reportada</th>
                  <th>Ubicación GPS</th>
                  <th>Referencia</th>
                  <th>Reportado por</th>
                  <th class="text-end pe-4">Acciones</th>
                </tr>
              </thead>
              <tbody id="tbodySeguimiento">
                <!-- Carga dinámica -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    
      <!-- Modal detalle avistamiento -->
      <div class="modal fade" id="modalDetalleAvistamiento" tabindex="-1" aria-labelledby="modalDetalleAvistamientoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalDetalleAvistamientoLabel">
                <i class="bi bi-eye me-2 text-primary"></i>Detalle del avistamiento
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-4 text-center">
                  <img id="modalSegFoto" src="/assets/img/placeholder-perro.svg" alt="Foto"
                       class="img-fluid rounded border shadow-sm mb-2"
                       style="max-height:220px;width:100%;object-fit:cover;background:#f0f0f0"
                       onerror="this.onerror=null;this.src='/assets/img/placeholder-mascota.svg'">
                  <div class="small text-muted" id="modalSegFolio">—</div>
                </div>
                <div class="col-md-8">
                  <table class="table table-sm table-borderless mb-2">
                    <tr><th class="text-muted" style="width:40%">Fecha / Hora</th><td id="modalSegFecha">—</td></tr>
                    <tr><th class="text-muted">Condición</th><td id="modalSegCondicion">—</td></tr>
                    <tr><th class="text-muted">Ubicación GPS</th><td><code id="modalSegGps">—</code></td></tr>
                    <tr><th class="text-muted">Referencia</th><td id="modalSegReferencia">—</td></tr>
                    <tr><th class="text-muted">Reportado por</th><td id="modalSegReportado">—</td></tr>
                  </table>
                  <div class="small">
                    <span class="text-muted fw-semibold">Descripción completa</span>
                    <p class="mb-0" id="modalSegDesc">—</p>
                  </div>
                </div>
                <div class="col-12">
                  <p class="small fw-semibold mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Mapa del avistamiento</p>
                  <div id="mapaSeguimientoModal" style="height:220px;border-radius:10px;overflow:hidden;border:1px solid #dee2e6;background:#e9ecef;"></div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>

    </section>

    <!-- MÓDULO 7: CONTROL DE ESTATUS -->
    <section id="modulo-estatus" class="<?php echo (($seccion_activa ?? '') === 'estatus') ? 'modulo-seccion activo' : 'modulo-seccion'; ?>">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
          <h3 class="fw-bold mb-0"><i class="bi bi-check2-circle text-success me-2"></i>7. Estatus y Seguimiento de Casos</h3>
          <p class="text-muted small mb-0">Consulta cualquier folio, revisa sus avistamientos y actualiza el estatus oficial al encontrar o resguardar a la mascota.</p>
        </div>
      </div>

      <!-- Buscador / selector de folio -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <div class="row g-2 align-items-end">
            <div class="col-md-6">
              <label class="form-label">Número de folio</label>
              <input type="text" id="tutorInputBuscarFolio" class="form-control" placeholder="Ej. FOL-2026-4821" list="tutorListaFolios">
              <datalist id="tutorListaFolios"></datalist>
            </div>
            <div class="col-md-3">
              <button type="button" class="btn btn-guinda w-100" id="btnConsultarFolioEstatus" onclick="if(window.buscarPorFolioTutor)buscarPorFolioTutor();return false;">
                <i class="bi bi-search me-1"></i> Consultar
              </button>
            </div>
            <div class="col-md-3">
              <select id="tutorSelectFolioRapido" class="form-select" onchange="document.getElementById('tutorInputBuscarFolio').value=this.value; if(window.buscarPorFolioTutor)buscarPorFolioTutor();">
                <option value="">— Folios recientes —</option>
                <?php if (!empty($mascotas) && is_array($mascotas)): foreach ($mascotas as $em):
                  $ef = htmlspecialchars((string)($em['folio'] ?? ''), ENT_QUOTES, 'UTF-8');
                  $en = htmlspecialchars((string)($em['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
                  $ee = htmlspecialchars((string)($em['estatus'] ?? ''), ENT_QUOTES, 'UTF-8');
                  $eid = (int)($em['id'] ?? $em['id_mascota'] ?? 0);
                  if ($ef === '') continue;
                ?>
                  <option value="<?= $ef ?>" data-id="<?= $eid ?>">#<?= $ef ?> · <?= $en ?><?= $ee !== '' ? ' · ' . $ee : '' ?></option>
                <?php endforeach; endif; ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div id="tutorSeguimientoVacio" class="card shadow-sm p-5 text-center text-muted">
        <i class="bi bi-radar display-4 d-block mb-3"></i>
        <p class="mb-0">Ingresa o selecciona un folio para ver el estado del caso, avistamientos y poder actualizar el estatus.</p>
      </div>

      <div id="tutorSeguimientoContenido" class="d-none">
        <!-- Resumen del caso -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="card shadow-sm p-3 kpi-card h-100">
              <span class="text-muted small fw-bold">FOLIO</span>
              <h5 class="fw-bold my-1" id="tutorSegFolio">—</h5>
              <span class="text-muted small" id="tutorSegNombre">—</span>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm p-3 kpi-card h-100">
              <span class="text-muted small fw-bold">ESTATUS ACTUAL</span>
              <h5 class="my-1"><span id="tutorSegEstatusBadge" class="badge">—</span></h5>
              <span class="text-muted small" id="tutorSegUltimaAct">—</span>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm p-3 kpi-card h-100">
              <span class="text-muted small fw-bold">AVISTAMIENTOS</span>
              <h5 class="fw-bold my-1" id="tutorSegNumAvist">0</h5>
              <span class="text-muted small">Reportes ciudadanos</span>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm p-3 kpi-card h-100">
              <span class="text-muted small fw-bold">CONTACTO TUTOR</span>
              <div class="small mt-1" id="tutorSegContactoTutor">—</div>
            </div>
          </div>
        </div>

        <!-- Cambiar estatus -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-check-circle-fill me-2" style="color:var(--color-primary)"></i>Actualizar estatus oficial</h6>
          </div>
          <div class="card-body">
            <form id="tutorFormCambiarEstatus" onsubmit="if(window.cambiarEstatusTutor){cambiarEstatusTutor(event);} return false;">
              <div class="row g-3 align-items-end">
                <div class="col-md-4">
                  <label class="form-label">Nuevo estatus *</label>
                  <select id="tutorNuevoEstatus" class="form-select" required>
                    <option value="En Búsqueda">En Búsqueda (Activo)</option>
                    <option value="En Resguardo">En Resguardo Confirmado</option>
                    <option value="Localizado">¡LOCALIZADO / REUNIFICADO!</option>
                  </select>
                </div>
                <div class="col-md-5">
                  <label class="form-label">Bitácora / comentario *</label>
                  <input type="text" id="tutorEstatusComentario" class="form-control" required placeholder="Ej. El perro fue entregado exitosamente a sus tutores.">
                </div>
                <div class="col-md-3">
                  <button type="submit" class="btn btn-guinda w-100">
                    <i class="bi bi-save me-1"></i> Actualizar estatus
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Lista de avistamientos -->
        <div class="card shadow-sm">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill me-2 text-danger"></i>Avistamientos y reportes</h6></div>
          <div class="card-body p-0">
            <div id="tutorListaAvistamientos" class="p-3">
              <!-- se llena por JS -->
            </div>
          </div>
        </div>
      </div>

      <!-- Vista general de todos los registros -->
      <div class="card shadow-sm mt-4">
        <div class="card-header bg-white py-3">
          <h6 class="fw-bold mb-0"><i class="bi bi-journal-check text-primary me-2"></i>Vista General de Todos los Registros</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive" style="max-height:320px; overflow-y:auto;">
            <table class="table table-sm table-striped align-middle mb-0">
              <thead class="table-light" style="position:sticky; top:0;">
                <tr>
                  <th class="ps-2">Folio</th>
                  <th>Mascota</th>
                  <th>Estatus Actual</th>
                  <th>Bitácora de Seguimiento</th>
                </tr>
              </thead>
              <tbody id="tbodyControlEstatus5">
                <?php if (!empty($mascotas) && is_array($mascotas)): ?>
                  <?php foreach ($mascotas as $m):
                    $est = (string)($m['estatus'] ?? 'En Búsqueda');
                    $badge = $est === 'Localizado' ? 'bg-success' : ($est === 'En Resguardo' ? 'bg-warning text-dark' : 'bg-danger');
                    $bit = (string)($m['bitacora'] ?? $m['notas'] ?? 'Registro en RUPE CDMX');
                    if ($bit === '' || strcasecmp($bit, 'null') === 0) $bit = 'Registro en RUPE CDMX';
                    $folio = (string)($m['folio'] ?? '');
                    $nombre = (string)($m['nombre'] ?? '—');
                    $raza = (string)($m['raza'] ?? $m['raza_nombre'] ?? '');
                  ?>
                  <tr style="cursor:pointer" data-folio="<?= htmlspecialchars($folio, ENT_QUOTES, 'UTF-8') ?>">
                    <td class="ps-2 fw-bold text-danger">#<?= htmlspecialchars($folio, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><strong><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></strong><?php if ($raza !== ''): ?> <span class="text-muted">(<?= htmlspecialchars($raza, ENT_QUOTES, 'UTF-8') ?>)</span><?php endif; ?></td>
                    <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($est, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="small text-muted"><?= htmlspecialchars($bit, ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="4" class="text-center text-muted py-3">Sin registros de seguimiento.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- MÓDULO 8: RED DE ALBERGUES -->
    <section id="modulo-albergues" class="<?php echo (($seccion_activa ?? '') === 'albergues') ? 'modulo-seccion activo' : 'modulo-seccion'; ?>">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
          <h3 class="fw-bold text-dark m-0">8. Red de Albergues y Hogares Temporales</h3>
          <p class="text-muted small m-0">Directorio de refugios aliados y asignación de casos en resguardo a un albergue.</p>
        </div>
        <button class="btn btn-sm btn-outline-primary" type="button" onclick="if(window.mostrarFormAlbergue)mostrarFormAlbergue();return false;">
          <i class="bi bi-plus-lg me-1"></i> Agregar Albergue
        </button>
      </div>

      <!-- KPIs -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card shadow-sm p-3 kpi-card h-100">
            <span class="text-muted small fw-bold">ALBERGUES ACTIVOS</span>
            <h4 class="fw-bold my-1" id="kpiAlberguesTotal">0</h4>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card shadow-sm p-3 kpi-card h-100">
            <span class="text-muted small fw-bold">CAPACIDAD TOTAL</span>
            <h4 class="fw-bold my-1" id="kpiAlberguesCapacidad">0</h4>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card shadow-sm p-3 kpi-card h-100">
            <span class="text-muted small fw-bold">CASOS EN RESGUARDO</span>
            <h4 class="fw-bold my-1" id="kpiAlberguesOcupacion">0</h4>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card shadow-sm p-3 kpi-card h-100">
            <span class="text-muted small fw-bold">SIN ALBERGUE ASIGNADO</span>
            <h4 class="fw-bold my-1 text-danger" id="kpiCasosSinAlbergue">0</h4>
          </div>
        </div>
      </div>

      <!-- Formulario para agregar albergue -->
      
      <!-- Filtros albergues -->
      <div class="card shadow-sm mb-3">
        <div class="card-body py-3">
          <div class="row g-2 align-items-end">
            <div class="col-md-4">
              <label class="form-label small fw-semibold mb-1">Buscar</label>
              <input type="text" id="filtroAlbBuscar" class="form-control form-control-sm" placeholder="Nombre, dirección, teléfono…">
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold mb-1">Alcaldía</label>
              <select id="filtroAlbAlcaldia" class="form-select form-select-sm">
                <option value="">Todas las alcaldías</option>
                  <option value="Álvaro Obregón">Álvaro Obregón</option>
                  <option value="Azcapotzalco">Azcapotzalco</option>
                  <option value="Benito Juárez">Benito Juárez</option>
                  <option value="Coyoacán">Coyoacán</option>
                  <option value="Cuajimalpa de Morelos">Cuajimalpa de Morelos</option>
                  <option value="Cuauhtémoc">Cuauhtémoc</option>
                  <option value="Gustavo A. Madero">Gustavo A. Madero</option>
                  <option value="Iztacalco">Iztacalco</option>
                  <option value="Iztapalapa">Iztapalapa</option>
                  <option value="La Magdalena Contreras">La Magdalena Contreras</option>
                  <option value="Miguel Hidalgo">Miguel Hidalgo</option>
                  <option value="Milpa Alta">Milpa Alta</option>
                  <option value="Tláhuac">Tláhuac</option>
                  <option value="Tlalpan">Tlalpan</option>
                  <option value="Venustiano Carranza">Venustiano Carranza</option>
                  <option value="Xochimilco">Xochimilco</option>
                  <option value="Nezahualcóyotl">Nezahualcóyotl</option>
                  <option value="Ecatepec de Morelos">Ecatepec de Morelos</option>
                  <option value="Tlalnepantla de Baz">Tlalnepantla de Baz</option>
                  <option value="Naucalpan de Juárez">Naucalpan de Juárez</option>
                  <option value="Chimalhuacán">Chimalhuacán</option>
                  <option value="Atizapán de Zaragoza">Atizapán de Zaragoza</option>
                  <option value="Cuautitlán Izcalli">Cuautitlán Izcalli</option>
                  <option value="Valle de Chalco Solidaridad">Valle de Chalco Solidaridad</option>
                  <option value="Ixtapaluca">Ixtapaluca</option>
                  <option value="Tultitlán">Tultitlán</option>
                  <option value="Coacalco de Berriozábal">Coacalco de Berriozábal</option>
                  <option value="Huixquilucan">Huixquilucan</option>
                  <option value="Texcoco">Texcoco</option>
                  <option value="Nicolás Romero">Nicolás Romero</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold mb-1">Tipo</label>
              <select id="filtroAlbTipo" class="form-select form-select-sm">
                <option value="">Todos los tipos</option>
                <option value="Albergue Oficial">Albergue Oficial</option>
                <option value="Refugio Independiente">Refugio Independiente</option>
                <option value="Hogar Temporal">Hogar Temporal</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-guinda btn-sm w-100" id="btnFiltrarAlbergues" onclick="if(window.aplicarFiltrosAlbergues)aplicarFiltrosAlbergues();return false;">
                <i class="bi bi-funnel me-1"></i> Filtrar
              </button>
            </div>
          </div>
        </div>
      </div>

<div class="card shadow-sm mb-4 d-none" id="cardFormAlbergue">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0"><i class="bi bi-house-add-fill me-2 text-primary"></i>Nuevo Albergue / Hogar Temporal</h6>
          <button type="button" class="btn-close" onclick="if(window.ocultarFormAlbergue)ocultarFormAlbergue();return false;"></button>
        </div>
        <div class="card-body">
          <form id="formAlbergue" onsubmit="if(window.guardarAlbergue){guardarAlbergue(event);} return false;">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Nombre del albergue *</label>
                <input type="text" id="albNombre" class="form-control" required>
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-semibold">Tipo *</label>
                <select id="albTipo" class="form-select" required>
                  <option>Albergue Oficial</option>
                  <option>Refugio Independiente</option>
                  <option>Hogar Temporal</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-semibold">Capacidad *</label>
                <input type="number" id="albCapacidad" class="form-control" min="1" required>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Alcaldía *</label>
                <select id="albAlcaldia" class="form-select" required>
                  <option value="">Selecciona alcaldía…</option>
                  <option value="Álvaro Obregón">Álvaro Obregón</option>
                  <option value="Azcapotzalco">Azcapotzalco</option>
                  <option value="Benito Juárez">Benito Juárez</option>
                  <option value="Coyoacán">Coyoacán</option>
                  <option value="Cuajimalpa de Morelos">Cuajimalpa de Morelos</option>
                  <option value="Cuauhtémoc">Cuauhtémoc</option>
                  <option value="Gustavo A. Madero">Gustavo A. Madero</option>
                  <option value="Iztacalco">Iztacalco</option>
                  <option value="Iztapalapa">Iztapalapa</option>
                  <option value="La Magdalena Contreras">La Magdalena Contreras</option>
                  <option value="Miguel Hidalgo">Miguel Hidalgo</option>
                  <option value="Milpa Alta">Milpa Alta</option>
                  <option value="Tláhuac">Tláhuac</option>
                  <option value="Tlalpan">Tlalpan</option>
                  <option value="Venustiano Carranza">Venustiano Carranza</option>
                  <option value="Xochimilco">Xochimilco</option>
                  <option value="Nezahualcóyotl">Nezahualcóyotl</option>
                  <option value="Ecatepec de Morelos">Ecatepec de Morelos</option>
                  <option value="Tlalnepantla de Baz">Tlalnepantla de Baz</option>
                  <option value="Naucalpan de Juárez">Naucalpan de Juárez</option>
                  <option value="Chimalhuacán">Chimalhuacán</option>
                  <option value="Atizapán de Zaragoza">Atizapán de Zaragoza</option>
                  <option value="Cuautitlán Izcalli">Cuautitlán Izcalli</option>
                  <option value="Valle de Chalco Solidaridad">Valle de Chalco Solidaridad</option>
                  <option value="Ixtapaluca">Ixtapaluca</option>
                  <option value="Tultitlán">Tultitlán</option>
                  <option value="Coacalco de Berriozábal">Coacalco de Berriozábal</option>
                  <option value="Huixquilucan">Huixquilucan</option>
                  <option value="Texcoco">Texcoco</option>
                  <option value="Nicolás Romero">Nicolás Romero</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Dirección</label>
                <input type="text" id="albDireccion" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Teléfono de contacto</label>
                <input type="tel" id="albTelefono" class="form-control">
              </div>
            </div>
            <div class="text-end mt-3">
              <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Guardar Albergue</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Directorio de albergues -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
          <h6 class="fw-bold m-0"><i class="bi bi-houses-fill me-2 text-primary"></i>Directorio de Albergues</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">Albergue</th>
                  <th>Tipo</th>
                  <th>Alcaldía</th>
                  <th>Contacto</th>
                  <th>Ocupación</th>
                  <th class="text-end pe-4">Acción</th>
                </tr>
              </thead>
              <tbody id="tbodyDirectorioAlbergues">
                <!-- Carga dinámica -->
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Casos en resguardo -->
      <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
          <h6 class="fw-bold m-0"><i class="bi bi-clipboard-heart-fill me-2 text-danger"></i>Casos en Resguardo — Asignación a Albergue</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">Folio RUPE</th>
                  <th>Mascota</th>
                  <th>Raza</th>
                  <th>Alcaldía del caso</th>
                  <th>Albergue asignado</th>
                  <th class="text-end pe-4">Acción</th>
                </tr>
              </thead>
              <tbody id="tbodyAlbergues">
                <!-- Carga dinámica -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== MÓDULO: MI PERFIL (TUTOR) ==================== -->
    <section id="modulo-perfil" class="<?php echo (($seccion_activa ?? '') === 'perfil') ? 'modulo-seccion activo' : 'modulo-seccion'; ?>">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
          <h3 class="fw-bold text-dark m-0">Mi Perfil</h3>
          <p class="text-muted small m-0">Tus datos de contacto y el resumen de tus reportes registrados en RUPE CDMX.</p>
        </div>
      </div>

      <div class="row g-3">
        <!-- TARJETA DE DATOS PERSONALES -->
        <div class="col-lg-4">
          <div class="card shadow-sm h-100">
            <div class="card-body text-center p-4">
              <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                   id="perfilAvatarGrande"
                   style="width:84px;height:84px;border-radius:50%;background:var(--color-accent);color:#4A1521;font-weight:800;font-size:1.6rem;">MT</div>
              <h5 class="fw-bold mb-1" id="perfilNombreDisplay">Tutor</h5>
              <span class="badge bg-light text-dark border mb-3">
                Cuenta gratuita · Tutor desde <span id="perfilFechaRegistro">ago 2026</span>
              </span>
              <div class="text-start small border-top pt-3 mt-1">
                <p class="mb-2"><i class="bi bi-telephone me-2 text-muted"></i><span id="perfilTelefonoDisplay">—</span></p>
                <p class="mb-2"><i class="bi bi-envelope me-2 text-muted"></i><span id="perfilCorreoDisplay">—</span></p>
                <p class="mb-0"><i class="bi bi-shield-check me-2 text-muted"></i><span id="perfilPrivacidadDisplay">—</span></p>
              </div>
              <button class="btn btn-sm w-100 mt-3 text-white" style="background-color:var(--color-primary);" onclick="if(window.abrirModalEditarPerfil)abrirModalEditarPerfil();return false;">
                <i class="bi bi-pencil me-1"></i> Editar datos de contacto
              </button>
            </div>
          </div>
        </div>

        <!-- ESTADÍSTICAS + MIS MASCOTAS -->
        <div class="col-lg-8">
          <div class="row g-3">
            <div class="col-md-4">
              <div class="card shadow-sm h-100 text-center p-3">
                <div class="fs-3 fw-bold" style="color:var(--color-primary);" id="perfilStatTotal">0</div>
                <div class="small text-muted">Reportes registrados</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card shadow-sm h-100 text-center p-3">
                <div class="fs-3 fw-bold text-warning" id="perfilStatActivos">0</div>
                <div class="small text-muted">Casos activos</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card shadow-sm h-100 text-center p-3">
                <div class="fs-3 fw-bold text-success" id="perfilStatResueltos">0</div>
                <div class="small text-muted">Casos localizados</div>
              </div>
            </div>

            <div class="col-12">
              <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold small">
                  <i class="bi bi-clipboard-heart me-1"></i> Mis mascotas registradas
                </div>
                
              
              <div class="row g-2 align-items-end mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-semibold mb-1">Selecciona folio para ver ficha</label>
                  <select id="perfilSelectMascota" class="form-select form-select-sm">
                    <option value="">— Selecciona un folio —</option>
                  </select>
                </div>
                <div class="col-md-6 d-flex gap-2">
                  <button type="button" id="btnPerfilVerFicha" class="btn btn-sm btn-outline-primary flex-fill">
                    <i class="bi bi-eye me-1"></i> Ver ficha
                  </button>
                  <button type="button" id="btnPerfilDescargarPdf" class="btn btn-sm btn-outline-danger flex-fill">
                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                  </button>
                </div>
              </div>
              <div id="perfilFichaPreview" class="border rounded p-3 mb-3 bg-light">
                <p class="text-muted small mb-0">Selecciona un folio para ver la ficha correspondiente.</p>
              </div>

<div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light small">
                      <tr><th class="ps-3">Folio</th><th>Mascota</th><th>Alcaldía</th><th>Estatus</th><th class="text-end pe-3">Acción</th></tr>
                    </thead>
                    <tbody id="tbodyPerfilMascotas">
                      <!-- Carga dinámica vía JS -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== MÓDULO: CONFIGURACIÓN DE CUENTA (TUTOR) ==================== -->
    <section id="modulo-configuracion" class="<?php echo (($seccion_activa ?? '') === 'configuracion') ? 'modulo-seccion activo' : 'modulo-seccion'; ?>">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
          <h3 class="fw-bold text-dark m-0">Configuración</h3>
          <p class="text-muted small m-0">Seguridad, notificaciones, privacidad y preferencias de tu cuenta.</p>
        </div>
      </div>

      <div class="row g-3">
        <!-- SEGURIDAD DE LA CUENTA -->
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold small">
              <i class="bi bi-shield-lock me-1"></i> Seguridad de la cuenta
            </div>
            <div class="card-body">
              <form id="formCambiarContrasena" onsubmit="if(window.cambiarContrasenaTutor){cambiarContrasenaTutor(event);}return false;">
                <div class="mb-3">
                  <label class="form-label fw-semibold small">Contraseña actual</label>
                  <input type="password" id="cfgPassActual" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold small">Nueva contraseña</label>
                  <input type="password" id="cfgPassNueva" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8" required>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold small">Confirmar nueva contraseña</label>
                  <input type="password" id="cfgPassConfirmar" class="form-control" placeholder="Repite la nueva contraseña" minlength="8" required>
                </div>
                <button type="submit" class="btn btn-sm text-white" style="background-color:var(--color-primary);">
                  <i class="bi bi-key me-1"></i> Actualizar contraseña
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- NOTIFICACIONES -->
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold small">
              <i class="bi bi-bell me-1"></i> Notificaciones
            </div>
            <div class="card-body">
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="cfgNotifEmail" onchange="if(window.guardarPreferencias)guardarPreferencias()">
                <label class="form-check-label small" for="cfgNotifEmail">Avisos por correo electrónico</label>
              </div>
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="cfgNotifSms" onchange="if(window.guardarPreferencias)guardarPreferencias()">
                <label class="form-check-label small" for="cfgNotifSms">Avisos por SMS</label>
              </div>
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="cfgNotifAvistamiento" onchange="if(window.guardarPreferencias)guardarPreferencias()">
                <label class="form-check-label small" for="cfgNotifAvistamiento">Nuevo avistamiento reportado en mis folios</label>
              </div>
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="cfgNotifEstatus" onchange="if(window.guardarPreferencias)guardarPreferencias()">
                <label class="form-check-label small" for="cfgNotifEstatus">Cambio de estatus de mis casos</label>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="cfgNotifBoletin" onchange="if(window.guardarPreferencias)guardarPreferencias()">
                <label class="form-check-label small" for="cfgNotifBoletin">Boletín informativo de RUPE CDMX</label>
              </div>

              <button type="button" id="btnGuardarNotificaciones" class="btn btn-sm btn-outline-primary mt-2"
                      onclick="if(window.guardarPreferencias){guardarPreferencias(true);}return false;">
                <i class="bi bi-save me-1"></i> Guardar notificaciones
              </button>

            </div>
          </div>
        </div>

        <!-- PREFERENCIAS GENERALES -->
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold small">
              <i class="bi bi-sliders me-1"></i> Preferencias generales
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label fw-semibold small">Idioma</label>
                <select id="cfgIdioma" class="form-select" onchange="if(window.guardarPreferencias)guardarPreferencias()">
                  <option value="es-MX">Español (México)</option>
                  <option value="en-US">English (United States)</option>
                </select>
              </div>
              <div class="mb-1">
                <label class="form-label fw-semibold small">Alcaldía predeterminada al registrar un reporte</label>
                <select id="cfgAlcaldiaDefault" class="form-select" onchange="if(window.guardarPreferencias)guardarPreferencias()">
                  <option>Cuauhtémoc (Roma Norte)</option>
                  <option>Coyoacán (Centro)</option>
                  <option>Benito Juárez (Del Valle)</option>
                  <option>Iztapalapa (San Miguel)</option>
                  <option>Miguel Hidalgo (Polanco)</option>
                  <option>Tlalpan (Centro)</option>
                  <option>Gustavo A. Madero (Lindavista)</option>
                </select>
                <div class="form-text">Se precargará automáticamente en el formulario de "Registro completo del extravío".</div>
              </div>
              <button type="button" id="btnGuardarPreferencias" class="btn btn-sm text-white mt-3 w-100"
                      style="background-color:var(--color-primary);"
                      onclick="if(window.guardarPreferencias){guardarPreferencias(true);}return false;">
                <i class="bi bi-save me-1"></i> Guardar preferencias
              </button>
            </div>
          </div>
        </div>

        <!-- PRIVACIDAD Y DATOS -->
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold small">
              <i class="bi bi-file-earmark-lock me-1"></i> Privacidad y mis datos
            </div>
            <div class="card-body">
              <p class="small text-muted">
                Aviso de privacidad:
                <span class="fw-semibold" id="cfgEstatusPrivacidad">—</span>
                <a href="#" class="ms-1" onclick="mostrarModuloPerfil(event)">(gestionar en Mi Perfil)</a>
              </p>
              <button class="btn btn-sm btn-outline-secondary w-100 mb-2" onclick="descargarMisDatos()">
                <i class="bi bi-download me-1"></i> Descargar mis datos (JSON)
              </button>
              <button class="btn btn-sm btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#modalEliminarCuenta">
                <i class="bi bi-trash me-1"></i> Eliminar mi cuenta
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== MÓDULO: AYUDA (TUTOR) ==================== -->
    <section id="modulo-ayuda" class="<?php echo (($seccion_activa ?? '') === 'ayuda') ? 'modulo-seccion activo' : 'modulo-seccion'; ?>">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
          <h3 class="fw-bold text-dark m-0">Centro de Ayuda</h3>
          <p class="text-muted small m-0">Guías rápidas, preguntas frecuentes y soporte directo.</p>
        </div>
      </div>

      <!-- BUSCADOR DE AYUDA -->
      <div class="card shadow-sm mb-3">
        <div class="card-body py-3">
          <label class="form-label small fw-semibold mb-1">¿En qué necesitas ayuda?</label>
          <input type="text" id="ayudaBuscador" class="form-control" placeholder="Ej. QR, avistamiento, contraseña, albergue..." oninput="filtrarAyuda()">
        </div>
      </div>

      <div class="row g-3">
        <!-- GUÍA RÁPIDA POR MÓDULO -->
        <div class="col-lg-7">
          <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold small">
              <i class="bi bi-signpost-split me-1"></i> Guía rápida por módulo
            </div>
            <div class="list-group list-group-flush">
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="irAModuloDesdeAyuda('registro')">
                <i class="bi bi-clipboard-plus text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Registrar un extravío</strong><br><span class="small text-muted">Captura los 15 campos + foto de tu mascota y genera su folio.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="irAModuloDesdeAyuda('ficha-pdf')">
                <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Ficha oficial + código QR</strong><br><span class="small text-muted">Descarga o imprime el expediente para pegar en postes y compartir.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="irAModuloDesdeAyuda('gps-qr')">
                <i class="bi bi-geo-alt text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Reportar un avistamiento</strong><br><span class="small text-muted">Marca en el mapa dónde viste a una mascota extraviada.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="irAModuloDesdeAyuda('estatus')">
                <i class="bi bi-check-circle text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Estatus y seguimiento</strong><br><span class="small text-muted">Consulta o actualiza en qué va tu caso.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="irAModuloDesdeAyuda('albergues')">
                <i class="bi bi-house-heart text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Red de albergues</strong><br><span class="small text-muted">Consulta a dónde puede haber sido llevada tu mascota.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="irAModuloDesdeAyuda('perfil')">
                <i class="bi bi-person-circle text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Mi perfil</strong><br><span class="small text-muted">Edita tus datos de contacto y consulta tus estadísticas.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
              <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3" onclick="irAModuloDesdeAyuda('configuracion')">
                <i class="bi bi-gear text-danger fs-5"></i>
                <span class="flex-grow-1"><strong>Configuración</strong><br><span class="small text-muted">Contraseña, notificaciones, idioma y privacidad.</span></span>
                <i class="bi bi-chevron-right text-muted"></i>
              </button>
            </div>
          </div>

          <!-- PREGUNTAS FRECUENTES -->
          <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold small">
              <i class="bi bi-patch-question me-1"></i> Preguntas frecuentes
            </div>
            <div class="card-body p-0">
              <div class="accordion accordion-flush" id="acordionAyudaFAQ">

                <div class="accordion-item" data-ayuda-item data-ayuda-texto="folio no encuentro perdí olvidé recuperar">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                      ¿Qué hago si perdí u olvidé mi número de folio?
                    </button>
                  </h2>
                  <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#acordionAyudaFAQ">
                    <div class="accordion-body small text-muted">
                      Tu folio también llegó a tu correo al momento del registro. Si no lo encuentras, entra a
                      <strong>Trazabilidad de Avistamientos</strong> o a <strong>Mi Perfil → Mis mascotas registradas</strong>,
                      ahí aparecen todos tus folios activos sin necesidad de escribirlo.
                    </div>
                  </div>
                </div>

                <div class="accordion-item" data-ayuda-item data-ayuda-texto="qr codigo ficha pdf imprimir descargar validado gps">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                      ¿Para qué sirve el código QR de la ficha oficial?
                    </button>
                  </h2>
                  <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#acordionAyudaFAQ">
                    <div class="accordion-body small text-muted">
                      Al escanearlo, cualquier persona puede verificar el folio y, si ya fue validada por un administrador,
                      la última ubicación GPS confirmada del caso. Si el GPS todavía no ha sido validado, el QR lo indica
                      como "pendiente" en vez de mostrar una ubicación sin confirmar.
                    </div>
                  </div>
                </div>

                <div class="accordion-item" data-ayuda-item data-ayuda-texto="avistamiento gps mapa ubicacion marcar">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                      Vi a un perro parecido en la calle, ¿cómo lo reporto?
                    </button>
                  </h2>
                  <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#acordionAyudaFAQ">
                    <div class="accordion-body small text-muted">
                      Entra a <strong>Reporte GPS / QR</strong>, selecciona el folio (o pide al tutor que te comparta el suyo),
                      marca el punto en el mapa y agrega una foto si es posible. El tutor recibirá una notificación si las
                      tiene activadas en <strong>Configuración</strong>.
                    </div>
                  </div>
                </div>

                <div class="accordion-item" data-ayuda-item data-ayuda-texto="estatus busqueda resguardo localizado significado">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                      ¿Qué significa cada estatus de mi caso?
                    </button>
                  </h2>
                  <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#acordionAyudaFAQ">
                    <div class="accordion-body small text-muted">
                      <strong>En Búsqueda:</strong> aún no hay avistamientos confirmados. <strong>En Resguardo:</strong> la mascota
                      está resguardada, generalmente en un albergue de la red. <strong>Localizado:</strong> el caso se cerró
                      porque la mascota fue reunida con su tutor.
                    </div>
                  </div>
                </div>

                <div class="accordion-item" data-ayuda-item data-ayuda-texto="contrasena password cambiar seguridad cuenta">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                      ¿Cómo cambio mi contraseña?
                    </button>
                  </h2>
                  <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#acordionAyudaFAQ">
                    <div class="accordion-body small text-muted">
                      En <strong>Configuración → Seguridad de la cuenta</strong>, ingresa tu contraseña actual y la nueva
                      (mínimo 8 caracteres). El cambio aplica de inmediato en tu siguiente inicio de sesión.
                    </div>
                  </div>
                </div>

                <div class="accordion-item" data-ayuda-item data-ayuda-texto="gratis costo pago plataforma">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                      ¿La plataforma tiene algún costo?
                    </button>
                  </h2>
                  <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#acordionAyudaFAQ">
                    <div class="accordion-body small text-muted">
                      No. RUPE CDMX es una plataforma gratuita: el registro, la ficha PDF con QR, los avistamientos y el
                      seguimiento de casos no tienen ningún costo para el tutor.
                    </div>
                  </div>
                </div>

                <div class="accordion-item" data-ayuda-item data-ayuda-texto="eliminar cuenta borrar datos privacidad">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                      ¿Puedo eliminar mi cuenta y mis datos?
                    </button>
                  </h2>
                  <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#acordionAyudaFAQ">
                    <div class="accordion-body small text-muted">
                      Sí, desde <strong>Configuración → Privacidad y mis datos</strong> puedes descargar toda tu información
                      en un archivo o solicitar la eliminación de tu cuenta. Tus folios permanecen como expedientes
                      anónimos para no perder el historial de casos.
                    </div>
                  </div>
                </div>

              </div>
              <p class="text-center text-muted small py-3 d-none" id="ayudaSinResultados">
                <i class="bi bi-search me-1"></i> No encontramos preguntas relacionadas con tu búsqueda.
              </p>
            </div>
          </div>
        </div>

        <!-- CONTACTO Y SOPORTE -->
        <div class="col-lg-5">
          <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold small">
              <i class="bi bi-headset me-1"></i> Contacto directo
            </div>
            <div class="card-body small">
              <p class="mb-2"><i class="bi bi-telephone me-2 text-muted"></i>55 5658 1111 (Línea RUPE CDMX)</p>
              <p class="mb-2"><i class="bi bi-envelope me-2 text-muted"></i>admin@buscandoahuellitasrupe.com.mx</p>
              <p class="mb-2"><i class="bi bi-clock me-2 text-muted"></i>Lunes a viernes, 9:00–18:00 hrs</p>
              <p class="mb-0"><i class="bi bi-whatsapp me-2 text-muted"></i>55 1234 5678 (solo mensajes)</p>
            </div>
          </div>

          <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold small">
              <i class="bi bi-envelope-paper me-1"></i> Enviar un mensaje
            </div>
            <div class="card-body">
              <form id="formContactoAyuda" onsubmit="if(window.enviarMensajeAyuda){enviarMensajeAyuda(event);}return false;">
                <div class="mb-3">
                  <label class="form-label fw-semibold small">Asunto</label>
                  <select id="ayudaAsunto" class="form-select" required>
                    <option value="">Selecciona un tema</option>
                    <option>Problema con un folio</option>
                    <option>Error en la plataforma</option>
                    <option>Duda sobre un albergue</option>
                    <option>Cuenta y datos personales</option>
                    <option>Otro</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold small">Mensaje</label>
                  <textarea id="ayudaMensaje" class="form-control" rows="4" placeholder="Cuéntanos en qué podemos ayudarte..." required></textarea>
                </div>
                <button type="submit" class="btn btn-sm w-100 text-white" style="background-color:var(--color-primary);">
                  <i class="bi bi-send me-1"></i> Enviar mensaje
                </button>
                <p class="text-muted small mt-2 mb-0">Te responderemos a <span id="ayudaCorreoRespuesta">tu correo registrado</span> en menos de 24 horas.</p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  </div>
  <!-- fin .app-content -->

  <!-- MODAL CONFIRMAR CIERRE DE SESIÓN -->

  <!-- Modal Bootstrap: eliminar albergue -->
  <div class="modal fade" id="modalEliminarAlbergue" tabindex="-1" aria-labelledby="modalEliminarAlbergueLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold text-danger" id="modalEliminarAlbergueLabel">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar albergue
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body pt-2">
          <p class="mb-2">¿Seguro que deseas eliminar este albergue del directorio?</p>
          <div class="alert alert-light border mb-0">
            <div class="fw-semibold" id="modalEliminarAlbergueNombre">—</div>
            <div class="small text-muted" id="modalEliminarAlbergueDetalle"></div>
          </div>
          <p class="small text-muted mt-3 mb-0">Esta acción no se puede deshacer. Solo se pueden eliminar albergues sin casos asignados.</p>
          <input type="hidden" id="modalEliminarAlbergueId" value="">
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" id="btnConfirmarEliminarAlbergue">
            <i class="bi bi-trash me-1"></i> Sí, eliminar
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalCerrarSesion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center p-4">
          <div class="mb-3">
            <i class="bi bi-box-arrow-right text-danger" style="font-size: 2.4rem;"></i>
          </div>
          <h5 class="fw-bold mb-2">¿Cerrar tu sesión?</h5>
          <p class="text-muted small mb-4">Tu folio y registros quedan guardados. Puedes volver a entrar cuando quieras con tu misma cuenta gratuita.</p>
          <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger px-4" id="btnConfirmarCierre" data-bs-dismiss="modal">
              <i class="bi bi-box-arrow-right me-1"></i> Sí, cerrar sesión
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- PANTALLA DE SESIÓN CERRADA -->
  <div class="logout-overlay" id="logoutOverlay">
    <i class="bi bi-check-circle-fill text-warning"></i>
    <h4 class="fw-bold m-0">Sesión cerrada</h4>
    <p class="mb-0" style="max-width: 320px; opacity: 0.85;">Gracias por usar RUPE CDMX. Tus folios seguirán activos para cuando regreses.</p>
    <button type="button" class="btn btn-light fw-semibold px-4 mt-2" id="btnVolverIniciar">
      <i class="bi bi-box-arrow-in-right me-1"></i> Volver a entrar
    </button>
  </div>

  <!-- MODAL EDITAR REGISTRO -->
  <div class="modal fade" id="modalEditarRegistro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white" style="background-color: var(--color-primary) !important;">
          <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Folio <span id="modalFolioId"></span></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formEditarRegistro" onsubmit="guardarEdicionRegistro(event); return false;">
            <input type="hidden" id="editIndex">
            <input type="hidden" id="editIdMascota">
            <div id="editAlert" class="alert d-none py-2 small" role="alert"></div>
            <div class="mb-3">
              <label class="form-label fw-semibold small">Mascota (nombre)</label>
              <input type="text" id="editNombre" name="nombre" class="form-control" required minlength="2" maxlength="20"
                     pattern="[A-Za-zÁÉÍÓÚÜáéíóúüÑñ0-9 .'\-]{2,20}" title="2 a 20 caracteres">
              <div class="form-text small text-success d-none" id="editNombreOk"><i class="bi bi-check-circle-fill me-1"></i>Correcto</div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold small">Raza</label>
              <select id="editRaza" name="id_raza" class="form-select" required>
                <option value="">Selecciona raza…</option>
                <?php if (!empty($razas) && is_array($razas)): foreach ($razas as $rz): ?>
                  <option value="<?= (int)($rz['id_raza'] ?? 0) ?>"><?= htmlspecialchars((string)($rz['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; endif; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold small">Alcaldía</label>
              <select id="editAlcaldia" name="id_alcaldia" class="form-select" required>
                <option value="">Selecciona alcaldía…</option>
                <?php if (!empty($alcaldias) && is_array($alcaldias)): foreach ($alcaldias as $al): ?>
                  <option value="<?= (int)($al['id_alcaldia'] ?? 0) ?>"><?= htmlspecialchars((string)($al['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; endif; ?>
              </select>
            </div>
            <div class="row g-2 mb-3">
              <div class="col-md-4">
                <label class="form-label fw-semibold small">Colonia</label>
                <select id="editColonia" name="colonia" class="form-select" required>
                  <option value="">Selecciona colonia…</option>
                  <option value="Roma Norte">Roma Norte</option>
                  <option value="Roma Sur">Roma Sur</option>
                  <option value="Condesa">Condesa</option>
                  <option value="Hipódromo">Hipódromo</option>
                  <option value="Del Valle Centro">Del Valle Centro</option>
                  <option value="Narvarte">Narvarte</option>
                  <option value="Coyoacán Centro">Coyoacán Centro</option>
                  <option value="Pedregal">Pedregal</option>
                  <option value="San Ángel">San Ángel</option>
                  <option value="Polanco">Polanco</option>
                  <option value="Anzures">Anzures</option>
                  <option value="Lindavista">Lindavista</option>
                  <option value="Industrial">Industrial</option>
                  <option value="Doctores">Doctores</option>
                  <option value="Juárez">Juárez</option>
                  <option value="Centro Histórico">Centro Histórico</option>
                  <option value="Santa María la Ribera">Santa María la Ribera</option>
                  <option value="Tacubaya">Tacubaya</option>
                  <option value="Mixcoac">Mixcoac</option>
                  <option value="Portales">Portales</option>
                  <option value="Villa Coapa">Villa Coapa</option>
                  <option value="Culhuacán">Culhuacán</option>
                  <option value="San Miguel (Iztapalapa)">San Miguel (Iztapalapa)</option>
                  <option value="Agrícola Oriental">Agrícola Oriental</option>
                  <option value="Jardín Balbuena">Jardín Balbuena</option>
                  <option value="Xochimilco Centro">Xochimilco Centro</option>
                  <option value="Tlalpan Centro">Tlalpan Centro</option>
                  <option value="Copilco">Copilco</option>
                  <option value="Universidad">Universidad</option>
                  <option value="Otro / Otra colonia">Otro / Otra colonia</option>
                </select>
                <div class="form-text small text-success d-none" id="editColoniaOk"><i class="bi bi-check-circle-fill me-1"></i>Correcto</div>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold small">Calle</label>
                <input type="text" id="editCalle" name="calle" class="form-control" required maxlength="120" minlength="2" placeholder="Calle / número">
                <div class="form-text small text-success d-none" id="editCalleOk"><i class="bi bi-check-circle-fill me-1"></i>Correcto</div>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold small">Referencia</label>
                <input type="text" id="editReferencia" name="referencia" class="form-control" maxlength="120" placeholder="Referencia">
                <div class="form-text small text-success d-none" id="editReferenciaOk"><i class="bi bi-check-circle-fill me-1"></i>Correcto</div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold small">Fecha Extravío</label>
              <input type="date" id="editFecha" name="fecha_extravio" class="form-control" required>
            </div>
            <div class="text-end">
              <button type="button" class="btn btn-secondary me-1" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-success" id="btnGuardarEdicion"><i class="bi bi-check-circle me-1"></i> Guardar Cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast de confirmación (Panel del Tutor) -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="tutorToastOk" class="toast align-items-center text-bg-success border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body" id="tutorToastMsg">Listo</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <!-- MODAL EDITAR PERFIL DEL TUTOR -->
  <div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header text-white" style="background-color: var(--color-primary);">
          <h5 class="modal-title fw-bold"><i class="bi bi-person-circle me-2"></i>Editar mis datos</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formEditarPerfil" onsubmit="if(window.guardarPerfilTutor){guardarPerfilTutor(event);}return false;">
            <div class="mb-3">
              <label class="form-label fw-semibold small">Nombre completo</label>
              <input type="text" id="perfilInputNombre" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold small">Teléfono</label>
              <input type="tel" id="perfilInputTelefono" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold small">Correo electrónico</label>
              <input type="email" id="perfilInputCorreo" class="form-control" required>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="perfilInputPrivacidad">
              <label class="form-check-label small" for="perfilInputPrivacidad">
                Acepto el aviso de privacidad y el uso de mis datos de contacto para fines de localización.
              </label>
            </div>
            <div class="text-end mt-3">
              <button type="button" class="btn btn-secondary me-1" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL CONFIRMAR ELIMINAR CUENTA -->

  <!-- Modal confirmar eliminar SOLO un folio (no afecta otros) -->
  <div class="modal fade" id="modalEliminarRegistro" tabindex="-1" aria-hidden="true" aria-labelledby="modalEliminarRegistroLabel">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold" id="modalEliminarRegistroLabel">
            <i class="bi bi-trash me-2"></i>Eliminar registro
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="delIndex" value="">
          <input type="hidden" id="delIdMascota" value="">
          <input type="hidden" id="delFolio" value="">
          <div id="delAlert" class="alert d-none py-2 small" role="alert"></div>
          <p class="mb-2">¿Seguro que deseas eliminar este reporte?</p>
          <ul class="list-unstyled mb-0 small bg-light rounded p-3">
            <li><strong>Folio:</strong> <span id="delLabelFolio" class="text-danger fw-bold">—</span></li>
            <li><strong>Mascota:</strong> <span id="delLabelNombre">—</span></li>
            <li><strong>Raza:</strong> <span id="delLabelRaza">—</span></li>
          </ul>
          <p class="text-muted small mt-3 mb-0">
            Solo se eliminará este folio. El resto de tus registros no se modifican.
            Esta acción marca el reporte como inactivo (soft-delete).
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
            <i class="bi bi-trash me-1"></i> Sí, eliminar solo este folio
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEliminarCuenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center p-4">
          <div class="mb-3"><i class="bi bi-exclamation-triangle text-danger" style="font-size: 2.4rem;"></i></div>
          <h5 class="fw-bold mb-2">¿Eliminar tu cuenta de RUPE CDMX?</h5>
          <p class="text-muted small mb-4">
            Se eliminarán tus datos de contacto y preferencias. Tus folios ya registrados permanecerán
            en el sistema como expedientes anónimos para no perder el historial de casos.
          </p>
          <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger px-4" onclick="confirmarEliminarCuenta()" data-bs-dismiss="modal">
              <i class="bi bi-trash me-1"></i> Sí, eliminar mi cuenta
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- JS BOOTSTRAP, LEAFLET Y LÓGICA GENERAL -->
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="/assets/js/rupe-sanitize.js"></script>
  <script defer src="/assets/js/rupe-alerts.js"></script>
  <script defer src="/assets/js/rupe-panels.js?v=20261003menu"></script>
  <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script defer src="/assets/js/tutor-gps.js?v=20260925one1"></script>

  <script>
    // Backup JSON de mascotas (por si RUPE_INIT falla)
  </script>
  <script type="application/json" id="rupeMascotasJson"><?= json_encode(array_values(is_array($mascotas ?? null) ? $mascotas : []), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?></script>
  <script>
    // Datos reales inyectados desde PHP (TutorController::panel → BD)
    window.RUPE_INIT = {
      csrf: <?= json_encode($csrf ?? (function_exists('csrf_token') ? csrf_token() : ''), JSON_UNESCAPED_UNICODE) ?>,
      app_url: <?= json_encode($app_url ?? (defined('APP_URL') ? rtrim(APP_URL, '/') : ''), JSON_UNESCAPED_UNICODE) ?>,
      user: <?= json_encode($user ?? [], JSON_UNESCAPED_UNICODE) ?>,
      mascotas: <?= json_encode($mascotas ?? [], JSON_UNESCAPED_UNICODE) ?>,
      stats: <?= json_encode($stats ?? [], JSON_UNESCAPED_UNICODE) ?>,
      alcaldias: <?= json_encode($alcaldias ?? [], JSON_UNESCAPED_UNICODE) ?>,
      razas: <?= json_encode($razas ?? [], JSON_UNESCAPED_UNICODE) ?>,
      albergues: <?= json_encode($albergues ?? [], JSON_UNESCAPED_UNICODE) ?>,
      preferencias: <?= json_encode($preferencias ?? [], JSON_UNESCAPED_UNICODE) ?>,
      bitacoras: <?= json_encode($bitacoras ?? [], JSON_UNESCAPED_UNICODE) ?>,
      historial_seguimiento: <?= json_encode($historial_seguimiento ?? [], JSON_UNESCAPED_UNICODE) ?>
    };
    window.__CSRF__ = window.RUPE_INIT.csrf || '';
    window.__RUPE_USER__ = window.RUPE_INIT.user || {};
    window.state = {
      tutorActual: {
        nombre: (window.RUPE_INIT.user && window.RUPE_INIT.user.nombre) || '',
        telefono: (window.RUPE_INIT.user && window.RUPE_INIT.user.telefono) || '',
        correo: (window.RUPE_INIT.user && window.RUPE_INIT.user.correo) || '',
        aceptaPrivacidad: true
      },
      preferencias: {
        notifEmail: true,
        notifSms: false,
        notifNuevoAvistamiento: true,
        notifCambioEstatus: true,
        notifBoletinRUPE: false,
        idioma: 'es-MX',
        alcaldiaDefault: ''
      },
      registros: Array.isArray(window.RUPE_INIT.mascotas) ? window.RUPE_INIT.mascotas.slice() : [],
      historial_seguimiento: Array.isArray(window.RUPE_INIT.historial_seguimiento) ? window.RUPE_INIT.historial_seguimiento.slice() : [],
      albergues: Array.isArray(window.RUPE_INIT.albergues) ? window.RUPE_INIT.albergues.slice() : [],
      folioSeleccionado: (window.RUPE_INIT.mascotas && window.RUPE_INIT.mascotas[0]) ? window.RUPE_INIT.mascotas[0].folio : '',
      statsBackend: window.RUPE_INIT.stats || {},
      mapa: null,
      markerGps: null,
      charts: {}
    };
    var state = window.state;

    // Sembrar state desde PHP / API init
    if (typeof state === 'undefined' || !state) { var state = window.state || {}; }
    if (!state.registros || !state.registros.length) {
      const seed = (window.RUPE_INIT && Array.isArray(window.RUPE_INIT.mascotas)) ? window.RUPE_INIT.mascotas : [];
      state.registros = seed.map(function (m) {
        return Object.assign({}, m, {
          id: m.id || m.id_mascota || 0,
          id_mascota: m.id_mascota || m.id || 0,
          raza: m.raza || m.raza_nombre || '',
          alcaldia: m.alcaldia || m.alcaldia_nombre || '',
          fecha: m.fecha || m.fecha_extravio || '',
          colonia: m.colonia || '',
          direccion_ref: m.direccion_ref || '',
          foto: m.foto || m.foto_url || '',
        });
      });
      try { window.state = state; } catch (_) {}
    }
    let modalEditarBs = null;
    let modalCerrarSesionBs = null;
    let modalEditarPerfilBs = null;

    // Textos de cada módulo, usados para actualizar el botón "Módulos" del topbar
    const NOMBRES_MODULO = {
      analisis: 'Panel Analítico',
      registro: 'Registro (15 Campos + Foto)',
      crud: 'Tabla CRUD',
      'ficha-pdf': 'Ficha Oficial + QR',
      'gps-qr': 'Reporte GPS / QR',
      seguimiento: 'Trazabilidad',
      estatus: 'Estatus y Seguimiento',
      albergues: 'Red de Albergues',
      perfil: 'Mi Perfil',
      configuracion: 'Configuración',
      ayuda: 'Centro de Ayuda'
    };
    const NUMEROS_MODULO = {
      analisis: 1, registro: 2, crud: 3, 'ficha-pdf': 4,
      'gps-qr': 5, seguimiento: 6, estatus: 7, albergues: 8, perfil: '', configuracion: '', ayuda: ''
    };

    document.addEventListener("DOMContentLoaded", () => {
      modalEditarBs = new bootstrap.Modal(document.getElementById('modalEditarRegistro'));
      modalCerrarSesionBs = new bootstrap.Modal(document.getElementById('modalCerrarSesion'));
      modalEditarPerfilBs = new bootstrap.Modal(document.getElementById('modalEditarPerfil'));

      inicializarNavegacion();
      rupeMobileNav.init();
      inicializarScrollNavbar();
      inicializarCerrarSesion();
      inicializarMapa();
      inicializarGraficas();
      renderizarTodo();
      inicializarPanelTutor();

      // Progreso en vivo del formulario de Registro (grid de 4)
      const formRegistro = document.getElementById('formCapturaPerro');
      if (formRegistro) {
        formRegistro.addEventListener('input', actualizarProgresoRegistro);
        formRegistro.addEventListener('change', actualizarProgresoRegistro);
      }
    });

    function inicializarNavegacion() {
      // Delegación de eventos: funciona aunque se re-rendericen botones
      const activarModulo = (targetModulo, btn, e) => {
        if (!targetModulo) return;
        if (e) {
          e.preventDefault();
          e.stopPropagation();
          if (btn && typeof crearRippleEnBoton === 'function') crearRippleEnBoton(e, btn);
        }
        document.querySelectorAll('.nav-modulo').forEach(nav => nav.classList.remove('active'));
        if (btn) btn.classList.add('active');
        else {
          const b = document.querySelector('.nav-modulo[data-modulo="' + targetModulo + '"]');
          if (b) b.classList.add('active');
        }
        if (typeof actualizarBotonModulos === 'function') actualizarBotonModulos(targetModulo);

        document.querySelectorAll('.modulo-seccion').forEach(sec => sec.classList.remove('activo'));
        const seccion = document.getElementById('modulo-' + targetModulo);
        if (seccion) {
          seccion.classList.add('activo');
          try { seccion.scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch (_) {}
        }

        if (targetModulo === 'gps-qr' && state && state.mapa) {
          setTimeout(() => { try { state.mapa.invalidateSize(); } catch (_) {} }, 300);
        }
        if (targetModulo === 'analisis' && typeof actualizarGraficasPanel === 'function') {
          try { actualizarGraficasPanel(); } catch (_) {}
        }
        if (targetModulo === 'registro' && typeof precargarDatosRegistro === 'function') {
          try { precargarDatosRegistro(); } catch (_) {}
        }
        if (typeof cerrarMegaMenu === 'function') cerrarMegaMenu();
      };

      document.querySelectorAll('.nav-modulo').forEach(btn => {
        btn.style.cursor = 'pointer';
        btn.addEventListener('click', (e) => {
          activarModulo(btn.getAttribute('data-modulo'), btn, e);
        });
      });
      // Delegación global por si hay botones dinámicos
      document.addEventListener('click', (e) => {
        const btn = e.target.closest('.nav-modulo, [data-modulo]');
        if (!btn || !btn.classList.contains('nav-modulo')) return;
        if (btn._rupeBound) return;
        activarModulo(btn.getAttribute('data-modulo'), btn, e);
      });
      window.activarModuloTutor = activarModulo;
    }

    // Actualiza el número y texto que se muestran en el botón "Módulos" del topbar
    function actualizarBotonModulos(clave) {
      const numEl = document.getElementById('moduloActualNum');
      const txtEl = document.getElementById('moduloActualTexto');
      if (numEl) numEl.textContent = NUMEROS_MODULO[clave] ?? '';
      if (txtEl) txtEl.textContent = NOMBRES_MODULO[clave] ?? '';
    }

    // Controla la animación de entrada en cascada de las tarjetas del mega menú
    /**
     * Clase ES6 que encapsula el comportamiento del menú horizontal / hamburguesa:
     * abre y cierra el panel colapsable en móvil, lo cierra al elegir un módulo,
     * al hacer clic fuera de él, y si la ventana crece a tamaño de escritorio.
     */
    class RupeMobileNav {
      constructor({ togglerId, collapseId }) {
        this.toggler = document.getElementById(togglerId);
        this.collapseEl = document.getElementById(collapseId);
        this.collapse = null;
      }

      init() {
        if (!this.toggler || !this.collapseEl || typeof bootstrap === 'undefined') return;
        this.collapse = new bootstrap.Collapse(this.collapseEl, { toggle: false });

        // Si el usuario agranda la ventana a escritorio, se asegura de cerrar el panel móvil
        window.addEventListener('resize', () => {
          if (window.innerWidth >= 1200) this.close();
        }, { passive: true });

        // Cierra el panel al hacer clic fuera de él (solo aplica cuando está abierto en móvil)
        document.addEventListener('click', (evento) => {
          const clicDentroDelPanel = this.collapseEl.contains(evento.target);
          const clicEnElBoton = this.toggler.contains(evento.target);
          if (!clicDentroDelPanel && !clicEnElBoton && this.isOpen()) this.close();
        });
      }

      isOpen() {
        return this.collapseEl.classList.contains('show');
      }

      close() {
        if (this.collapse && this.isOpen()) this.collapse.hide();
      }
    }

    const rupeMobileNav = new RupeMobileNav({ togglerId: 'rupeNavToggler', collapseId: 'rupeNavCollapse' });

    // Se mantiene esta función global por compatibilidad: varias partes del código
    // ya la invocan (irAModuloDesdeAyuda, mostrarModuloPerfil, etc.) para cerrar el
    // panel móvil tras navegar a otro módulo.
    const cerrarMegaMenu = () => rupeMobileNav.close();

    // Pequeño efecto de onda al pulsar una tarjeta del mega menú
    function crearRippleEnBoton(evento, boton) {
      const rect = boton.getBoundingClientRect();
      const ripple = document.createElement('span');
      const tam = Math.max(rect.width, rect.height);
      ripple.className = 'ripple';
      ripple.style.width = ripple.style.height = `${tam}px`;
      ripple.style.left = `${(evento.clientX ?? rect.left + rect.width / 2) - rect.left - tam / 2}px`;
      ripple.style.top = `${(evento.clientY ?? rect.top + rect.height / 2) - rect.top - tam / 2}px`;
      boton.appendChild(ripple);
      setTimeout(() => ripple.remove(), 550);
    }

    // Reduce la barra superior sutilmente al hacer scroll para dar sensación de profundidad
    function inicializarScrollNavbar() {
      const topbar = document.querySelector('.topbar');
      if (!topbar) return;
      window.addEventListener('scroll', () => {
        topbar.classList.toggle('navbar-scrolled', window.scrollY > 12);
      }, { passive: true });
    }

    // Flujo de cierre de sesión: confirmación -> pantalla de despedida -> volver a entrar
    function inicializarCerrarSesion() {
      const btnConfirmar = document.getElementById('btnConfirmarCierre');
      const overlay = document.getElementById('logoutOverlay');
      const btnVolver = document.getElementById('btnVolverIniciar');

      btnConfirmar.addEventListener('click', () => {
        setTimeout(() => overlay.classList.add('visible'), 200);
      });

      btnVolver.addEventListener('click', () => {
        overlay.classList.remove('visible');
      });
    }

    function inicializarMapa() {
      const el = document.getElementById('mapaGPS');
      if (!el || typeof L === 'undefined') return;
      if (state.mapa) {
        setTimeout(function () { try { state.mapa.invalidateSize(); } catch (_) {} }, 200);
        return;
      }
      try {
        state.mapa = L.map('mapaGPS').setView([19.4326, -99.1332], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap',
          maxZoom: 19
        }).addTo(state.mapa);

        state.markerGps = L.marker([19.4326, -99.1332], { draggable: true }).addTo(state.mapa)
          .bindPopup("Punto de avistamiento");

        state.markerGps.on('dragend', function (e) {
          const coord = e.target.getLatLng();
          const latEl = document.getElementById('gpsLatitud');
          const lngEl = document.getElementById('gpsLongitud');
          if (latEl) latEl.value = coord.lat.toFixed(6);
          if (lngEl) lngEl.value = coord.lng.toFixed(6);
        });

        state.capaAvistamientos = L.layerGroup().addTo(state.mapa);
        setTimeout(function () { try { state.mapa.invalidateSize(); } catch (_) {} }, 300);
      } catch (err) {
        console.warn('[RUPE] mapa init', err);
      }
    }
    window.inicializarMapa = inicializarMapa;

    function renderizarMarcadoresAvistamientosGPS(reg) {
      if (!state.capaAvistamientos) return;
      state.capaAvistamientos.clearLayers();
      if (!reg) return;

      reg.avistamientos.forEach(a => {
        if (isNaN(a.lat) || isNaN(a.lng)) return;
        L.circleMarker([a.lat, a.lng], {
          radius: 8, color: '#dc3545', fillColor: '#dc3545', fillOpacity: 0.85, weight: 2
        })
          .bindPopup(`<strong>${a.condicion}</strong><br>${a.fechaHora}<br>${a.referencia}`)
          .addTo(state.capaAvistamientos);
      });
    }

    function capturarUbicacionGPS() {
      if (!("geolocation" in navigator)) {
        if (typeof rupeToast === 'function') rupeToast("Tu navegador no soporta geolocalización.", 'warning');
        else if (typeof mostrarToastTutor === 'function') mostrarToastTutor("Tu navegador no soporta geolocalización.", 'warning');
        return;
      }
      try {
        if (!state.mapa && typeof L !== 'undefined' && document.getElementById('mapaGPS')) {
          state.mapa = L.map('mapaGPS').setView([19.4326, -99.1332], 13);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(state.mapa);
          state.markerGps = L.marker([19.4326, -99.1332], { draggable: true }).addTo(state.mapa);
          state.markerGps.on('dragend', function (e) {
            const c = e.target.getLatLng();
            document.getElementById('gpsLatitud').value = c.lat.toFixed(6);
            document.getElementById('gpsLongitud').value = c.lng.toFixed(6);
          });
        }
        if (state.mapa) setTimeout(function(){ try { state.mapa.invalidateSize(); } catch(_){} }, 100);
      } catch (err) { console.warn(err); }

      if (typeof rupeToast === 'function') rupeToast('Obteniendo ubicación…', 'info');
      navigator.geolocation.getCurrentPosition(
        function (position) {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          document.getElementById('gpsLatitud').value = lat.toFixed(6);
          document.getElementById('gpsLongitud').value = lng.toFixed(6);
          if (state.mapa && state.markerGps && typeof L !== 'undefined') {
            const ll = L.latLng(lat, lng);
            state.markerGps.setLatLng(ll);
            state.mapa.setView(ll, 16);
          }
          if (typeof rupeToast === 'function') rupeToast('Ubicación GPS obtenida con éxito.', 'success');
          else if (typeof mostrarToastTutor === 'function') mostrarToastTutor('Ubicación GPS obtenida con éxito.');
        },
        function (error) {
          const mensajes = {
            1: 'Permiso de ubicación denegado. Actívalo en el navegador o usa el buscador.',
            2: 'No se pudo determinar tu ubicación.',
            3: 'La solicitud de ubicación tardó demasiado.'
          };
          const msg = mensajes[error.code] || 'No se pudo obtener tu ubicación GPS.';
          if (typeof rupeToast === 'function') rupeToast(msg, 'danger');
          else if (typeof mostrarToastTutor === 'function') mostrarToastTutor(msg, 'danger');
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
      );
    }
    window.capturarUbicacionGPS = capturarUbicacionGPS;

    function buscarDireccionGPS() {
      const query = document.getElementById('gpsBuscarDireccion').value.trim();
      const msg = document.getElementById('gpsBuscarDireccionMsg');
      if (!query) return;

      msg.textContent = "Buscando…";
      fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=mx&q=${encodeURIComponent(query)}`)
        .then(r => r.json())
        .then(resultados => {
          if (!resultados || resultados.length === 0) {
            msg.textContent = "No se encontró esa dirección. Intenta ser más específico (colonia, alcaldía).";
            return;
          }
          const lugar = resultados[0];
          const lat = parseFloat(lugar.lat);
          const lng = parseFloat(lugar.lon);
          document.getElementById('gpsLatitud').value = lat.toFixed(4);
          document.getElementById('gpsLongitud').value = lng.toFixed(4);
          const newLatLng = new L.LatLng(lat, lng);
          state.markerGps.setLatLng(newLatLng);
          state.mapa.setView(newLatLng, 16);
          msg.textContent = "Ubicación encontrada: " + lugar.display_name;
        })
        .catch(() => {
          msg.textContent = "No se pudo buscar la dirección en este momento. Intenta más tarde o usa el mapa directamente.";
        });
    }

    function inicializarGraficas() {
      const optsBase = { responsive: true, maintainAspectRatio: false };

      state.charts.estatus = new Chart(document.getElementById('chartEstatus').getContext('2d'), {
        type: 'doughnut',
        data: { labels: ['En Búsqueda', 'En Resguardo', 'Localizado'], datasets: [{ data: [0, 0, 0], backgroundColor: ['#dc3545', '#ffc107', '#198754'] }] },
        options: { ...optsBase, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
      });

      state.charts.alcaldias = new Chart(document.getElementById('chartAlcaldias').getContext('2d'), {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Reportes de Extravío', data: [], backgroundColor: '#6B1D2F' }] },
        options: { ...optsBase, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
      });

      state.charts.tamanos = new Chart(document.getElementById('chartTamanos').getContext('2d'), {
        type: 'bar',
        data: { labels: ['Pequeño', 'Mediano', 'Grande', 'Gigante'], datasets: [{ label: 'Cantidad', data: [0, 0, 0, 0], backgroundColor: '#206A5D' }] },
        options: { indexAxis: 'y', ...optsBase, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
      });

      state.charts.sexo = new Chart(document.getElementById('chartSexo').getContext('2d'), {
        type: 'doughnut',
        data: { labels: ['Macho', 'Hembra'], datasets: [{ data: [0, 0], backgroundColor: ['#0d6efd', '#d63384'] }] },
        options: { ...optsBase, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
      });

      state.charts.chip = new Chart(document.getElementById('chartChip').getContext('2d'), {
        type: 'doughnut',
        data: { labels: ['Con chip/tatuaje', 'Sin identificación'], datasets: [{ data: [0, 0], backgroundColor: ['#6f42c1', '#adb5bd'] }] },
        options: { ...optsBase, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
      });

      state.charts.razas = new Chart(document.getElementById('chartRazas').getContext('2d'), {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Casos', data: [], backgroundColor: '#E8A33D' }] },
        options: { indexAxis: 'y', ...optsBase, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
      });

      state.charts.fechas = new Chart(document.getElementById('chartFechas').getContext('2d'), {
        type: 'line',
        data: { labels: [], datasets: [{ label: 'Extravíos', data: [], borderColor: '#206A5D', backgroundColor: 'rgba(32, 106, 93, 0.15)', fill: true, tension: 0.3, pointRadius: 4, pointBackgroundColor: '#206A5D' }] },
        options: { ...optsBase, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
      });
    }

    function actualizarGraficasPanel() {
      if (!state.charts.estatus) return;

      const activas = state.registros.filter(r => r.estatus === 'En Búsqueda').length;
      const resguardo = state.registros.filter(r => r.estatus === 'En Resguardo').length;
      const localizadas = state.registros.filter(r => r.estatus === 'Localizado').length;
      state.charts.estatus.data.datasets[0].data = [activas, resguardo, localizadas];
      state.charts.estatus.update();

      const conteoAlcaldias = {};
      state.registros.forEach(r => {
        const alc = (r.alcaldia || '').split(' ')[0] || 'Sin dato';
        conteoAlcaldias[alc] = (conteoAlcaldias[alc] || 0) + 1;
      });
      const alcOrdenadas = Object.entries(conteoAlcaldias).sort((a, b) => b[1] - a[1]);
      state.charts.alcaldias.data.labels = alcOrdenadas.map(e => e[0]);
      state.charts.alcaldias.data.datasets[0].data = alcOrdenadas.map(e => e[1]);
      state.charts.alcaldias.update();

      const tamanos = { 'Pequeño': 0, 'Mediano': 0, 'Grande': 0, 'Gigante': 0 };
      state.registros.forEach(r => { if (tamanos[r.tamano] !== undefined) tamanos[r.tamano]++; });
      state.charts.tamanos.data.datasets[0].data = Object.values(tamanos);
      state.charts.tamanos.update();

      let machos = 0, hembras = 0;
      state.registros.forEach(r => { if (r.sexo === 'Macho') machos++; else if (r.sexo === 'Hembra') hembras++; });
      if (state.charts.sexo) { state.charts.sexo.data.datasets[0].data = [machos, hembras]; state.charts.sexo.update(); }

      let conId = 0, sinId = 0;
      state.registros.forEach(r => {
        const chip = (r.chip || '').toLowerCase();
        if (chip.includes('no posee') || chip === '') sinId++; else conId++;
      });
      if (state.charts.chip) { state.charts.chip.data.datasets[0].data = [conId, sinId]; state.charts.chip.update(); }

      const conteoRazas = {};
      state.registros.forEach(r => { const raza = r.raza || 'Sin especificar'; conteoRazas[raza] = (conteoRazas[raza] || 0) + 1; });
      const topRazas = Object.entries(conteoRazas).sort((a, b) => b[1] - a[1]).slice(0, 6);
      if (state.charts.razas) {
        state.charts.razas.data.labels = topRazas.map(e => e[0]);
        state.charts.razas.data.datasets[0].data = topRazas.map(e => e[1]);
        state.charts.razas.update();
      }

      const conteoFechas = {};
      state.registros.forEach(r => { if (r.fecha) conteoFechas[r.fecha] = (conteoFechas[r.fecha] || 0) + 1; });
      const fechasOrdenadas = Object.keys(conteoFechas).sort();
      if (state.charts.fechas) {
        state.charts.fechas.data.labels = fechasOrdenadas.map(f => { const p = f.split('-'); return p.length === 3 ? p[2] + '/' + p[1] : f; });
        state.charts.fechas.data.datasets[0].data = fechasOrdenadas.map(f => conteoFechas[f]);
        state.charts.fechas.update();
      }
    }

    function renderizarTodo() {
      renderizarTablaCrud();
      renderizarSelectoresFolio();
      if (typeof llenarSelectFoliosGPS === "function") llenarSelectFoliosGPS();
      renderizarFichaPDF();
      renderizarSeguimiento();
      renderizarModuloEstatus5();
      renderizarAlbergues();
      actualizarAnalisisMetricas();
      if (typeof actualizarSelectoresFolioTutor === 'function') actualizarSelectoresFolioTutor();
      if (typeof renderizarContextoFolioGPS === 'function') renderizarContextoFolioGPS();
    }

    // Guarda temporalmente la foto subida como archivo (Base64) para el Módulo 2
    let fotoRegistroCapturada = null;
    const FOTO_PLACEHOLDER = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200'%3E%3Crect fill='%23f0f0f0' width='400' height='200'%3E%3C/rect%3E%3Ctext fill='%23999' x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle'%3ESin foto%3C/text%3E%3C/svg%3E";

    // Guarda temporalmente la foto subida como archivo (Base64) para el avistamiento del Módulo 5
    let fotoAvistamientoCapturada = null;

    function previsualizarFotoAvistamiento(input) {
      const file = input.files[0];
      if (!file) return;
      if (!file.type.startsWith('image/')) {
        alert('Selecciona un archivo de imagen (JPG, PNG, etc.).');
        input.value = '';
        return;
      }
      const reader = new FileReader();
      reader.onload = e => {
        fotoAvistamientoCapturada = e.target.result;
        document.getElementById('gpsFotoPreview').src = e.target.result;
      };
      reader.readAsDataURL(file);
    }

    function previsualizarFotoRegistro(input) {
      const file = input.files[0];
      if (!file) return;
      if (!file.type.startsWith('image/')) {
        alert('Selecciona un archivo de imagen (JPG, PNG, etc.).');
        input.value = '';
        return;
      }
      const reader = new FileReader();
      reader.onload = e => {
        fotoRegistroCapturada = e.target.result;
        window.fotoRegistroCapturada = e.target.result;
        document.getElementById('regFotoPreview').src = e.target.result;
      };
      reader.readAsDataURL(file);

      const dropzone = document.getElementById('regDropzone');
      if (dropzone) {
        dropzone.classList.remove('dragover');
        dropzone.classList.add('tiene-archivo');
        dropzone.querySelector('i').className = 'bi bi-check-circle-fill d-block mb-1';
        document.getElementById('regDropzoneTexto').textContent = file.name;
      }
      actualizarProgresoRegistro();
    }

    // Permite soltar (drag & drop) una imagen directamente sobre el dropzone
    function manejarSoltarFoto(evento) {
      evento.preventDefault();
      const dropzone = document.getElementById('regDropzone');
      if (dropzone) dropzone.classList.remove('dragover');
      const archivo = evento.dataTransfer?.files?.[0];
      if (!archivo) return;
      const inputFoto = document.getElementById('regFoto');
      const transferencia = new DataTransfer();
      transferencia.items.add(archivo);
      inputFoto.files = transferencia.files;
      previsualizarFotoRegistro(inputFoto);
    }

    // Contador de caracteres para "Señas particulares" (máx. 220)
    function actualizarContadorSenas() {
      const campo = document.getElementById('regSenas');
      const contador = document.getElementById('regSenasContador');
      if (!campo || !contador) return;
      contador.textContent = `${campo.value.length} / 220`;
    }

    // Precarga los datos de contacto del tutor y su alcaldía preferida al entrar al módulo,
    // para que no tenga que volver a escribirlos en cada nuevo registro.
    function precargarDatosRegistro() {
      const campoNombre = document.getElementById('regTutorNombre');
      const campoTel = document.getElementById('regTutorTel');
      const campoCorreo = document.getElementById('regTutorEmail');
      const campoAlcaldia = document.getElementById('regAlcaldia');
      const avisoTutor = document.getElementById('regTutorAuto');
      const avisoAlcaldia = document.getElementById('regAlcaldiaAuto');

      if (campoNombre && !campoNombre.value) campoNombre.value = state.tutorActual.nombre;
      if (campoTel && !campoTel.value) campoTel.value = state.tutorActual.telefono;
      if (campoCorreo && !campoCorreo.value) campoCorreo.value = state.tutorActual.correo;
      if (avisoTutor) {
        avisoTutor.innerHTML = `<i class="bi bi-magic me-1"></i>Precargamos tus datos desde <strong>Mi Perfil</strong>; puedes editarlos si este reporte es de otra persona.`;
      }

      if (campoAlcaldia && state.preferencias?.alcaldiaDefault) {
        campoAlcaldia.value = state.preferencias.alcaldiaDefault;
        if (avisoAlcaldia) avisoAlcaldia.innerHTML = `<i class="bi bi-magic me-1"></i>Tu alcaldía predeterminada (Configuración).`;
      }

      actualizarContadorSenas();
      actualizarProgresoRegistro();
    }

    // Calcula cuántos de los campos requeridos del formulario ya están completos
    // y actualiza la barra de progreso + las insignias numeradas de cada campo.
    function actualizarProgresoRegistro() {
      const formulario = document.getElementById('formCapturaPerro');
      const barra = document.getElementById('regProgresoBarra');
      const texto = document.getElementById('regProgresoTexto');
      if (!formulario || !barra || !texto) return;

      const campos = [...formulario.querySelectorAll('[required]')];
      const completos = campos.filter(campo => {
        if (campo.type === 'checkbox') return campo.checked;
        return campo.value.trim() !== '';
      });

      const porcentaje = campos.length ? Math.round((completos.length / campos.length) * 100) : 0;
      barra.style.width = `${porcentaje}%`;
      barra.setAttribute('aria-valuenow', porcentaje);
      barra.classList.toggle('completo', porcentaje === 100);
      texto.textContent = `${completos.length} de ${campos.length} campos`;

      // Marca en verde la insignia numerada de cada campo ya completo
      campos.forEach(campo => {
        const contenedor = campo.closest('.col-xl-3, .col-xl-6, .col-lg-6, .col-md-6, .col-md-8, .col-12');
        const badge = contenedor?.querySelector('.num-badge');
        const valido = campo.type === 'checkbox' ? campo.checked : campo.value.trim() !== '';
        campo.classList.toggle('is-valid', valido);
        if (badge) badge.style.backgroundColor = valido ? '#198754' : '';
      });
    }

    function procesarRegistroTutorLegacy(e) {
      // Delega al flujo real API (tutor-registro.js?v=20260924fast'function' && window.procesarRegistroTutor !== procesarRegistroTutorLegacy) {
        return window.procesarRegistroTutor(e);
      }
      if (window.RUPETutor && typeof window.RUPETutor.syncMascotas === 'function') {
        alert('Usa el botón Guardar: se enviará al servidor para generar folio y QR reales.');
      }
      if (e && e.preventDefault) e.preventDefault();
    }

    function renderizarTablaCrud(filtro = "") {
      if (window.RUPECrud && typeof window.RUPECrud.renderTablaCrud === 'function') {
        return window.RUPECrud.renderTablaCrud(filtro);
      }
      if (typeof window.RUPETutor !== 'undefined' && typeof window.RUPETutor.renderTablaCrudDB === 'function') {
        return window.RUPETutor.renderTablaCrudDB(filtro);
      }
      const tbody = document.getElementById('tbodyCrud');
      if (!tbody) return;
      tbody.innerHTML = "";
      const regs = (state && state.registros) ? state.registros : [];
      const filtrados = regs.filter(r => {
        const folio = String(r.folio || '').toLowerCase();
        const nombre = String(r.nombre || '').toLowerCase();
        const q = String(filtro || '').toLowerCase();
        return !q || folio.includes(q) || nombre.includes(q);
      });
      if (!filtrados.length) {
        if (!regs.length && tbody.querySelector('tr[data-id], tr[data-folio]')) {
          return; // conservar filas del servidor
        }
        tbody.innerHTML = '<tr><td colspan="11" class="text-center text-muted py-4">Sin registros</td></tr>';
        return;
      }

      const clean = (v) => {
        if (v == null || v === '' || v === 'null' || v === 'undefined') return '—';
        return String(v);
      };
      filtrados.forEach((reg) => {
        const indexOriginal = state.registros.findIndex(r => r.folio === reg.folio || r.id === reg.id);
        const est = clean(reg.estatus) !== '—' ? reg.estatus : 'En Búsqueda';
        let badgeColor = est === 'En Búsqueda' ? 'bg-danger' : (est === 'En Resguardo' ? 'bg-warning text-dark' : 'bg-success');
        const folio = clean(reg.folio);
        const nombre = clean(reg.nombre);
        const raza = clean(reg.raza || reg.raza_nombre);
        const alc = clean(reg.alcaldia || reg.alcaldia_nombre);
        let colonia = clean(reg.colonia);
        let calle = clean(reg.calle);
        let referencia = '—';
        const dirRef = String(reg.direccion_ref || reg.referencia || '').trim();
        if ((calle === '—' || !reg.calle) && dirRef) {
          const mCalle = dirRef.match(/Calle:\s*([^|]+)/i);
          const mRef = dirRef.match(/Ref(?:erencia)?:\s*(.+)$/i);
          if (mCalle) calle = clean(mCalle[1].trim());
          if (mRef) referencia = clean(mRef[1].trim());
          else if (dirRef.indexOf(',') >= 0 && calle === '—') {
            const parts = dirRef.split(',');
            calle = clean((parts[0] || '').trim());
            referencia = clean(parts.slice(1).join(',').trim());
          } else if (calle === '—') {
            referencia = clean(dirRef);
          }
        } else if (dirRef) {
          const mRef2 = dirRef.match(/Ref(?:erencia)?:\s*(.+)$/i);
          referencia = clean(mRef2 ? mRef2[1].trim() : dirRef);
        }
        if (reg.referencia && clean(reg.referencia) !== '—') referencia = clean(reg.referencia);
        const fecha = clean(reg.fecha || reg.fecha_extravio);
        const tr = document.createElement('tr');
        const fotoSrc = (reg.foto && reg.foto !== 'null') ? reg.foto
          : ((reg.foto_url && reg.foto_url !== 'null') ? reg.foto_url
          : ((String(reg.especie || '').toLowerCase().indexOf('gato') >= 0)
              ? '/assets/img/placeholder-gato.svg'
              : '/assets/img/placeholder-perro.svg'));
        tr.innerHTML = `
          <td class="ps-4 fw-bold text-danger">#${folio}</td>
          <td><img src="${fotoSrc}" alt="${nombre}" class="rounded border" style="width:48px;height:48px;object-fit:cover;background:#f0f0f0;" onerror="this.src='/assets/img/placeholder-mascota.svg'"></td>
          <td><strong>${nombre}</strong></td>
          <td>${raza}</td>
          <td>${alc}</td>
          <td class="small">${colonia}</td>
          <td class="small">${calle}</td>
          <td class="small">${referencia}</td>
          <td>${fecha}</td>
          <td><span class="badge ${badgeColor}">${clean(est)}</span></td>
          <td class="text-end pe-4">
            <button type="button" class="btn btn-sm btn-outline-primary me-1" title="Ver Ficha PDF" onclick="seleccionarYVerPDF('${String(reg.folio || '').replace(/'/g, "\\'")}')"><i class="bi bi-eye"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary me-1" title="Editar" onclick="abrirModalEditar(${indexOriginal})"><i class="bi bi-pencil"></i></button>
            <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarRegistro(${indexOriginal})"><i class="bi bi-trash"></i></button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }

    function filtrarTablaCrud() {
      const query = document.getElementById('inputBuscadorCrud').value;
      renderizarTablaCrud(query);
    }

    function poblarSelectAlcaldiasEdicion(selectedId) {
      const sel = document.getElementById('editAlcaldia');
      if (!sel) return;
      const list = (window.RUPE_INIT && window.RUPE_INIT.alcaldias) || window.__RUPE_ALCALDIAS__ || [];
      const cur = selectedId != null && selectedId !== '' ? String(selectedId) : '';
      sel.innerHTML = '<option value="">— Sin cambiar —</option>';
      list.forEach(function (a) {
        const id = a.id_alcaldia != null ? a.id_alcaldia : a.id;
        const opt = document.createElement('option');
        opt.value = id;
        opt.textContent = a.nombre || ('Alcaldía ' + id);
        if (cur && String(id) === cur) opt.selected = true;
        sel.appendChild(opt);
      });
    }

    function previsualizarFotoEdicion(input) {
      const prev = document.getElementById('editFotoPreview');
      if (!input || !input.files || !input.files[0] || !prev) return;
      const file = input.files[0];
      if (file.size > 5 * 1024 * 1024) {
        if (typeof rupeToast === 'function') rupeToast('La foto no debe superar 5 MB.', 'warning');
        input.value = '';
        return;
      }
      const reader = new FileReader();
      reader.onload = function (e) { prev.src = e.target.result; };
      reader.readAsDataURL(file);
    }
    window.previsualizarFotoEdicion = previsualizarFotoEdicion;

    function abrirModalEditar(index) {
      const reg = state.registros[index];
      if (!reg) {
        if (typeof rupeToast === 'function') rupeToast('Registro no encontrado.', 'warning');
        return;
      }
      const alertEl = document.getElementById('editAlert');
      if (alertEl) { alertEl.classList.add('d-none'); alertEl.textContent = ''; }

      document.getElementById('editIndex').value = index;
      const idM = reg.id || reg.id_mascota || '';
      const idEl = document.getElementById('editIdMascota');
      if (idEl) idEl.value = idM;
      document.getElementById('modalFolioId').innerText = '#' + (reg.folio || '');
      document.getElementById('editNombre').value = reg.nombre || '';

      // Raza por id
      const selRaza = document.getElementById('editRaza');
      if (selRaza) {
        const list = (window.RUPE_INIT && window.RUPE_INIT.razas) || window.__RUPE_RAZAS__ || [];
        if (list.length && selRaza.options.length <= 1) {
          selRaza.innerHTML = '<option value="">Selecciona raza…</option>';
          list.forEach(function (r) {
            const id = r.id_raza != null ? r.id_raza : r.id;
            if (!id) return;
            const opt = document.createElement('option');
            opt.value = String(id);
            opt.textContent = r.nombre || ('Raza ' + id);
            selRaza.appendChild(opt);
          });
        }
        selRaza.value = reg.id_raza != null ? String(reg.id_raza) : '';
      }

      // Alcaldía por id
      const selAlc = document.getElementById('editAlcaldia');
      if (selAlc) {
        const list = (window.RUPE_INIT && window.RUPE_INIT.alcaldias) || window.__RUPE_ALCALDIAS__ || [];
        if (list.length && selAlc.options.length <= 1) {
          selAlc.innerHTML = '<option value="">Selecciona alcaldía…</option>';
          list.forEach(function (a) {
            const id = a.id_alcaldia != null ? a.id_alcaldia : a.id;
            if (!id) return;
            const opt = document.createElement('option');
            opt.value = String(id);
            opt.textContent = a.nombre || ('Alcaldía ' + id);
            selAlc.appendChild(opt);
          });
        }
        selAlc.value = reg.id_alcaldia != null ? String(reg.id_alcaldia) : '';
      }

      const fecha = reg.fecha || reg.fecha_extravio || '';
      document.getElementById('editFecha').value = fecha ? String(fecha).substring(0, 10) : '';
      const elColor = document.getElementById('editColor');
      if (elColor) elColor.value = reg.color || '';
      const elCol = document.getElementById('editColonia');
      if (elCol) elCol.value = reg.colonia || '';
      const elSenas = document.getElementById('editSenas');
      if (elSenas) elSenas.value = reg.senas || reg.senas_particulares || '';
      const prev = document.getElementById('editFotoPreview');
      if (prev) {
        const f = reg.foto || reg.foto_url || '';
        if (f && f !== 'null') { prev.src = f; prev.classList.remove('d-none'); }
        else { prev.removeAttribute('src'); prev.classList.add('d-none'); }
      }

      if (modalEditarBs) modalEditarBs.show();
      else {
        const modal = document.getElementById('modalEditarRegistro');
        if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
      }
    }
    window.abrirModalEditar = abrirModalEditar;

    async function guardarEdicionRegistro(e) {
      if (e) { e.preventDefault(); e.stopPropagation(); }
      const index = parseInt((document.getElementById('editIndex') || {}).value || '-1', 10);
      const reg = (typeof state !== 'undefined' && state.registros) ? state.registros[index] : null;
      const alertEl = document.getElementById('editAlert');
      if (!reg) {
        if (typeof rupeToast === 'function') rupeToast('Registro no encontrado.', 'danger');
        return false;
      }

      const nombre = ((document.getElementById('editNombre') || {}).value || '').trim();
      const idRaza = ((document.getElementById('editRaza') || {}).value || '').trim();
      const idAlc = ((document.getElementById('editAlcaldia') || {}).value || '').trim();
      const fecha = ((document.getElementById('editFecha') || {}).value || '').trim();
      const color = ((document.getElementById('editColor') || {}).value || '').trim();
      const colonia = ((document.getElementById('editColonia') || {}).value || '').trim();
      const senas = ((document.getElementById('editSenas') || {}).value || '').trim();

      if (nombre.length < 2) {
        if (alertEl) { alertEl.className = 'alert alert-warning py-2 small'; alertEl.textContent = 'El nombre debe tener al menos 2 caracteres.'; alertEl.classList.remove('d-none'); }
        return false;
      }
      if (!idRaza || idRaza === '0') {
        if (alertEl) { alertEl.className = 'alert alert-warning py-2 small'; alertEl.textContent = 'Selecciona una raza del catálogo.'; alertEl.classList.remove('d-none'); }
        return false;
      }
      if (!idAlc || idAlc === '0') {
        if (alertEl) { alertEl.className = 'alert alert-warning py-2 small'; alertEl.textContent = 'Selecciona una alcaldía del catálogo.'; alertEl.classList.remove('d-none'); }
        return false;
      }

      const idMascota = reg.id || reg.id_mascota || ((document.getElementById('editIdMascota') || {}).value || '');
      if (!idMascota) {
        if (typeof rupeToast === 'function') rupeToast('Este folio no tiene ID en base de datos.', 'danger');
        return false;
      }

      const csrf = (window.RUPE_INIT && window.RUPE_INIT.csrf) || window.__CSRF__ || '';
      const fd = new FormData();
      fd.append('nombre', nombre);
      fd.append('id_raza', String(parseInt(idRaza, 10)));
      fd.append('id_alcaldia', String(parseInt(idAlc, 10)));
      if (fecha) fd.append('fecha_extravio', fecha);
      if (color) fd.append('color', color);
      if (colonia) fd.append('colonia', colonia);
      if (senas) fd.append('senas_particulares', senas);
      fd.append('_csrf', csrf);
      fd.append('_method', 'PUT');
      const fileInput = document.getElementById('editFoto');
      if (fileInput && fileInput.files && fileInput.files[0]) {
        fd.append('foto', fileInput.files[0]);
      }

      const btn = document.getElementById('btnGuardarEdicion');
      if (btn) btn.disabled = true;
      if (alertEl) alertEl.classList.add('d-none');

      try {
        // Siempre POST (Hostinger / algunos servidores bloquean PUT)
        const res = await fetch('/api/tutor/mascotas/' + encodeURIComponent(idMascota), {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrf
          },
          credentials: 'same-origin',
          body: fd
        });
        const data = await res.json().catch(function () { return {}; });
        if (!res.ok || data.success === false) {
          const msg = data.message || ('Error ' + res.status);
          if (alertEl) { alertEl.className = 'alert alert-danger py-2 small'; alertEl.textContent = msg; alertEl.classList.remove('d-none'); }
          else if (typeof rupeToast === 'function') rupeToast(msg, 'danger');
          return false;
        }

        const m = (data.data && data.data.mascota) ? data.data.mascota : {};
        const razaTxt = (m.raza || m.raza_nombre || ((document.getElementById('editRaza') || {}).selectedOptions[0] || {}).textContent || '').toString().trim();
        const alcTxt = (m.alcaldia || m.alcaldia_nombre || ((document.getElementById('editAlcaldia') || {}).selectedOptions[0] || {}).textContent || '').toString().trim();

        reg.nombre = m.nombre || nombre;
        reg.id_raza = m.id_raza != null ? m.id_raza : parseInt(idRaza, 10);
        reg.id_alcaldia = m.id_alcaldia != null ? m.id_alcaldia : parseInt(idAlc, 10);
        reg.fecha = m.fecha || m.fecha_extravio || fecha;
        reg.fecha_extravio = reg.fecha;
        reg.raza = razaTxt || '—';
        reg.raza_nombre = reg.raza;
        reg.alcaldia = alcTxt || '—';
        reg.alcaldia_nombre = reg.alcaldia;
        reg.color = m.color != null ? m.color : color;
        reg.colonia = m.colonia != null ? m.colonia : colonia;
        reg.senas = m.senas || m.senas_particulares || senas;
        reg.senas_particulares = reg.senas;
        if (m.foto || m.foto_url) {
          reg.foto = m.foto || m.foto_url;
          reg.foto_url = reg.foto;
        }

        if (modalEditarBs) modalEditarBs.hide();
        else {
          const modal = document.getElementById('modalEditarRegistro');
          if (modal && window.bootstrap) {
            const inst = bootstrap.Modal.getInstance(modal);
            if (inst) inst.hide();
          }
        }
        if (typeof window.pintarTablaCrud === 'function') window.pintarTablaCrud();
        else if (typeof renderizarTablaCrud === 'function') renderizarTablaCrud();
        // No pintar ficha con datos parciales del modal CRUD
        const folioFichaActivo = (typeof state !== 'undefined' && state.folioSeleccionado) ||
          ((document.getElementById('tutorSelectFolioFicha') || {}).value || '');
        if (String(folioFichaActivo) === String(reg.folio || '') && typeof window.renderizarFichaTutor === 'function') {
          const idRefetch = reg.id || reg.id_mascota;
          if (idRefetch) {
            fetch('/api/tutor/mascotas/' + encodeURIComponent(idRefetch), {
              headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
              credentials: 'same-origin'
            }).then(function (r) { return r.json(); }).then(function (full) {
              const fm = (full.data && full.data.mascota) || {};
              Object.keys(fm).forEach(function (k) {
                if (fm[k] !== undefined && fm[k] !== null && fm[k] !== '') reg[k] = fm[k];
              });
              try { window.renderizarFichaTutor(reg); } catch (_) {}
            }).catch(function () {
              try { window.renderizarFichaTutor(reg); } catch (_) {}
            });
          }
        }
        if (typeof rupeToast === 'function') rupeToast(data.message || 'Registro actualizado en la base de datos.', 'success');
        else if (typeof mostrarToastTutor === 'function') mostrarToastTutor('Registro actualizado.');
        return true;
      } catch (err) {
        if (alertEl) { alertEl.className = 'alert alert-danger py-2 small'; alertEl.textContent = err.message || 'Error de red.'; alertEl.classList.remove('d-none'); }
        else if (typeof rupeToast === 'function') rupeToast(err.message || 'Error de red.', 'danger');
        return false;
      } finally {
        if (btn) btn.disabled = false;
      }
    }
    window.guardarEdicionRegistro = guardarEdicionRegistro;

    function eliminarRegistro(index) {
      if (typeof window.abrirModalEliminarRegistro === 'function') {
        return window.abrirModalEliminarRegistro(index);
      }
      const reg = state.registros[index];
      if (!reg) return;
      if (!confirm('¿Eliminar el folio #' + (reg.folio || '') + '? Solo este registro.')) return;
      if (typeof window.confirmarEliminarRegistro === 'function') {
        return window.confirmarEliminarRegistro(index);
      }
    }
    window.eliminarRegistro = eliminarRegistro;

    function seleccionarYVerPDF(folio) {
      state.folioSeleccionado = folio;
      renderizarFichaPDF();
      document.querySelector('[data-modulo="ficha-pdf"]').click();
    }

    function renderizarFichaPDF() {
      const reg = state.registros.find(r => r.folio === state.folioSeleccionado) || state.registros[0];
      renderizarFichaTutor(reg || null);
    }

    function sincronizarGpsPorFolio() {
      state.folioSeleccionado = document.getElementById('gpsFolio').value;
      renderizarTodo();
    }

    function renderizarContextoFolioGPS() {
      const contexto = document.getElementById('gpsContextoFolio');
      const alerta = document.getElementById('gpsAlertaLocalizado');
      if (!contexto || !alerta) return;

      const folio = document.getElementById('gpsFolio') ? document.getElementById('gpsFolio').value : '';
      const reg = state.registros.find(r => r.folio === folio);

      if (!reg) {
        contexto.classList.add('d-none');
        alerta.classList.add('d-none');
        renderizarMarcadoresAvistamientosGPS(null);
        return;
      }

      const badgeColor = reg.estatus === 'En Búsqueda' ? 'bg-danger' : (reg.estatus === 'En Resguardo' ? 'bg-warning text-dark' : 'bg-success');
      contexto.classList.remove('d-none');
      contexto.innerHTML = `
        <img src="${reg.foto}" style="width:56px;height:56px;object-fit:cover;" class="rounded border">
        <div class="flex-grow-1">
          <strong>${reg.nombre}</strong> · ${reg.raza} · <span class="badge ${badgeColor}">${reg.estatus}</span>
          <div class="small text-muted">${reg.alcaldia}, Col. ${reg.colonia} · ${reg.avistamientos.length} avistamiento(s) previo(s)</div>
        </div>
      `;

      alerta.classList.toggle('d-none', reg.estatus !== 'Localizado');
      renderizarMarcadoresAvistamientosGPS(reg);
    }

    async function enviarReporteGPS_inline_legacy(e) {
      // Delega a tutor-gps.js?v=20260925one1'function' && window.enviarReporteGPS !== enviarReporteGPS_inline_legacy) {
        return window.enviarReporteGPS(e);
      }
      if (typeof rupeToast === 'function') rupeToast('Módulo GPS no cargado. Recarga la página.', 'danger');
      return false;
    }
    // No sobrescribir window.enviarReporteGPS si ya existe (tutor-gps.js?v=20260925one1'function') {
      window.enviarReporteGPS = enviarReporteGPS_inline_legacy;
    }
    window.capturarUbicacionGPS = capturarUbicacionGPS;
    window.buscarDireccionGPS = buscarDireccionGPS;
    window.verSeguimientoDesdeGPS = verSeguimientoDesdeGPS;

    function verSeguimientoDesdeGPS() {
      const folio = document.getElementById('gpsFolio').value;
      if (!folio) return;
      document.getElementById('tutorInputBuscarFolio').value = folio;
      buscarPorFolioTutor();
      document.querySelector('[data-modulo="estatus"]').click();
    }

    function renderizarSeguimiento() {
      if (typeof window.llenarSelectSeguimiento === 'function') window.llenarSelectSeguimiento();
      if (typeof window.cargarSeguimientoFolio === 'function') {
        const folio = (document.getElementById('selectFolioSeguimiento') || {}).value
          || state.folioSeleccionado
          || (state.registros[0] && state.registros[0].folio)
          || '';
        window.cargarSeguimientoFolio(folio);
        return;
      }
      const reg = state.registros.find(r => r.folio === state.folioSeleccionado) || state.registros[0];
      const tbody = document.getElementById('tbodySeguimiento');
      const badgeFolio = document.getElementById('badgeSeguimientoFolio');
      if (!tbody) return;
      tbody.innerHTML = "";
      if (!reg) return;

      badgeFolio.innerHTML = `<i class="bi bi-shield-check text-success me-1"></i> Folio Activo: <strong>#${reg.folio} (${reg.nombre})</strong>`;

      if(reg.avistamientos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">Aún no hay avistamientos registrados para este folio.</td></tr>`;
        return;
      }

      reg.avistamientos.forEach(a => {
        let badgeStyle = a.condicion === 'Resguardado' ? 'bg-success' : 'bg-warning text-dark';
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="ps-4">${a.fechaHora}</td>
          <td><span class="badge ${badgeStyle}">${a.condicion}</span></td>
          <td><img src="${a.foto}" style="width:45px; height:45px; object-fit:cover;" class="rounded border"></td>
          <td><code>${a.lat}, ${a.lng}</code></td>
          <td>${a.referencia}</td>
          <td class="small">${a.reporteroNombre || '<span class="text-muted">Ciudadano</span>'}${a.reporteroTel ? '<br><span class="text-muted">'+a.reporteroTel+'</span>' : ''}</td>
          <td class="text-end pe-4">
            <button class="btn btn-sm btn-outline-success" onclick="centrarEnMapa(${a.lat}, ${a.lng})"><i class="bi bi-pin-map-fill"></i> Ver Mapa</button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }

    function centrarEnMapa(lat, lng) {
      document.querySelector('[data-modulo="gps-qr"]').click();
      const newLatLng = new L.LatLng(lat, lng);
      state.markerGps.setLatLng(newLatLng);
      state.mapa.setView(newLatLng, 16);
    }

    function renderizarModuloEstatus5() {
      const tbody = document.getElementById('tbodyControlEstatus5');
      if (!tbody) return;
      tbody.innerHTML = '';
      const regs = (state && Array.isArray(state.registros)) ? state.registros : [];
      if (!regs.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin registros de seguimiento.</td></tr>';
        return;
      }
      regs.forEach(function (reg) {
        const est = String(reg.estatus || 'En Búsqueda');
        const badgeColor = est === 'En Búsqueda' ? 'bg-danger' : (est === 'En Resguardo' ? 'bg-warning text-dark' : 'bg-success');
        const folio = String(reg.folio || '');
        const nombre = String(reg.nombre || '—');
        const raza = String(reg.raza || reg.raza_nombre || '');
        let bit = String(reg.bitacora || reg.notas || '');
        if (!bit || bit === 'null' || bit === 'undefined') bit = 'Registro en RUPE CDMX';
        const tr = document.createElement('tr');
        tr.style.cursor = 'pointer';
        tr.title = 'Ver seguimiento de este folio';
        tr.setAttribute('data-folio', folio);
        tr.onclick = function () {
          const inp = document.getElementById('tutorInputBuscarFolio');
          if (inp) inp.value = folio;
          if (typeof buscarPorFolioTutor === 'function') buscarPorFolioTutor();
        };
        tr.innerHTML =
          '<td class="ps-2 fw-bold text-danger">#' + folio.replace(/</g, '&lt;') + '</td>' +
          '<td><strong>' + nombre.replace(/</g, '&lt;') + '</strong>' + (raza ? ' <span class="text-muted">(' + raza.replace(/</g, '&lt;') + ')</span>' : '') + '</td>' +
          '<td><span class="badge ' + badgeColor + '">' + est.replace(/</g, '&lt;') + '</span></td>' +
          '<td class="small text-muted">' + bit.replace(/</g, '&lt;') + '</td>';
        tbody.appendChild(tr);
      });
    }
    window.renderizarModuloEstatus5 = renderizarModuloEstatus5;

    function renderizarSelectoresFolio() {
      const selectGps = document.getElementById('gpsFolio');
      if (!selectGps) return;

      const list = (typeof state !== 'undefined' && state.registros && state.registros.length)
        ? state.registros
        : ((window.RUPE_INIT && Array.isArray(window.RUPE_INIT.mascotas)) ? window.RUPE_INIT.mascotas : []);

      // No vaciar si no hay datos (conserva opciones PHP del servidor)
      if (!list.length) {
        if (selectGps.options.length <= 1 && typeof window.llenarSelectFoliosGPS === 'function') {
          window.llenarSelectFoliosGPS();
        }
        return;
      }

      const prev = selectGps.value || (typeof state !== 'undefined' && state.folioSeleccionado) || '';
      selectGps.innerHTML = '';
      const o0 = document.createElement('option');
      o0.value = '';
      o0.textContent = '— Selecciona folio —';
      selectGps.appendChild(o0);

      list.forEach(function (reg) {
        if (!reg || !reg.folio) return;
        const opt = document.createElement('option');
        opt.value = String(reg.folio);
        opt.textContent = '#' + reg.folio + ' · ' + (reg.nombre || 'Mascota') + (reg.estatus ? ' · ' + reg.estatus : '');
        opt.dataset.id = String(reg.id || reg.id_mascota || '');
        selectGps.appendChild(opt);
      });

      if (prev) {
        try { selectGps.value = String(prev); } catch (_) {}
      }
      if (typeof window.sincronizarGpsPorFolio === 'function' && selectGps.value) {
        try { window.sincronizarGpsPorFolio(); } catch (_) {}
      }
    }

    function calcularOcupacionAlbergue(albergueId) {
      return state.registros.filter(r => r.albergueId === albergueId && r.estatus === 'En Resguardo').length;
    }

    function renderizarAlbergues() {
      renderizarDirectorioAlbergues();
      renderizarCasosResguardo();
      actualizarKpisAlbergues();
    }

    function renderizarDirectorioAlbergues() {
      const tbody = document.getElementById('tbodyDirectorioAlbergues');
      tbody.innerHTML = "";

      if (state.albergues.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">Aún no hay albergues registrados en la red. Agrega el primero con el botón de arriba.</td></tr>`;
        return;
      }

      state.albergues.forEach(alb => {
        const ocupados = calcularOcupacionAlbergue(alb.id);
        const pct = alb.capacidad > 0 ? Math.min(100, Math.round((ocupados / alb.capacidad) * 100)) : 0;
        const colorBarra = pct >= 100 ? 'bg-danger' : (pct >= 70 ? 'bg-warning' : 'bg-success');

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="ps-4">
            <strong>${alb.nombre}</strong>
            ${alb.direccion ? `<div class="small text-muted">${alb.direccion}</div>` : ''}
          </td>
          <td><span class="badge bg-light text-dark border">${alb.tipo}</span></td>
          <td>${alb.alcaldia}</td>
          <td class="small">${alb.telefono || '<span class="text-muted">Sin registrar</span>'}</td>
          <td style="min-width:150px;">
            <div class="d-flex justify-content-between small mb-1"><span>${ocupados}/${alb.capacidad}</span><span>${pct}%</span></div>
            <div class="progress" style="height:6px;"><div class="progress-bar ${colorBarra}" style="width:${pct}%"></div></div>
          </td>
          <td class="text-end pe-4">
            <button class="btn btn-sm btn-outline-danger" onclick="eliminarAlbergue('${alb.id}')" ${ocupados > 0 ? 'disabled title="No se puede eliminar: tiene casos asignados"' : 'title="Eliminar albergue"'}>
              <i class="bi bi-trash"></i>
            </button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }

    function renderizarCasosResguardo() {
      const tbody = document.getElementById('tbodyAlbergues');
      tbody.innerHTML = "";

      const casos = state.registros.filter(r => r.estatus === 'En Resguardo');

      if (casos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No hay casos en resguardo en este momento.</td></tr>`;
        return;
      }

      casos.forEach(reg => {
        const opciones = state.albergues.map(alb =>
          `<option value="${alb.id}" ${reg.albergueId === alb.id ? 'selected' : ''}>${alb.nombre}</option>`
        ).join('');

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="ps-4 fw-bold text-danger">#${reg.folio}</td>
          <td>${reg.nombre}</td>
          <td>${reg.raza}</td>
          <td>${reg.alcaldia}</td>
          <td>
            <select class="form-select form-select-sm" onchange="asignarAlbergue('${reg.folio}', this.value)">
              <option value="">— Sin asignar —</option>
              ${opciones}
            </select>
          </td>
          <td class="text-end pe-4">
            <button class="btn btn-sm btn-outline-primary" onclick="seleccionarYVerPDF('${reg.folio}')" title="Ver Ficha"><i class="bi bi-eye"></i></button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }

    function asignarAlbergue(folio, albergueId) {
      const reg = state.registros.find(r => r.folio === folio);
      if (!reg) return;
      reg.albergueId = albergueId || null;
      const alb = state.albergues.find(a => a.id === albergueId);
      reg.bitacora = alb ? `Resguardado en ${alb.nombre}.` : 'En resguardo, pendiente de asignar albergue.';
      renderizarTodo();
      mostrarToastTutor(alb ? `Folio #${folio} asignado a ${alb.nombre}.` : `Folio #${folio} sin albergue asignado.`);
    }

    function actualizarKpisAlbergues() {
      const totalAlbergues = state.albergues.length;
      const capacidadTotal = state.albergues.reduce((sum, a) => sum + Number(a.capacidad || 0), 0);
      const casosResguardo = state.registros.filter(r => r.estatus === 'En Resguardo').length;
      const sinAsignar = state.registros.filter(r => r.estatus === 'En Resguardo' && !r.albergueId).length;

      document.getElementById('kpiAlberguesTotal').textContent = totalAlbergues;
      document.getElementById('kpiAlberguesCapacidad').textContent = capacidadTotal;
      document.getElementById('kpiAlberguesOcupacion').textContent = casosResguardo;
      document.getElementById('kpiCasosSinAlbergue').textContent = sinAsignar;
    }

    function mostrarFormAlbergue() {
      document.getElementById('cardFormAlbergue').classList.remove('d-none');
    }

    function ocultarFormAlbergue() {
      document.getElementById('cardFormAlbergue').classList.add('d-none');
      document.getElementById('formAlbergue').reset();
    }

    function guardarAlbergue(e) {
      e.preventDefault();
      const nuevo = {
        id: 'ALB-' + Date.now(),
        nombre: document.getElementById('albNombre').value.trim(),
        tipo: document.getElementById('albTipo').value,
        capacidad: parseInt(document.getElementById('albCapacidad').value, 10) || 1,
        alcaldia: document.getElementById('albAlcaldia').value.trim(),
        direccion: document.getElementById('albDireccion').value.trim(),
        telefono: document.getElementById('albTelefono').value.trim()
      };
      state.albergues.push(nuevo);
      ocultarFormAlbergue();
      renderizarTodo();
      mostrarToastTutor(`Albergue "${nuevo.nombre}" agregado a la red.`);
    }

    function eliminarAlbergue(id) {
      const ocupados = calcularOcupacionAlbergue(id);
      if (ocupados > 0) {
        alert('No se puede eliminar: este albergue tiene casos asignados actualmente. Reasigna esos casos primero.');
        return;
      }
      const alb = state.albergues.find(a => a.id === id);
      if (alb && confirm(`¿Eliminar "${alb.nombre}" de la red de albergues?`)) {
        state.albergues = state.albergues.filter(a => a.id !== id);
        renderizarTodo();
      }
    }

    function actualizarAnalisisMetricas() {
      const total = state.registros.length;
      const activas = state.registros.filter(r => r.estatus === 'En Búsqueda').length;
      const resguardo = state.registros.filter(r => r.estatus === 'En Resguardo').length;
      const localizadas = state.registros.filter(r => r.estatus === 'Localizado').length;

      const totalAvist = state.registros.reduce((sum, r) => sum + (r.avistamientos ? r.avistamientos.length : 0), 0);
      const promAvist = total > 0 ? (totalAvist / total).toFixed(1) : '0.0';
      const conChip = state.registros.filter(r => { const c = (r.chip || '').toLowerCase(); return c && !c.includes('no posee'); }).length;
      const pctChip = total > 0 ? ((conChip / total) * 100).toFixed(1) : '0.0';
      const sinAvist = state.registros.filter(r => r.estatus === 'En Búsqueda' && (!r.avistamientos || r.avistamientos.length === 0)).length;
      const atendidos = resguardo + localizadas;
      const tasaResguardo = total > 0 ? ((atendidos / total) * 100).toFixed(1) : '0.0';
      const tasaLoc = total > 0 ? ((localizadas / total) * 100).toFixed(1) : '0.0';
      const pctActivas = total > 0 ? ((activas / total) * 100).toFixed(1) : '0.0';
      const pctResguardo = total > 0 ? ((resguardo / total) * 100).toFixed(1) : '0.0';

      document.getElementById('kpiTotalRegistros').innerText = total;
      document.getElementById('kpiAlertasActivas').innerText = activas;
      document.getElementById('kpiEnResguardo').innerText = resguardo;
      document.getElementById('kpiTasaLocalizacion').innerText = tasaLoc + '%';

      const detTasa = document.getElementById('kpiTasaDetalle');
      if (detTasa) detTasa.innerHTML = '<i class="bi bi-graph-up text-success me-1"></i>' + localizadas + ' de ' + total + ' casos';
      const detRes = document.getElementById('kpiResguardoPct');
      if (detRes) detRes.innerHTML = '<i class="bi bi-house-door text-warning me-1"></i>' + pctResguardo + '% del total';
      const detAlert = document.getElementById('kpiAlertasPct');
      if (detAlert) detAlert.innerHTML = '<i class="bi bi-broadcast text-danger me-1"></i>' + pctActivas + '% del total';

      const elAvist = document.getElementById('kpiTotalAvistamientos');
      if (elAvist) elAvist.innerText = totalAvist;
      const elProm = document.getElementById('kpiPromAvist');
      if (elProm) elProm.innerHTML = '<i class="bi bi-geo-alt text-info me-1"></i>Prom. ' + promAvist + ' por caso';
      const elChip = document.getElementById('kpiConChip');
      if (elChip) elChip.innerText = pctChip + '%';
      const elChipDet = document.getElementById('kpiChipDetalle');
      if (elChipDet) elChipDet.innerHTML = '<i class="bi bi-cpu me-1" style="color:#6f42c1;"></i>' + conChip + ' de ' + total + ' identificados';
      const elSin = document.getElementById('kpiSinAvist');
      if (elSin) elSin.innerText = sinAvist;
      const elTasaR = document.getElementById('kpiTasaResguardo');
      if (elTasaR) elTasaR.innerText = tasaResguardo + '%';

      if (state.charts.estatus) actualizarGraficasPanel();
      renderizarRankingAlcaldias();
      generarInsightsPanel();
    }

    function renderizarRankingAlcaldias() {
      const tbody = document.getElementById('tbodyRankingAlcaldias');
      if (!tbody) return;
      tbody.innerHTML = '';
      const porAlc = {};
      state.registros.forEach(r => {
        const alc = (r.alcaldia || '').split(' (')[0] || 'Sin dato';
        if (!porAlc[alc]) porAlc[alc] = { total: 0, busqueda: 0, resguardo: 0, localizado: 0 };
        porAlc[alc].total++;
        if (r.estatus === 'En Búsqueda') porAlc[alc].busqueda++;
        else if (r.estatus === 'En Resguardo') porAlc[alc].resguardo++;
        else if (r.estatus === 'Localizado') porAlc[alc].localizado++;
      });
      const ranking = Object.entries(porAlc).map(([nombre, d]) => ({
        nombre, ...d, tasa: d.total > 0 ? (d.localizado / d.total) * 100 : 0
      })).sort((a, b) => b.tasa - a.tasa || b.total - a.total);
      if (!ranking.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Sin datos</td></tr>';
        return;
      }
      ranking.forEach((row, i) => {
        const tasaColor = row.tasa >= 40 ? 'text-success' : (row.tasa >= 20 ? 'text-warning' : 'text-danger');
        const tr = document.createElement('tr');
        tr.innerHTML = '<td class="ps-3 fw-bold text-muted">' + (i + 1) + '</td>' +
          '<td><strong>' + row.nombre + '</strong></td>' +
          '<td class="text-center">' + row.total + '</td>' +
          '<td class="text-center"><span class="badge bg-danger">' + row.busqueda + '</span></td>' +
          '<td class="text-center"><span class="badge bg-warning text-dark">' + row.resguardo + '</span></td>' +
          '<td class="text-center"><span class="badge bg-success">' + row.localizado + '</span></td>' +
          '<td class="text-end pe-3 fw-bold ' + tasaColor + '">' + row.tasa.toFixed(1) + '%</td>';
        tbody.appendChild(tr);
      });
    }

    function generarInsightsPanel() {
      const lista = document.getElementById('listaInsights');
      if (!lista) return;
      const total = state.registros.length;
      if (!total) { lista.innerHTML = '<li class="text-muted small">Aún no hay registros para analizar.</li>'; return; }

      const activas = state.registros.filter(r => r.estatus === 'En Búsqueda').length;
      const localizadas = state.registros.filter(r => r.estatus === 'Localizado').length;
      const resguardo = state.registros.filter(r => r.estatus === 'En Resguardo').length;
      const totalAvist = state.registros.reduce((s, r) => s + (r.avistamientos ? r.avistamientos.length : 0), 0);
      const sinAvist = state.registros.filter(r => r.estatus === 'En Búsqueda' && (!r.avistamientos || r.avistamientos.length === 0)).length;
      const conChip = state.registros.filter(r => { const c = (r.chip || '').toLowerCase(); return c && !c.includes('no posee'); }).length;
      const pctChip = ((conChip / total) * 100).toFixed(0);
      const tasaLoc = ((localizadas / total) * 100).toFixed(1);

      const conteoAlc = {};
      state.registros.forEach(r => { const a = (r.alcaldia || '').split(' ')[0]; conteoAlc[a] = (conteoAlc[a] || 0) + 1; });
      const topAlc = Object.entries(conteoAlc).sort((a, b) => b[1] - a[1])[0];
      const conteoTam = {};
      state.registros.forEach(r => { conteoTam[r.tamano] = (conteoTam[r.tamano] || 0) + 1; });
      const topTam = Object.entries(conteoTam).sort((a, b) => b[1] - a[1])[0];

      const insights = [];
      insights.push('<li class="mb-2 small"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>' + tasaLoc + '%</strong> de los casos han sido localizados y reunificados.</li>');
      if (activas > 0) insights.push('<li class="mb-2 small"><i class="bi bi-broadcast text-danger me-2"></i>Hay <strong>' + activas + '</strong> alerta' + (activas !== 1 ? 's' : '') + ' activa' + (activas !== 1 ? 's' : '') + ' en búsqueda.</li>');
      if (sinAvist > 0) insights.push('<li class="mb-2 small"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i><strong>' + sinAvist + '</strong> caso' + (sinAvist !== 1 ? 's' : '') + ' en búsqueda sin ningún avistamiento reportado.</li>');
      if (totalAvist > 0) insights.push('<li class="mb-2 small"><i class="bi bi-geo-alt-fill text-info me-2"></i>La comunidad ha enviado <strong>' + totalAvist + '</strong> avistamiento' + (totalAvist !== 1 ? 's' : '') + ' en total.</li>');
      insights.push('<li class="mb-2 small"><i class="bi bi-cpu-fill me-2" style="color:#6f42c1;"></i><strong>' + pctChip + '%</strong> de las mascotas cuentan con microchip o tatuaje.</li>');
      if (topAlc) insights.push('<li class="mb-2 small"><i class="bi bi-geo-fill text-primary me-2"></i>La alcaldía con más reportes es <strong>' + topAlc[0] + '</strong> (' + topAlc[1] + ' casos).</li>');
      if (topTam) insights.push('<li class="mb-2 small"><i class="bi bi-bounding-box text-success me-2"></i>Tamaño más frecuente: <strong>' + topTam[0] + '</strong> (' + topTam[1] + ' casos).</li>');
      if (resguardo > 0) insights.push('<li class="mb-0 small"><i class="bi bi-house-heart-fill text-warning me-2"></i><strong>' + resguardo + '</strong> mascota' + (resguardo !== 1 ? 's' : '') + ' en resguardo temporal o albergue.</li>');
      lista.innerHTML = insights.join('');
    }

        function exportarResumenCSV_inline() {
      if (typeof window.exportarResumenCSV === 'function') {
        try { return window.exportarResumenCSV(); } catch (e) { console.warn(e); }
      }
      if (!state.registros || !state.registros.length) {
        if (typeof rupeToast === 'function') rupeToast('No hay registros para exportar.', 'warning');
        return;
      }
      const headers = ['Folio','Nombre','Raza','Sexo','Tamaño','Color','Fecha','Alcaldía','Estatus','Avistamientos','Chip'];
      const rows = state.registros.map(r => [r.folio,r.nombre,r.raza,r.sexo,r.tamano,r.color,r.fecha,(r.alcaldia||'').replace(/,/g,' '),r.estatus,(r.avistamientos||[]).length,r.chip||'']);
      const csv = [headers.join(',')].concat(rows.map(row => row.map(c => '"'+String(c).replace(/"/g,'""')+'"').join(','))).join('\n');
      const blob = new Blob(['\ufeff'+csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = 'RUPE_CDMX_resumen_'+new Date().toISOString().slice(0,10)+'.csv';
      a.click(); URL.revokeObjectURL(url);
    }
    // No sobrescribir la versión de tutor-analitico.js?v=20260924fast'function') {
      window.exportarResumenCSV = exportarResumenCSV_inline;
    }
    function actualizarSelectoresFolioTutor() {
      const selFicha = document.getElementById('tutorSelectFolioFicha');
      const selRapido = document.getElementById('tutorSelectFolioRapido');
      const datalist = document.getElementById('tutorListaFolios');
      if (!selFicha || !selRapido || !datalist) return;

      selFicha.innerHTML = state.registros.length ? '' : '<option value="">— Sin registros —</option>';
      selRapido.innerHTML = '<option value="">— Folios recientes —</option>';
      datalist.innerHTML = '';

      state.registros.forEach(r => {
        const opt1 = new Option(`#${r.folio} · ${r.nombre}`, r.folio);
        const opt2 = new Option(`#${r.folio} · ${r.nombre}`, r.folio);
        const opt3 = document.createElement('option');
        opt3.value = r.folio;
        selFicha.add(opt1);
        selRapido.add(opt2);
        datalist.appendChild(opt3);
      });

      if (state.folioSeleccionado) {
        selFicha.value = state.folioSeleccionado;
        selRapido.value = state.folioSeleccionado;
      }
    }




    function renderizarFichaTutor(reg) {
      const contVacio = document.getElementById('tutorContenedorFichaVacia');
      const cont = document.getElementById('tutorContenedorFicha');
      if (!reg || !reg.folio) {
        if (contVacio) contVacio.classList.remove('d-none');
        if (cont) cont.classList.add('d-none');
        return;
      }
      if (contVacio) contVacio.classList.add('d-none');
      if (cont) cont.classList.remove('d-none');

      // Completar campos desde RUPE_INIT / state si faltan
      try {
        const pool = [].concat(
          (window.RUPE_INIT && window.RUPE_INIT.mascotas) || [],
          (window.state && window.state.registros) || []
        );
        const full = pool.find(function (r) {
          return r && (String(r.folio) === String(reg.folio) || String(r.id || r.id_mascota) === String(reg.id || reg.id_mascota || ''));
        });
        if (full) {
          reg = Object.assign({}, full, reg);
        }
      } catch (_) {}

      const folio = String(reg.folio || '');
      const token = reg.qr_token || folio;
      const base = (window.location && window.location.origin) ? window.location.origin : '';
      const enlaceQR = base + '/mascota/' + encodeURIComponent(token);
      const qrImg = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=8&data=' + encodeURIComponent(enlaceQR);

      const disp = function (val, emptyLabel) {
        if (val === null || val === undefined) return emptyLabel || 'No registrado';
        const s = String(val).trim();
        if (s === '' || s === '—' || s.toLowerCase() === 'null' || s.toLowerCase() === 'undefined') {
          return emptyLabel || 'No registrado';
        }
        return s;
      };
      const setT = function (id, val, emptyLabel) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = disp(val, emptyLabel);
      };
      const setSrc = function (id, src, especie) {
        const el = document.getElementById(id);
        if (!el) return;
        const esp = String(especie || reg.especie || 'perro').toLowerCase();
        const ph = esp.indexOf('gato') >= 0
          ? '/assets/img/placeholder-gato.svg'
          : '/assets/img/placeholder-perro.svg';
        let url = src && String(src).trim() !== '' && String(src).toLowerCase() !== 'null'
          ? String(src).trim()
          : ph;
        if (url.indexOf('http') !== 0 && url.indexOf('data:') !== 0 && url.charAt(0) !== '/') {
          url = '/' + url;
        }
        el.removeAttribute('hidden');
        el.style.display = 'block';
        el.style.visibility = 'visible';
        el.style.maxHeight = '240px';
        el.style.width = '100%';
        el.style.objectFit = 'cover';
        el.style.background = '#e9ecef';
        el.style.minHeight = '140px';
        el.alt = String(reg.nombre || 'Mascota');
        el.onerror = function () {
          this.onerror = null;
          this.src = ph;
        };
        el.src = url;
      };

      const razaTxt = reg.raza || reg.raza_nombre || '';
      const alcTxt = reg.alcaldia || reg.alcaldia_nombre || '';
      const colTxt = reg.colonia || reg.direccion_ref || '';
      const fotoSrc = reg.foto || reg.foto_url || '';
      const sexoRaw = String(reg.sexo || '').toLowerCase();
      const sexoTxt = sexoRaw === 'macho' || sexoRaw === 'Macho' ? 'Macho'
        : (sexoRaw === 'hembra' || sexoRaw === 'Hembra' ? 'Hembra'
        : (sexoRaw && sexoRaw !== 'desconocido' ? reg.sexo : ''));
      const edadTxt = reg.edad_aprox || reg.edad || '';
      const colorTxt = reg.color || '';
      const fechaTxt = reg.fecha || reg.fecha_extravio || '';
      const senasRaw = String(reg.senas || reg.senas_particulares || '');
      // Extraer Tamaño / Collar / Chip del texto compuesto guardado en BD
      function extractMeta(senas) {
        const o = { tamano: '', collar: '', chip: '', limpio: senas };
        if (!senas) return o;
        let m = senas.match(/Tamaño\s*:\s*([^|]+)/i);
        if (m) o.tamano = m[1].trim();
        m = senas.match(/Collar\s*:\s*([^|]+)/i);
        if (m) o.collar = m[1].trim();
        m = senas.match(/Chip(?:\/tatuaje)?\s*:\s*([^|]+)/i);
        if (m) o.chip = m[1].trim();
        o.limpio = senas
          .replace(/(?:Raza|Tamaño|Collar|Chip(?:\/tatuaje)?|Hora extravío)\s*:\s*[^|]*/gi, '')
          .replace(/\s*\|\s*\|\s*/g, ' | ')
          .replace(/^\s*\|\s*|\s*\|\s*$/g, '')
          .trim();
        return o;
      }
      const meta = extractMeta(senasRaw);
      let tamanoTxt = reg.tamano || meta.tamano || '';
      let collarTxt = reg.collar || meta.collar || '';
      let chipTxt = reg.microchip || reg.chip || meta.chip || '';
      if (collarTxt && chipTxt) collarTxt = collarTxt + ' · Microchip: ' + chipTxt;
      else if (!collarTxt && chipTxt) collarTxt = 'Microchip: ' + chipTxt;
      const senasTxt = meta.limpio || '';
      const especieTxt = reg.especie || '';
      const estatusTxt = reg.estatus || '';
      const gpsTxt = (reg.latitud !== null && reg.latitud !== undefined && String(reg.latitud) !== ''
          && reg.longitud !== null && reg.longitud !== undefined && String(reg.longitud) !== '')
        ? (Number(reg.latitud).toFixed(5) + ', ' + Number(reg.longitud).toFixed(5))
        : '';

      setT('tutorFichaFolio', '#' + folio, '—');
      setT('tutorFichaFechaGen', 'Generada: ' + new Date().toLocaleString('es-MX'), '');
      setSrc('tutorFichaFoto', fotoSrc, especieTxt);
      setT('tutorFichaNombre', String(reg.nombre || '').toUpperCase(), 'SIN NOMBRE');
      setT('tutorFichaRaza', [especieTxt ? (String(especieTxt).charAt(0).toUpperCase() + String(especieTxt).slice(1)) : '', razaTxt].filter(Boolean).join(' · '), 'No registrada');
      setT('tutorFichaSexoTamano', [sexoTxt, tamanoTxt].filter(Boolean).join(' · ') || 'No registrado', 'No registrado');
      setT('tutorFichaEdad', edadTxt ? (String(edadTxt).match(/año|mes/i) ? edadTxt : (edadTxt + ' año(s)')) : '', 'No registrada');
      setT('tutorFichaColor', colorTxt, 'No registrado');
      setT('tutorFichaLugar', [alcTxt, colTxt ? ('Col. ' + colTxt) : ''].filter(Boolean).join(', '), 'No registrada');
      setT('tutorFichaFecha', fechaTxt, 'No registrada');
      setT('tutorFichaCollar', (reg.collar || meta.collar || ''), 'No registrado');
      setT('tutorFichaMicrochip', (reg.microchip || reg.chip || meta.chip || ''), 'No registrado');
      setT('tutorFichaSenas', senasTxt, 'Sin señas registradas');
      setT('tutorFichaEnlaceQR', enlaceQR, '');
      setT('tutorFichaEstatus', estatusTxt, 'En Búsqueda');
      setT('tutorFichaGps', gpsTxt, 'Sin GPS capturado');
      setSrc('tutorFichaQR', qrImg);

      if (typeof QRCode !== 'undefined') {
        let box = document.getElementById('tutorQrCanvasBox');
        const img = document.getElementById('tutorFichaQR');
        if (!box && img && img.parentElement) {
          box = document.createElement('div');
          box.id = 'tutorQrCanvasBox';
          box.className = 'd-flex justify-content-center mb-2';
          img.parentElement.insertBefore(box, img);
        }
        if (box) {
          try {
            box.innerHTML = '';
            new QRCode(box, { text: enlaceQR, width: 128, height: 128 });
            if (img) img.style.display = 'none';
          } catch (e) {
            if (img) img.style.display = '';
          }
        }
      }

      const sel = document.getElementById('tutorSelectFolioFicha');
      if (sel && typeof state !== 'undefined' && state && Array.isArray(state.registros)) {
        const cur = folio;
        sel.innerHTML = state.registros.map(function (r) {
          return '<option value="' + String(r.folio || '').replace(/"/g, '&quot;') + '">' +
            String(r.folio || '') + ' — ' + String(r.nombre || '') + '</option>';
        }).join('');
        try { sel.value = cur; } catch (_) {}
      }
      if (typeof state !== 'undefined' && state) state.folioSeleccionado = folio;
    }
    window.renderizarFichaTutor = renderizarFichaTutor;



    function cambiarFolioFichaTutor() {
      const folio = document.getElementById('tutorSelectFolioFicha').value;
      const reg = state.registros.find(r => r.folio === folio);
      state.folioSeleccionado = folio || state.folioSeleccionado;
      renderizarFichaTutor(reg);
    }

    function imprimirFichaTutor() {
      if (!state.folioSeleccionado) {
        alert('No hay ficha para imprimir.');
        return;
      }
      window.print();
    }

    // ========== SEGUIMIENTO (vista rápida del tutor) ==========
    function buscarPorFolioTutor() {
      const inp = document.getElementById('tutorInputBuscarFolio');
      const folio = (inp ? inp.value : '').trim().replace(/^#/, '');
      const list = (state && state.registros) || [];
      const reg = list.find(r => r && (String(r.folio) === folio || String(r.folio).toUpperCase() === folio.toUpperCase()));
      if (!reg) {
        const v = document.getElementById('tutorSeguimientoVacio');
        const c = document.getElementById('tutorSeguimientoContenido');
        if (v) v.classList.remove('d-none');
        if (c) c.classList.add('d-none');
        if (folio && typeof rupeToast === 'function') rupeToast('No se encontró el folio: ' + folio, 'warning');
        return;
      }
      state.folioSeleccionado = reg.folio;
      if (typeof renderizarSeguimientoTutor === 'function') renderizarSeguimientoTutor(reg);
    }

    function renderizarSeguimientoTutor(reg) {
      document.getElementById('tutorSeguimientoVacio').classList.add('d-none');
      document.getElementById('tutorSeguimientoContenido').classList.remove('d-none');

      document.getElementById('tutorSegFolio').textContent = '#' + reg.folio;
      document.getElementById('tutorSegNombre').textContent = reg.nombre + ' · ' + reg.raza;

      const badge = document.getElementById('tutorSegEstatusBadge');
      badge.textContent = reg.estatus;
      badge.className = 'badge ';
      if (reg.estatus === 'En Búsqueda') badge.classList.add('badge-estatus-busqueda');
      else if (reg.estatus === 'En Resguardo') badge.classList.add('badge-estatus-resguardo');
      else badge.classList.add('badge-estatus-localizado');

      document.getElementById('tutorSegUltimaAct').textContent = reg.bitacora;
      document.getElementById('tutorSegNumAvist').textContent = reg.avistamientos.length;
      document.getElementById('tutorSegContactoTutor').innerHTML = reg.tutorNombre
        ? `<strong>${reg.tutorNombre}</strong><br>${reg.tutorTel}<br><span class="text-muted">${reg.tutorEmail}</span>`
        : `<span class="text-muted">Sin datos de contacto capturados para este folio.</span>`;

      document.getElementById('tutorNuevoEstatus').value = reg.estatus.includes('Localizado') ? 'Localizado' : reg.estatus;

      // Avistamientos
      const cont = document.getElementById('tutorListaAvistamientos');
      if (reg.avistamientos.length === 0) {
        cont.innerHTML = '<p class="text-muted text-center py-4 mb-0">Aún no hay avistamientos reportados para este folio.</p>';
        return;
      }

      cont.innerHTML = reg.avistamientos.map((a, i) => `
        <div class="card avistamiento-card shadow-sm mb-3 ${i === 0 ? '' : ''}">
          <div class="card-body">
            <div class="row g-3 align-items-center">
              <div class="col-auto">
                <img src="${a.foto}" alt="Avistamiento" style="width:72px;height:72px;object-fit:cover;border-radius:8px;" class="border">
              </div>
              <div class="col">
                <div class="d-flex justify-content-between flex-wrap gap-1 mb-1">
                  <span class="badge ${a.condicion === 'Resguardado' ? 'bg-success' : 'bg-warning text-dark'}">${a.condicion}</span>
                  <small class="text-muted">${a.fechaHora}</small>
                </div>
                <p class="mb-1 small"><strong>Ubicación:</strong> <code>${a.lat}, ${a.lng}</code> · ${a.referencia}</p>
                <p class="mb-0 small"><strong>Reportado por:</strong> ${a.reporteroNombre || 'Ciudadano'} · ${a.reporteroTel || ''} · <span class="text-muted">${a.reporteroEmail || 'sin correo'}</span></p>
              </div>
            </div>
          </div>
        </div>
      `).join('');
    }

    async function cambiarEstatusTutor(e) {
      if (e && e.preventDefault) e.preventDefault();
      const reg = state.registros.find(r => r.folio === state.folioSeleccionado);
      if (!reg) {
        if (typeof mostrarToastTutor === 'function') mostrarToastTutor('Selecciona un folio primero.', 'warning');
        return;
      }
      const nuevo = (document.getElementById('tutorNuevoEstatus') || {}).value || '';
      const comentario = ((document.getElementById('tutorEstatusComentario') || {}).value || '').trim();
      if (!nuevo) {
        if (typeof mostrarToastTutor === 'function') mostrarToastTutor('Elige el nuevo estatus.', 'warning');
        return;
      }
      const bit = (comentario || ('Cambio de estatus a ' + nuevo)) + ' · ' + new Date().toLocaleString('es-MX');
      const id = reg.id || reg.id_mascota;
      try {
        if (id && typeof fetch === 'function') {
          const csrf = (window.RUPE_INIT && window.RUPE_INIT.csrf) || window.__CSRF__ || '';
          const fd = new FormData();
          fd.append('estatus', nuevo);
          fd.append('notas', bit);
          if (csrf) fd.append('_csrf', csrf);
          const res = await fetch('/api/tutor/mascotas/' + id, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
          });
          if (comentario && res.ok) {
            await fetch('/api/tutor/mascotas/' + id + '/seguimiento', {
              method: 'POST',
              credentials: 'same-origin',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
              body: JSON.stringify({ tipo: 'cambio_estatus', descripcion: bit, _csrf: csrf })
            });
          }
        }
        reg.estatus = nuevo;
        reg.bitacora = bit;
        reg.notas = bit;
        const ce = document.getElementById('tutorEstatusComentario');
        if (ce) ce.value = '';
        if (typeof renderizarSeguimientoTutor === 'function') renderizarSeguimientoTutor(reg);
        if (typeof renderizarModuloEstatus5 === 'function') renderizarModuloEstatus5();
        if (typeof renderizarTodo === 'function') renderizarTodo();
        if (typeof mostrarToastTutor === 'function') mostrarToastTutor('Estatus actualizado a: ' + nuevo);
      } catch (err) {
        console.error(err);
        if (typeof mostrarToastTutor === 'function') mostrarToastTutor('No se pudo guardar el estatus.', 'error');
      }
    }
    window.cambiarEstatusTutor = cambiarEstatusTutor;

    // Simular un avistamiento ciudadano (demo)
    function simularAvistamientoTutor() {
      const reg = state.registros.find(r => r.folio === state.folioSeleccionado);
      if (!reg) return;

      const av = {
        fechaHora: new Date().toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' }),
        condicion: Math.random() > 0.5 ? 'Visto en vía pública' : 'Resguardado',
        foto: reg.foto,
        lat: (19.3 + Math.random() * 0.2).toFixed(4),
        lng: (-99.2 + Math.random() * 0.15).toFixed(4),
        referencia: ['Cerca del Metro', 'Parque local', 'Calle secundaria', 'Fuera de clínica veterinaria'][Math.floor(Math.random()*4)],
        reporteroNombre: ['Ana L.', 'Carlos M.', 'Vecino anónimo', 'Brigada ciudadana'][Math.floor(Math.random()*4)],
        reporteroTel: '55 ' + Math.floor(1000 + Math.random()*9000) + ' ' + Math.floor(1000 + Math.random()*9000),
        reporteroEmail: 'ciudadano' + Math.floor(Math.random()*99) + '@mail.com'
      };
      reg.avistamientos.unshift(av);
      renderizarSeguimientoTutor(reg);
      renderizarTodo();
      mostrarToastTutor('Avistamiento simulado agregado.');
    }

    function mostrarToastTutor(msg, tipo) {
      if (typeof window.rupeToast === 'function') {
        window.rupeToast(msg, tipo || 'success', 4000);
        return;
      }
      const elMsg = document.getElementById('tutorToastMsg');
      const elOk = document.getElementById('tutorToastOk');
      if (elMsg && elOk && typeof bootstrap !== 'undefined') {
        elMsg.textContent = msg;
        const t = tipo || 'success';
        elOk.className = 'toast align-items-center border-0 text-bg-' + (t === 'error' ? 'danger' : t === 'warning' ? 'warning' : t === 'info' ? 'info' : 'success');
        const toast = new bootstrap.Toast(elOk, { delay: 3500 });
        toast.show();
      } else {
        console.log('[RUPE]', msg);
      }
    }
    window.mostrarToastTutor = mostrarToastTutor;

    function inicializarPanelTutor() {
      actualizarSelectoresFolioTutor();
      renderizarFichaTutor(state.registros.find(r => r.folio === state.folioSeleccionado) || state.registros[0] || null);
      renderizarPerfilTutor();
    }

    // ========== MÓDULO: MI PERFIL DEL TUTOR ==========

    // Navegación al módulo de perfil (se invoca desde el dropdown de usuario, fuera del mega menú)
    function mostrarModuloPerfil(e) {
      if (e) e.preventDefault();
      cerrarMegaMenu();
      document.querySelectorAll('.nav-modulo').forEach(nav => nav.classList.remove('active'));
      document.querySelectorAll('.modulo-seccion').forEach(sec => sec.classList.remove('activo'));
      const seccion = document.getElementById('modulo-perfil');
      if (seccion) seccion.classList.add('activo');
      actualizarBotonModulos('perfil');
      renderizarPerfilTutor();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function obtenerInicialesTutor(nombre) {
      return (nombre || '').split(' ').filter(Boolean).slice(0, 2).map(p => p[0].toUpperCase()).join('') || 'MT';
    }

    function formatearFechaMesAnio(fechaIso) {
      const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
      const partes = (fechaIso || '').split('-');
      if (partes.length < 2) return fechaIso || '—';
      return `${meses[parseInt(partes[1], 10) - 1] || ''} ${partes[0]}`;
    }

    // Pinta la tarjeta de datos personales, las estadísticas y la tabla de mascotas del tutor
    function renderizarPerfilTutor() {
      const t = state.tutorActual;
      const iniciales = obtenerInicialesTutor(t.nombre);

      const elAvatar = document.getElementById('perfilAvatarGrande');
      if (elAvatar) elAvatar.textContent = iniciales;
      const elNombre = document.getElementById('perfilNombreDisplay');
      if (elNombre) elNombre.textContent = t.nombre;
      const elTel = document.getElementById('perfilTelefonoDisplay');
      if (elTel) elTel.textContent = t.telefono;
      const elCorreo = document.getElementById('perfilCorreoDisplay');
      if (elCorreo) elCorreo.textContent = t.correo;
      const elPriv = document.getElementById('perfilPrivacidadDisplay');
      if (elPriv) elPriv.textContent = t.aceptaPrivacidad ? 'Aviso de privacidad aceptado' : 'Aviso de privacidad pendiente de aceptar';
      const elFecha = document.getElementById('perfilFechaRegistro');
      if (elFecha) elFecha.textContent = formatearFechaMesAnio(t.fechaRegistro);

      // Sincroniza el avatar y nombre que se ven en el menú superior (dropdown de usuario)
      document.querySelectorAll('.user-dropdown .avatar-mini').forEach(el => el.textContent = iniciales);
      if (t.nombre && String(t.nombre).trim()) {
        if (typeof window.rupeApplyUserName === 'function') {
          window.rupeApplyUserName(t.nombre);
        } else {
          const nombreNav = document.getElementById('tutorUserName') || document.querySelector('.user-dropdown .d-none.d-md-inline');
          if (nombreNav) nombreNav.textContent = t.nombre;
          const headerSpan = document.getElementById('tutorUserHeader');
          if (headerSpan) headerSpan.textContent = t.nombre;
          const headerNav = document.querySelector('.user-dropdown .dropdown-header');
          if (headerNav && !headerSpan) headerNav.textContent = `${t.nombre} · Cuenta gratuita`;
        }
      }

      // Estadísticas calculadas a partir de state.registros
      const total = state.registros.length;
      const activos = state.registros.filter(r => r.estatus === 'En Búsqueda' || r.estatus === 'En Resguardo').length;
      const resueltos = state.registros.filter(r => r.estatus === 'Localizado').length;
      const elTotal = document.getElementById('perfilStatTotal');
      if (elTotal) elTotal.textContent = total;
      const elActivos = document.getElementById('perfilStatActivos');
      if (elActivos) elActivos.textContent = activos;
      const elResueltos = document.getElementById('perfilStatResueltos');
      if (elResueltos) elResueltos.textContent = resueltos;

      // Tabla de mascotas registradas
      const mapaBadge = { 'En Búsqueda': 'danger', 'En Resguardo': 'info text-dark', 'Localizado': 'success' };
      const cuerpo = document.getElementById('tbodyPerfilMascotas');
      if (cuerpo) {
        cuerpo.innerHTML = state.registros.map(r => `
          <tr>
            <td class="ps-3 fw-bold small">${r.folio}</td>
            <td class="small">${r.nombre} <span class="text-muted">(${r.raza})</span></td>
            <td class="small">${r.alcaldia}</td>
            <td><span class="badge bg-${mapaBadge[r.estatus] || 'secondary'}">${r.estatus}</span></td>
            <td class="text-end pe-3">
              <button class="btn btn-sm btn-outline-secondary" onclick="irAFichaDesdePerfil('${r.folio}')">
                <i class="bi bi-eye"></i> Ver ficha
              </button>
            </td>
          </tr>
        `).join('');
      }
    }

    // Desde "Mis mascotas registradas" en el perfil, salta directo a la Ficha Oficial + QR de ese folio
    function irAFichaDesdePerfil(folio) {
      state.folioSeleccionado = folio;
      document.querySelectorAll('.nav-modulo').forEach(nav => nav.classList.remove('active'));
      document.querySelectorAll('.modulo-seccion').forEach(sec => sec.classList.remove('activo'));
      const seccion = document.getElementById('modulo-ficha-pdf');
      if (seccion) seccion.classList.add('activo');
      actualizarBotonModulos('ficha-pdf');
      actualizarSelectoresFolioTutor();
      const selector = document.getElementById('tutorSelectFolioFicha');
      if (selector) selector.value = folio;
      renderizarFichaTutor(state.registros.find(r => r.folio === folio) || null);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function abrirModalEditarPerfil() {
      const t = state.tutorActual;
      document.getElementById('perfilInputNombre').value = t.nombre;
      document.getElementById('perfilInputTelefono').value = t.telefono;
      document.getElementById('perfilInputCorreo').value = t.correo;
      document.getElementById('perfilInputPrivacidad').checked = t.aceptaPrivacidad;
      modalEditarPerfilBs.show();
    }

    function guardarPerfilTutor(e) {
      e.preventDefault();
      state.tutorActual.nombre = document.getElementById('perfilInputNombre').value.trim();
      state.tutorActual.telefono = document.getElementById('perfilInputTelefono').value.trim();
      state.tutorActual.correo = document.getElementById('perfilInputCorreo').value.trim();
      state.tutorActual.aceptaPrivacidad = document.getElementById('perfilInputPrivacidad').checked;
      // En producción: UPDATE tutor SET nombre=?, telefono=?, correo=?, acepta_privacidad=? WHERE id_tutor=?
      modalEditarPerfilBs.hide();
      renderizarPerfilTutor();
      mostrarToastTutor('Tus datos de contacto se actualizaron correctamente.');
    }

    // ========== MÓDULO: CONFIGURACIÓN DE CUENTA ==========

    function mostrarModuloConfiguracion(e) {
      if (e) e.preventDefault();
      cerrarMegaMenu();
      document.querySelectorAll('.nav-modulo').forEach(nav => nav.classList.remove('active'));
      document.querySelectorAll('.modulo-seccion').forEach(sec => sec.classList.remove('activo'));
      const seccion = document.getElementById('modulo-configuracion');
      if (seccion) seccion.classList.add('activo');
      actualizarBotonModulos('configuracion');
      renderizarConfiguracion();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Pinta los controles de configuración con los valores guardados en state.preferencias
    function renderizarConfiguracion() {
      const p = state.preferencias;
      const campo = (id) => document.getElementById(id);

      if (campo('cfgNotifEmail')) campo('cfgNotifEmail').checked = p.notifEmail;
      if (campo('cfgNotifSms')) campo('cfgNotifSms').checked = p.notifSms;
      if (campo('cfgNotifAvistamiento')) campo('cfgNotifAvistamiento').checked = p.notifNuevoAvistamiento;
      if (campo('cfgNotifEstatus')) campo('cfgNotifEstatus').checked = p.notifCambioEstatus;
      if (campo('cfgNotifBoletin')) campo('cfgNotifBoletin').checked = p.notifBoletinRUPE;
      if (campo('cfgIdioma')) campo('cfgIdioma').value = p.idioma;
      if (campo('cfgAlcaldiaDefault')) campo('cfgAlcaldiaDefault').value = p.alcaldiaDefault;

      const elPriv = document.getElementById('cfgEstatusPrivacidad');
      if (elPriv) {
        elPriv.textContent = state.tutorActual.aceptaPrivacidad ? 'Aceptado' : 'Pendiente de aceptar';
        elPriv.className = 'fw-semibold ' + (state.tutorActual.aceptaPrivacidad ? 'text-success' : 'text-warning');
      }
    }

    // Guarda cualquier cambio de un switch/select de la sección Notificaciones / Preferencias
    function guardarPreferencias() {
      const campo = (id) => document.getElementById(id);
      state.preferencias.notifEmail = campo('cfgNotifEmail').checked;
      state.preferencias.notifSms = campo('cfgNotifSms').checked;
      state.preferencias.notifNuevoAvistamiento = campo('cfgNotifAvistamiento').checked;
      state.preferencias.notifCambioEstatus = campo('cfgNotifEstatus').checked;
      state.preferencias.notifBoletinRUPE = campo('cfgNotifBoletin').checked;
      state.preferencias.idioma = campo('cfgIdioma').value;
      state.preferencias.alcaldiaDefault = campo('cfgAlcaldiaDefault').value;
      // En producción: INSERT ... ON DUPLICATE KEY UPDATE en preferencias_usuario (ver rupe_cdmx_3fn.sql)
      mostrarToastTutor('Preferencias actualizadas.');
    }

    // Cambio de contraseña (simulado en el cliente; en producción valida contra usuario.contrasena_hash)
    function cambiarContrasenaTutor(e) {
      e.preventDefault();
      const actual = document.getElementById('cfgPassActual').value;
      const nueva = document.getElementById('cfgPassNueva').value;
      const confirmar = document.getElementById('cfgPassConfirmar').value;

      if (nueva.length < 8) {
        alert('La nueva contraseña debe tener al menos 8 caracteres.');
        return;
      }
      if (nueva !== confirmar) {
        alert('La nueva contraseña y su confirmación no coinciden.');
        return;
      }
      // En producción: verificar `actual` contra usuario.contrasena_hash antes de aplicar el UPDATE
      document.getElementById('formCambiarContrasena').reset();
      mostrarToastTutor('Contraseña actualizada correctamente.');
    }

    // Exporta un JSON con los datos personales y folios del tutor (portabilidad de datos)
    function descargarMisDatos() {
      const datos = {
        tutor: state.tutorActual,
        preferencias: state.preferencias,
        reportes: state.registros.map(r => ({
          folio: r.folio, mascota: r.nombre, estatus: r.estatus,
          alcaldia: r.alcaldia, fecha: r.fecha
        }))
      };
      const blob = new Blob([JSON.stringify(datos, null, 2)], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const enlace = document.createElement('a');
      enlace.href = url;
      enlace.download = 'mis-datos-rupe-cdmx.json';
      document.body.appendChild(enlace);
      enlace.click();
      document.body.removeChild(enlace);
      URL.revokeObjectURL(url);
    }

    // Confirmación final del modal "Eliminar mi cuenta"
    function confirmarEliminarCuenta() {
      // En producción: DELETE FROM tutor WHERE id_tutor=? (o baja lógica),
      // conservando 'reporte' con id_tutor en NULL para no perder el historial de casos.
      mostrarToastTutor('Solicitud de eliminación de cuenta enviada. Recibirás un correo de confirmación.');
    }

    // ========== MÓDULO: CENTRO DE AYUDA ==========

    function mostrarModuloAyuda(e) {
      if (e) e.preventDefault();
      cerrarMegaMenu();
      document.querySelectorAll('.nav-modulo').forEach(nav => nav.classList.remove('active'));
      document.querySelectorAll('.modulo-seccion').forEach(sec => sec.classList.remove('activo'));
      const seccion = document.getElementById('modulo-ayuda');
      if (seccion) seccion.classList.add('activo');
      actualizarBotonModulos('ayuda');

      const elCorreo = document.getElementById('ayudaCorreoRespuesta');
      if (elCorreo) elCorreo.textContent = state.tutorActual.correo;

      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Salta a cualquier módulo (numerado o de cuenta) desde la "Guía rápida" del Centro de Ayuda.
    // Reutiliza el mismo botón/listener del mega menú cuando existe, para no duplicar su lógica especial
    // (mapa de gps-qr, gráficas de análisis, selectores de ficha-pdf, etc.).
    function irAModuloDesdeAyuda(clave) {
      cerrarMegaMenu();
      const boton = document.querySelector(`.nav-modulo[data-modulo="${clave}"]`);
      if (boton) {
        boton.click();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }
      if (clave === 'perfil') return mostrarModuloPerfil();
      if (clave === 'configuracion') return mostrarModuloConfiguracion();
    }

    // Filtra las preguntas frecuentes por texto (pregunta + palabras clave de data-ayuda-texto)
    function filtrarAyuda() {
      const texto = document.getElementById('ayudaBuscador').value.trim().toLowerCase();
      const items = document.querySelectorAll('#acordionAyudaFAQ [data-ayuda-item]');
      let visibles = 0;

      items.forEach(item => {
        const pregunta = item.querySelector('.accordion-button').textContent.toLowerCase();
        const claves = (item.dataset.ayudaTexto || '').toLowerCase();
        const coincide = !texto || pregunta.includes(texto) || claves.includes(texto);
        item.classList.toggle('d-none', !coincide);
        if (coincide) visibles++;
      });

      document.getElementById('ayudaSinResultados').classList.toggle('d-none', visibles !== 0);
    }

    // Envío del formulario de contacto (simulado en el cliente)
    function enviarMensajeAyuda(e) {
      e.preventDefault();
      const asunto = document.getElementById('ayudaAsunto').value;
      // En producción: INSERT INTO mensaje_soporte (id_usuario, asunto, mensaje, fecha) VALUES (...)
      document.getElementById('formContactoAyuda').reset();
      mostrarToastTutor(`Tu mensaje sobre "${asunto}" fue enviado. Te responderemos pronto.`);
    }

  </script>
  <script defer src="/assets/js/tutor-ayuda.js?v=20260924fast"></script>

  <script defer src="/assets/js/tutor-cuenta.js?v=20260924fast"></script>

  <script defer src="/assets/js/tutor-perfil.js?v=20260924fast"></script>

  <script defer src="/assets/js/tutor-albergues.js?v=20260925alb1"></script>


  <script>
    // Auto-navegación: abre el módulo "crud" al cargar este archivo independiente
    window.addEventListener('load', () => {
      setTimeout(() => {
        const btn = document.querySelector('[data-modulo="crud"]');
        if (btn) btn.click();
      }, 50);
    });
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
    document.querySelectorAll('#btnUserDropdown #tutorUserName, #btnUserDropdown .tutor-user-label').forEach(function (n) {
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

<script defer src="/assets/lib/qrcode.min.js"></script>
<!-- CRUD con datos reales de BD (inyectados en RUPE_INIT) — sin api.js -->
<script defer src="/assets/js/tutor-crud.js?v=20260926fix2"></script>
<script src="/assets/js/tutor-registro.js?v=20260924fast" defer></script>
<script src="/assets/js/tutor-app.js?v=20260926fix2" defer></script>
<script>
/* Tras cargar, re-pintar tabla desde RUPE_INIT / state */
(function () {
  function paint() {
    if (window.RUPECrud && typeof window.RUPECrud.renderTablaCrud === 'function') {
      window.RUPECrud.renderTablaCrud();
    } else if (typeof renderizarTablaCrud === 'function') {
      renderizarTablaCrud();
    }
  }
  if (document.readyState === 'complete') setTimeout(paint, 300);
  else window.addEventListener('load', function () { setTimeout(paint, 300); });
})();
</script>


<!-- TABLA CRUD 11 columnas — datos reales BD -->
<script defer src="/assets/js/tutor-tabla-crud.js?v=20260926fix2"></script>

<script defer src="/assets/js/tutor-edit-save.js?v=20260926fix2"></script>
<script defer src="/assets/js/tutor-ficha.js?v=20260924fast"></script>


<script>
(function(){
  function repaint(){
    if (typeof window.pintarTablaCrud === 'function') window.pintarTablaCrud();
  }
  if (document.readyState === 'complete') { setTimeout(repaint, 100); setTimeout(repaint, 600); }
  else window.addEventListener('load', function(){ setTimeout(repaint, 100); setTimeout(repaint, 600); });
})();
</script>

<script defer src="/assets/js/tutor-seguimiento.js?v=20260925one1"></script>
<script defer src="/assets/js/tutor-estatus.js?v=20260925estval1"></script>
<script defer src="/assets/js/tutor-analitico.js?v=20260924fast"></script>

  <script>
  (function(){
    function closeNav(){
      var c=document.getElementById('rupeNavCollapse');
      var t=document.getElementById('rupeNavToggler');
      if(c){ c.classList.remove('show'); }
      if(t){ t.setAttribute('aria-expanded','false'); }
    }
    document.addEventListener('click', function(e){
      var link=e.target.closest && e.target.closest('.rupe-nav-link.nav-modulo');
      if(link && window.matchMedia('(max-width: 1199.98px)').matches){
        setTimeout(closeNav, 80);
      }
      // click fuera cierra
      var wrap=document.querySelector('.rupe-nav-wrapper');
      var col=document.getElementById('rupeNavCollapse');
      if(col && col.classList.contains('show') && wrap && !wrap.contains(e.target)){
        closeNav();
      }
    });
    window.rupeNavCloseOnPick=true;
  })();
  </script>

  <script src="/assets/js/rupe-responsive-nav.js?v=20261003menu" defer></script>

<script>
/* Boot CRUD definitivo: sembrar state y pintar tabla 11 columnas */
(function () {
  function norm(m) {
    if (!m || typeof m !== 'object') return null;
    return {
      id: m.id || m.id_mascota || 0,
      id_mascota: m.id_mascota || m.id || 0,
      folio: String(m.folio || ''),
      nombre: String(m.nombre || ''),
      raza: String(m.raza || m.raza_nombre || ''),
      raza_nombre: String(m.raza_nombre || m.raza || ''),
      id_raza: m.id_raza || 0,
      alcaldia: String(m.alcaldia || m.alcaldia_nombre || ''),
      alcaldia_nombre: String(m.alcaldia_nombre || m.alcaldia || ''),
      id_alcaldia: m.id_alcaldia || 0,
      colonia: String(m.colonia || ''),
      calle: String(m.calle || ''),
      direccion_ref: String(m.direccion_ref || ''),
      referencia: String(m.referencia || ''),
      fecha: String(m.fecha || m.fecha_extravio || ''),
      fecha_extravio: String(m.fecha_extravio || m.fecha || ''),
      estatus: String(m.estatus || 'En Búsqueda'),
      foto: String(m.foto || m.foto_url || ''),
      foto_url: String(m.foto_url || m.foto || ''),
      especie: String(m.especie || 'perro'),
      qr_token: String(m.qr_token || '')
    };
  }
  function seed() {
    var list = [];
    try {
      if (window.RUPE_INIT && Array.isArray(window.RUPE_INIT.mascotas) && window.RUPE_INIT.mascotas.length) {
        list = window.RUPE_INIT.mascotas;
      }
    } catch (e) {}
    if (!list.length) {
      var el = document.getElementById('rupeMascotasJson');
      if (el) {
        try {
          var p = JSON.parse(el.textContent || '[]');
          if (Array.isArray(p) && p.length) list = p;
        } catch (e2) {}
      }
    }
    if (!list.length && window.state && Array.isArray(window.state.registros) && window.state.registros.length) {
      list = window.state.registros;
    }
    list = list.map(norm).filter(Boolean);
    if (!window.state) window.state = {};
    if (list.length) {
      window.state.registros = list;
      try {
        if (window.RUPE_INIT) window.RUPE_INIT.mascotas = list.slice();
      } catch (e3) {}
    }
    return list;
  }
  function paint() {
    seed();
    try {
      if (typeof window.pintarTablaCrud === 'function') {
        window.pintarTablaCrud();
        return true;
      }
    } catch (e) {}
    try {
      if (window.RUPECrud && typeof window.RUPECrud.renderTablaCrud === 'function') {
        window.RUPECrud.renderTablaCrud();
        return true;
      }
    } catch (e2) {}
    return false;
  }
  function api() {
    var csrf = '';
    try { csrf = (window.RUPE_INIT && window.RUPE_INIT.csrf) || window.__CSRF__ || ''; } catch (e) {}
    return fetch('/api/tutor/mascotas', {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-Token': csrf
      }
    })
      .then(function (r) { return r.json().catch(function () { return {}; }); })
      .then(function (data) {
        var arr = (data && data.data && data.data.mascotas) || (data && data.mascotas) || [];
        if (!Array.isArray(arr) || !arr.length) return false;
        if (!window.state) window.state = {};
        window.state.registros = arr.map(norm).filter(Boolean);
        try {
          if (window.RUPE_INIT) window.RUPE_INIT.mascotas = window.state.registros.slice();
        } catch (e) {}
        paint();
        return true;
      })
      .catch(function () { return false; });
  }
  function boot() {
    paint();
    api().then(function (ok) {
      if (!ok) paint();
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      setTimeout(boot, 120);
      setTimeout(paint, 700);
    });
  } else {
    setTimeout(boot, 120);
    setTimeout(paint, 700);
  }
  window.addEventListener('load', function () {
    setTimeout(boot, 250);
  });
})();
</script>

</body>
</html>