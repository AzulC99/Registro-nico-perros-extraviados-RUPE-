<?php
/**
 * RUPE CDMX — Manejo global de excepciones
 * app/core/ExceptionHandler.php
 */

declare(strict_types=1);

namespace App\Core;

use Throwable;
use App\Core\Exceptions\ValidationException;
use App\Core\Exceptions\AuthException;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ForbiddenException;

final class ExceptionHandler
{
    public static function handle(Throwable $e): void
    {
        $isApi = str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/api/');

        $code    = 500;
        $message = 'Error interno del servidor';
        $errors  = null;

        if ($e instanceof ValidationException) {
            $code    = 422;
            $message = $e->getMessage() ?: 'Datos inválidos';
            $errors  = $e->getErrors();
        } elseif ($e instanceof AuthException) {
            $code    = 401;
            $message = $e->getMessage() ?: 'No autenticado';
        } elseif ($e instanceof ForbiddenException) {
            $code    = 403;
            $message = $e->getMessage() ?: 'Acceso denegado';
        } elseif ($e instanceof NotFoundException) {
            $code    = 404;
            $message = $e->getMessage() ?: 'Recurso no encontrado';
        } elseif ($e instanceof \PDOException) {
            $code    = 500;
            $message = 'Error de base de datos';
            app_log('error', 'PDO: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        } else {
            app_log('error', $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        if ($isApi || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            $payload = [
                'success' => false,
                'message' => $message,
            ];
            if ($errors !== null) {
                $payload['errors'] = $errors;
            }
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $payload['debug'] = [
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                ];
            }
            json_response($payload, $code);
        }

        http_response_code($code);
        if ($code === 404) {
            require APP_PATH . '/views/home/404.php';
            return;
        }
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Error RUPE</title></head><body style="font-family:system-ui;padding:2rem;max-width:720px;margin:auto">';
        echo '<h1>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</h1>';
        echo '<p>La página no pudo cargarse. Si el problema continúa, revisa la conexión a la base de datos en <code>app/config/config.php</code>.</p>';
        echo '<pre style="background:#f5f5f5;padding:1rem;overflow:auto">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "\n" . htmlspecialchars($e->getFile() . ':' . $e->getLine(), ENT_QUOTES, 'UTF-8') . '</pre>';
        echo '<p><a href="/">Volver al inicio</a></p>';
        echo '</body></html>';
        exit;
    }
}
