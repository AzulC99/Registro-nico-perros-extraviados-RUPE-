<?php
/**
 * RUPE CDMX — Response helpers
 * app/core/Response.php
 */

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(array $data, int $code = 200): never
    {
        json_response($data, $code);
    }

    public static function ok(mixed $data = null, string $message = 'OK', int $code = 200): never
    {
        json_ok($data, $message, $code);
    }

    public static function error(string $message, int $code = 400, mixed $errors = null): never
    {
        json_error($message, $code, $errors);
    }

    public static function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $path = APP_PATH . '/views/' . ltrim(str_replace('.', '/', $view), '/') . '.php';
        if (!is_file($path)) {
            http_response_code(404);
            $path = APP_PATH . '/views/home/404.php';
        }
        require $path;
    }

    public static function redirect(string $path): never
    {
        redirect($path);
    }
}
