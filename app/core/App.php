<?php
/**
 * RUPE CDMX — Bootstrap aplicación
 * app/core/App.php
 */

declare(strict_types=1);

namespace App\Core;

final class App
{
    public function run(Router $router, Request $request): void
    {
        // CORS mínimo para API (mismo origen preferido)
        if ($request->isApi()) {
            header('Access-Control-Allow-Origin: ' . APP_URL);
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, X-Requested-With');
            header('Access-Control-Allow-Credentials: true');

            if ($request->isMethod('OPTIONS')) {
                http_response_code(204);
                exit;
            }
        }

        // Headers de seguridad
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header(
            "Content-Security-Policy: "
            . "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://www.google.com https://www.gstatic.com https://www.recaptcha.net; "
            . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://www.gstatic.com; "
            . "img-src 'self' data: https: blob:; "
            . "font-src 'self' data: https://cdn.jsdelivr.net; "
            . "connect-src 'self' https://nominatim.openstreetmap.org https://www.google.com https://www.gstatic.com https://www.recaptcha.net; "
            . "frame-src https://www.google.com https://www.recaptcha.net; "
            . "worker-src 'self' blob:;"
        );

        $router->dispatch($request);
    }
}
