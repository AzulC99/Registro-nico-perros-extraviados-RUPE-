<?php
/**
 * RUPE CDMX — Router REST + Web
 * app/core/Router.php
 */

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable|array>> */
    private array $routes = [];

    public function get(string $path, callable|array $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): self
    {
        return $this->add('PUT', $path, $handler);
    }

    public function patch(string $path, callable|array $handler): self
    {
        return $this->add('PATCH', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): self
    {
        return $this->add('DELETE', $path, $handler);
    }

    private function add(string $method, string $path, callable|array $handler): self
    {
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        $this->routes[$method][$path] = $handler;
        return $this;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        // Soporte method override
        if ($method === 'POST') {
            $override = $request->input('_method') ?? $request->header('X-HTTP-Method-Override');
            if ($override && in_array(strtoupper($override), ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = strtoupper($override);
            }
        }

        $uri = $request->uri();
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $path => $handler) {
            $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $path);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request->setParams($params);
                $this->invoke($handler, $request);
                return;
            }
        }

        // 404
        if ($request->isApi()) {
            Response::error('Endpoint no encontrado', 404);
        }
        http_response_code(404);
        require APP_PATH . '/views/home/404.php';
    }

    private function invoke(callable|array $handler, Request $request): void
    {
        try {
            if (is_array($handler)) {
                [$class, $method] = $handler;
                $controller = new $class();
                $controller->$method($request);
                return;
            }
            $handler($request);
        } catch (\Throwable $e) {
            $msg = $e->getMessage() ?: 'Error interno';
            $code = 500;
            $cls = get_class($e);
            if (str_contains($cls, 'Forbidden') || str_contains($msg, 'administradores') || str_contains($msg, 'Acceso denegado')) {
                $code = 403;
            } elseif (str_contains($cls, 'NotFound')) {
                $code = 404;
            } elseif (str_contains($cls, 'Validation') || str_contains($cls, 'Auth')) {
                $code = 422;
            }
            if (function_exists('app_log')) {
                app_log('error', 'Router: ' . $msg, ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
            if ($request->isApi()) {
                Response::error($msg, $code);
            }
            http_response_code($code >= 400 && $code < 600 ? $code : 500);
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
            echo '<title>RUPE CDMX</title></head><body style="font-family:system-ui;padding:2rem;max-width:640px;margin:auto">';
            echo '<h1>RUPE CDMX</h1>';
            echo '<p>Ocurrió un problema al cargar esta página.</p>';
            if (defined('APP_DEBUG') && APP_DEBUG) {
                echo '<pre style="background:#f5f5f5;padding:1rem;border-radius:8px;overflow:auto">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</pre>';
            }
            echo '<p><a href="/">Volver al inicio</a></p></body></html>';
            exit;
        }
    }
}
