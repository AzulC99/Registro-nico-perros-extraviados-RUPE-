<?php
/**
 * RUPE CDMX — Configuración global
 * app/config/config.php
 * PHP 8.3.33 · sin .env · Hostinger
 */

declare(strict_types=1);

// -------------------------------------------------
// Entorno
// -------------------------------------------------
define('APP_NAME', 'RUPE CDMX');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', false); // true solo en desarrollo local

// URL pública (sin barra final)
define('APP_URL', 'https://buscandoahuellitasrupe.com.mx');

// Rutas de sistema
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . '/app');
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . '/public');
}

define('UPLOAD_PATH', PUBLIC_PATH . '/assets/uploads');
define('UPLOAD_URL', APP_URL . '/assets/uploads');
define('ASSETS_URL', APP_URL . '/assets');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('LOG_PATH', STORAGE_PATH . '/logs');

// -------------------------------------------------
// Base de datos (detalle de conexión en database.php)
// -------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'u977404351_buscandohuella');
// Si en phpMyAdmin usas otra BD, debe ser ESTA misma. Alternativa conocida: u977404351_huellasrupe

define('DB_USER', 'u977404351_root');
define('DB_PASS', '>lUi0FwV|Jv4');
define('DB_CHARSET', 'utf8mb4');

// -------------------------------------------------
// Roles (solo 2)
// -------------------------------------------------
define('ROLE_ADMIN', 'administrador');
define('ROLE_TUTOR', 'tutor');

// Estados de usuario
define('USER_ACTIVE', 1);
define('USER_INACTIVE', 0);

// Estatus de reportes
define('STATUS_BUSQUEDA', 'En Búsqueda');
define('STATUS_RESGUARDO', 'En Resguardo');
define('STATUS_LOCALIZADO', 'Localizado');

// -------------------------------------------------
// Sesión / seguridad
// -------------------------------------------------
define('SESSION_NAME', 'RUPESESSID');
define('CSRF_TOKEN_KEY', '_csrf');
define('PASSWORD_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_OPTIONS', ['cost' => 12]);

// -------------------------------------------------
// reCAPTCHA v3 (Google) — Formularios: login, registro,
// forgot, reset, contacto
// 1) https://www.google.com/recaptcha/admin/create
// 2) Tipo: reCAPTCHA v3
// 3) Dominios: buscandoahuellitasrupe.com.mx (+ www)
// 4) Pegar Site Key y Secret Key abajo
// -------------------------------------------------
define('RECAPTCHA_SITE_KEY', '6LdcRastAAAAAHWxHEOk8Hhhco4LuA5wwWbVx6iH');
define('RECAPTCHA_SECRET', '6LdcRastAAAAAOZbU3u1JPu9glYdUoS0No73xkGY');
define('RECAPTCHA_VERIFY_URL', 'https://www.google.com/recaptcha/api/siteverify');
define('RECAPTCHA_MIN_SCORE', 0.5); // 0.0-1.0 (0.5 recomendado)

// -------------------------------------------------
// Correo SMTP Hostinger (detalle en mail.php)
// -------------------------------------------------
define('MAIL_FROM', 'admin@buscandoahuellitasrupe.com.mx');
define('MAIL_FROM_NAME', 'RUPE CDMX');
define('MAIL_HOST', 'smtp.hostinger.com');
define('MAIL_PORT', 465);
define('MAIL_USER', 'admin@buscandoahuellitasrupe.com.mx');
define('MAIL_PASS', 'D8$*Si+d');
define('MAIL_ENCRYPTION', 'ssl'); // ssl (465) | tls (587)

// -------------------------------------------------
// QR / ficha pública
// -------------------------------------------------
define('QR_BASE_URL', APP_URL . '/mascota/');

// -------------------------------------------------
// Zona horaria
// -------------------------------------------------
define('APP_TIMEZONE', 'America/Mexico_City');
date_default_timezone_set(APP_TIMEZONE);