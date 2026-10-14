<?php
/**
 * RUPE CDMX — Configuración PHPMailer (Hostinger, sin Composer)
 * app/config/mail.php
 */

declare(strict_types=1);

if (!defined('MAIL_HOST')) {
    require_once __DIR__ . '/config.php';
}

/**
 * Ruta manual a PHPMailer (sin Composer)
 * Preferir app/lib/PHPMailer; fallback vendor/
 */
define('PHPMAILER_PATH', APP_PATH . '/lib/PHPMailer');

/**
 * Plantillas de correo (asunto / cuerpo base)
 */
define('MAIL_SUBJECT_RESET', 'Restablecer contraseña — RUPE CDMX');
define('MAIL_SUBJECT_WELCOME', 'Bienvenido a RUPE CDMX');
define('MAIL_SUBJECT_ADMIN_CREDENTIALS', 'Tu acceso de administrador — RUPE CDMX');
define('MAIL_SUBJECT_STATUS', 'Actualización de estatus de mascota — RUPE CDMX');
define('MAIL_SUBJECT_SIGHTING', 'Nuevo avistamiento reportado — RUPE CDMX');
