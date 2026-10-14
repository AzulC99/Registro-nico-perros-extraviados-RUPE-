<?php
/**
 * RUPE CDMX — Front Controller
 * PHP 8.3.33 · Hostinger · sin Composer
 * public/index.php
 */

declare(strict_types=1);

// Mostrar errores en HTML si falla el arranque (Hostinger 500 vacío)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('display_startup_errors', '0');

date_default_timezone_set('America/Mexico_City');

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', __DIR__);

/**
 * Respuesta de emergencia — nunca dejar body vacío
 */
function rupe_fail(Throwable $e, string $where = 'bootstrap'): void
{
    if (!headers_sent()) {
        http_response_code(200);
        header('Content-Type: text/html; charset=utf-8');
    }
    $msg  = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    $file = htmlspecialchars($e->getFile() . ':' . $e->getLine(), ENT_QUOTES, 'UTF-8');
    $where = htmlspecialchars($where, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>RUPE CDMX — Error</title></head>';
    echo '<body style="font-family:system-ui,sans-serif;padding:2rem;max-width:720px;margin:auto;background:#fafafa">';
    echo '<h1 style="color:#6B1D2F">RUPE CDMX</h1>';
    echo '<p>Error en <strong>' . $where . '</strong>.</p>';
    echo '<pre style="background:#fff;border:1px solid #ddd;padding:1rem;border-radius:8px;overflow:auto;font-size:13px">';
    echo $msg . "\n" . $file;
    echo '</pre>';
    echo '<p style="color:#666;font-size:14px">Revisa <code>app/config/config.php</code> (DB_NAME, DB_USER, DB_PASS) y que el document root sea la carpeta <code>public/</code>.</p>';
    echo '<p><a href="/">Recargar</a> · <a href="/ping.php">Diagnóstico PHP</a></p>';
    echo '</body></html>';
    exit;
}

// Autoload
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $parts    = explode('\\', $relative);
    $file     = array_pop($parts);
    $dir      = strtolower(implode('/', $parts));
    $path     = APP_PATH . ($dir !== '' ? '/' . $dir : '') . '/' . $file . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

// Cargar config y helpers (protegido)
try {
    if (!is_dir(APP_PATH)) {
        throw new RuntimeException('No existe la carpeta app/ junto a public/. ROOT=' . ROOT_PATH);
    }
    require_once APP_PATH . '/config/config.php';
    require_once APP_PATH . '/config/database.php';
    require_once APP_PATH . '/config/mail.php';
    require_once APP_PATH . '/config/apis.php';
    require_once APP_PATH . '/helpers/functions.php';
} catch (Throwable $e) {
    rupe_fail($e, 'carga de config/helpers');
}

// Storage
try {
    $storageDirs = [
        defined('STORAGE_PATH') ? STORAGE_PATH : (ROOT_PATH . '/storage'),
        defined('LOG_PATH') ? LOG_PATH : (ROOT_PATH . '/storage/logs'),
        ROOT_PATH . '/storage/rate',
        defined('UPLOAD_PATH') ? UPLOAD_PATH : (PUBLIC_PATH . '/assets/uploads'),
    ];
    foreach ($storageDirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
    $phpLog = (defined('LOG_PATH') ? LOG_PATH : ROOT_PATH . '/storage/logs') . '/php-' . date('Y-m-d') . '.log';
    @ini_set('error_log', $phpLog);
} catch (Throwable $e) {
    // no crítico
}

// Handlers
set_exception_handler(static function (Throwable $e): void {
    if (class_exists(\App\Core\ExceptionHandler::class)) {
        try {
            \App\Core\ExceptionHandler::handle($e);
            return;
        } catch (Throwable $e2) {
            rupe_fail($e2, 'ExceptionHandler');
        }
    }
    rupe_fail($e, 'excepción no manejada');
});

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    if (in_array($severity, [E_WARNING, E_NOTICE, E_USER_WARNING, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED], true)) {
        if (function_exists('app_log')) {
            app_log('warning', $message, ['file' => $file, 'line' => $line]);
        }
        return true;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(static function (): void {
    $err = error_get_last();
    if ($err === null) {
        return;
    }
    $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (!in_array($err['type'], $fatal, true)) {
        return;
    }
    if (!headers_sent()) {
        http_response_code(200);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>RUPE CDMX</title></head>';
    echo '<body style="font-family:system-ui;padding:2rem"><h1>Error fatal PHP</h1>';
    echo '<pre>' . htmlspecialchars($err['message'] . "\n" . $err['file'] . ':' . $err['line'], ENT_QUOTES, 'UTF-8') . '</pre>';
    echo '<p><a href="/">Recargar</a> · <a href="/ping.php">Diagnóstico</a></p></body></html>';
});

// Sesión (no tumbar si falla)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

try {
    if (session_status() === PHP_SESSION_NONE) {
        if (defined('SESSION_NAME')) {
            session_name(SESSION_NAME);
        }
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        @session_start();
    }
} catch (Throwable $e) {
    // continuar sin sesión
}

// App
use App\Core\App;
use App\Core\Request;
use App\Core\Router;

try {
    $app     = new App();
    $request = new Request();
    $router  = new Router();
    require_once APP_PATH . '/config/routes.php';
    $app->run($router, $request);
} catch (Throwable $e) {
    rupe_fail($e, 'despacho de rutas');
}
