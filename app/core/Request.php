<?php
/**
 * RUPE CDMX — Request HTTP
 * app/core/Request.php
 * PHP 8.3.33 · Hostinger
 */

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private string $method;
    private string $uri;
    /** @var array<string, mixed> */
    private array $queryParams;
    /** @var array<string, mixed> */
    private array $bodyParams;
    /** @var array<string, mixed> */
    private array $uploadedFiles;
    /** @var array<string, string> */
    private array $headers;
    /** @var array<string, mixed> */
    private array $routeParams = [];

    public function __construct()
    {
        $this->method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $uri = is_string($uri) ? $uri : '/';
        $this->uri = rtrim($uri, '/') ?: '/';
        $this->queryParams = is_array($_GET) ? $_GET : [];
        $this->uploadedFiles = is_array($_FILES) ? $_FILES : [];
        $this->headers = $this->parseHeaders();
        $this->bodyParams = $this->parseBody();
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = (string) $value;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = (string) $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = (string) $_SERVER['CONTENT_LENGTH'];
        }
        return $headers;
    }

    private function parseBody(): array
    {
        $contentType = $this->header('Content-Type') ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $raw = is_string($raw) ? $raw : '';
            if ($raw === '') {
                return [];
            }
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }
        // multipart/form-data y application/x-www-form-urlencoded
        return is_array($_POST) ? $_POST : [];
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function isAjax(): bool
    {
        $xrw = strtolower($this->header('X-Requested-With') ?? '');
        $ct = $this->header('Content-Type') ?? '';
        return $xrw === 'xmlhttprequest' || str_contains($ct, 'application/json');
    }

    public function isApi(): bool
    {
        return str_starts_with($this->uri, '/api/');
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->queryParams;
        }
        return $this->queryParams[$key] ?? $default;
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->bodyParams;
        }
        return $this->bodyParams[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->queryParams, $this->bodyParams);
    }

    public function file(string $key): ?array
    {
        $f = $this->uploadedFiles[$key] ?? null;
        return is_array($f) ? $f : null;
    }

    public function header(string $name): ?string
    {
        // Busca case-insensitive
        foreach ($this->headers as $k => $v) {
            if (strcasecmp($k, $name) === 0) {
                return $v;
            }
        }
        return null;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->header('Authorization') ?? '';
        if (preg_match('/^Bearer\s+(\S+)$/i', $auth, $m)) {
            return $m[1];
        }
        return null;
    }

    public function setParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function csrf(): ?string
    {
        $token = $this->input('_csrf')
            ?? $this->header('X-CSRF-Token')
            ?? $this->header('X-Csrf-Token')
            ?? $this->query('_csrf');
        return $token !== null && $token !== '' ? (string) $token : null;
    }

    public function ip(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = (string) $_SERVER[$k];
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }
        return '0.0.0.0';
    }
}
