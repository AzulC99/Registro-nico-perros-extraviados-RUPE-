<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\MascotaDAO;
use App\Models\AlcaldiaDAO;

final class HomeController
{
    public function index(Request $request): void
    {
        $public = [];
        $alcaldias = [];

        try {
            $dao = new MascotaDAO();
            $list = $dao->listAll([], 12, 0);
            if (!is_array($list)) {
                $list = [];
            }
            foreach ($list as $m) {
                if (!is_array($m)) {
                    continue;
                }
                try {
                    $public[] = function_exists('mascota_public_row')
                        ? mascota_public_row($m)
                        : (function_exists('sanitize_data') ? sanitize_data($m, true) : $m);
                } catch (\Throwable $e) {
                    $public[] = $m;
                }
            }
            $public = array_slice($public, 0, 12);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'Home mascotas: ' . $e->getMessage());
            }
            $public = [];
        }

        try {
            $alcaldias = (new AlcaldiaDAO())->listActive() ?: [];
            if (!is_array($alcaldias)) {
                $alcaldias = [];
            }
        } catch (\Throwable $e) {
            $alcaldias = [];
        }

        $csrf = '';
        try {
            $csrf = function_exists('csrf_token') ? (string) csrf_token() : '';
        } catch (\Throwable $e) {
            $csrf = '';
        }

        $this->safeView('home/index', [
            'mascotas' => $public,
            'alcaldias' => $alcaldias,
            'csrf' => $csrf,
            'recaptchaSiteKey' => defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '',
        ]);
    }

    public function conocer(Request $request): void
    {
        $this->safeView('home/conocer', [
            'csrf' => function_exists('csrf_token') ? csrf_token() : '',
        ]);
    }

    public function blog(Request $request): void
    {
        $this->safeView('home/blog', [
            'csrf' => function_exists('csrf_token') ? csrf_token() : '',
        ]);
    }

    public function avisoPrivacidad(Request $request): void
    {
        $this->safeView('home/aviso-privacidad', [
            'csrf' => function_exists('csrf_token') ? csrf_token() : '',
            'fecha' => date('d/m/Y'),
        ]);
    }

    public function terminosServicio(Request $request): void
    {
        $this->safeView('home/terminos-servicio', [
            'csrf' => function_exists('csrf_token') ? csrf_token() : '',
            'fecha' => date('d/m/Y'),
        ]);
    }

    public function fichaPublica(Request $request): void
    {
        $token = (string) ($request->param('token') ?? $request->query('token') ?? '');
        $folio = (string) ($request->query('folio') ?? '');
        $m = null;
        try {
            $dao = new MascotaDAO();
            if ($token !== '') {
                $m = method_exists($dao, 'findByQrToken') ? $dao->findByQrToken($token) : null;
                if (!$m && method_exists($dao, 'findByFolio')) {
                    $m = $dao->findByFolio($token);
                }
            }
            if (!$m && $folio !== '' && method_exists($dao, 'findByFolio')) {
                $m = $dao->findByFolio($folio);
            }
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'fichaPublica: ' . $e->getMessage());
            }
        }

        if (!$m) {
            http_response_code(404);
            $this->safeView('home/404', ['mensaje' => 'Ficha no encontrada']);
            return;
        }

        $row = function_exists('mascota_public_row') ? mascota_public_row($m) : $m;
        $token = (string) ($m['qr_token'] ?? $token ?? '');
        $folio = (string) ($m['folio'] ?? $folio ?? '');
        $base = defined('APP_URL') ? rtrim((string) APP_URL, '/') : '';
        if ($base === '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'buscandoahuellitasrupe.com.mx');
        }
        $path = $token !== '' ? ('/mascota/' . rawurlencode($token)) : ('/mascota/' . rawurlencode($folio));
        $qrUrl = $base . $path . '?avistar=1';
        $this->safeView('home/ficha-publica', [
            'mascota' => $row,
            'qr_url' => $qrUrl,
            'folio' => $folio,
            'csrf' => function_exists('csrf_token') ? csrf_token() : '',
        ]);
    }

    /** Render de vista sin tumbar la app si falla */
    private function safeView(string $view, array $data = []): void
    {
        try {
            Response::view($view, $data);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'View ' . $view . ': ' . $e->getMessage());
            }
            if (!headers_sent()) {
                http_response_code(200);
                header('Content-Type: text/html; charset=utf-8');
            }
            $title = htmlspecialchars($view, ENT_QUOTES, 'UTF-8');
            echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>RUPE CDMX</title>';
            echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>';
            echo '<body class="p-4"><h1>RUPE CDMX</h1>';
            echo '<p>No se pudo cargar la página solicitada.</p>';
            echo '<p><a class="btn btn-primary" href="/">Volver al inicio</a></p></body></html>';
        }
    }
}
