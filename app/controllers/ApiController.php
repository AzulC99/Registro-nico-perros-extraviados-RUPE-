<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\MascotaDAO;
use App\Models\AlcaldiaDAO;
use App\Helpers\Validator;
use App\Helpers\Recaptcha;
use App\Helpers\Mailer;
use App\Core\Exceptions\NotFoundException;

final class ApiController
{
    /**
     * GET /api/public/qr?data=...&size=200
     * Devuelve imagen PNG del QR (misma origen → usable en canvas/PDF sin CORS).
     * Usa API externa con caché en memoria de petición; fallback blanco con texto.
     */
    public function qr(Request $request): void
    {
        $data = trim((string) ($request->query('data') ?? $request->query('text') ?? ''));
        $size = (int) ($request->query('size') ?? 200);
        if ($size < 80) {
            $size = 80;
        }
        if ($size > 400) {
            $size = 400;
        }
        if ($data === '') {
            $data = 'RUPE';
        }
        if (strlen($data) > 800) {
            $data = substr($data, 0, 800);
        }

        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size
            . '&margin=8&ecc=M&data=' . rawurlencode($data);

        $png = null;
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'RUPE-CDMX/1.0',
            ]);
            $png = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code < 200 || $code >= 300 || $png === false || strlen((string) $png) < 50) {
                $png = null;
            }
        }
        if ($png === null && ini_get('allow_url_fopen')) {
            $ctx = stream_context_create(['http' => ['timeout' => 8, 'header' => "User-Agent: RUPE-CDMX/1.0\r\n"]]);
            $png = @file_get_contents($url, false, $ctx);
            if ($png === false || strlen((string) $png) < 50) {
                $png = null;
            }
        }

        // Limpiar buffers y enviar imagen
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if ($png !== null) {
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=3600');
            header('Content-Length: ' . strlen($png));
            echo $png;
            exit;
        }

        // Fallback GD: cuadro con folio
        if (function_exists('imagecreate')) {
            $im = imagecreate($size, $size);
            $bg = imagecolorallocate($im, 255, 255, 255);
            $fg = imagecolorallocate($im, 107, 29, 47);
            imagerectangle($im, 4, 4, $size - 5, $size - 5, $fg);
            $label = substr($data, 0, 24);
            imagestring($im, 3, 12, (int) ($size / 2 - 8), 'QR RUPE', $fg);
            header('Content-Type: image/png');
            imagepng($im);
            imagedestroy($im);
            exit;
        }

        header('HTTP/1.1 503 Service Unavailable');
        header('Content-Type: text/plain; charset=utf-8');
        echo 'QR no disponible';
        exit;
    }

    public function health(Request $request): void
    {
        Response::ok([
            'app'     => APP_NAME,
            'version' => APP_VERSION,
            'time'    => date('c'),
            'php'     => PHP_VERSION,
        ]);
    }

    public function mascotaPublica(Request $request): void
    {
        $folio = (string) $request->param('folio');
        $m = (new MascotaDAO())->findByFolio($folio);
        if (!$m) {
            throw new NotFoundException('Folio no encontrado');
        }
        $foto = (string) ($m['foto_url'] ?? '');
        if ($foto !== '' && !str_starts_with($foto, '/assets/uploads/') && !str_starts_with($foto, 'assets/uploads/')) {
            $foto = '';
        }
        Response::ok([
            'folio'          => $m['folio'] ?? '',
            'nombre'         => $m['nombre'] ?? '',
            'raza'           => (string) ($m['raza_nombre'] ?? ''),
            'especie'        => $m['especie'] ?? 'perro',
            'sexo'           => $m['sexo'] ?? null,
            'color'          => $m['color'] ?? null,
            'estatus'        => $m['estatus'] ?? null,
            'alcaldia'       => (string) ($m['alcaldia_nombre'] ?? ''),
            'fecha_extravio' => (string) ($m['fecha_extravio'] ?? ''),
            'foto_url'       => $foto !== '' ? $foto : (function_exists('mascota_foto_src') ? mascota_foto_src(null, (string)($m['especie'] ?? 'perro')) : ''),
            'qr_token'       => $m['qr_token'] ?? null,
        ]);
    }

    public function csrf(Request $request): void
    {
        Response::ok(['csrf' => csrf_token()]);
    }

    /** GET /api/public/alcaldias */
    public function alcaldiasPublicas(Request $request): void
    {
        Response::ok((new AlcaldiaDAO())->listActive());
    }

    /**
     * GET /api/public/mascotas
     * Filtros: q, id_alcaldia, estatus, limit
     */
    public function listMascotasPublicas(Request $request): void
    {
        if (!rate_limit_check('public_mascotas', 40, 60)) {
            Response::error('Demasiadas consultas. Espera un momento.', 429);
        }

        $filters = array_filter([
            'estatus'     => $request->query('estatus') ?: '',
            'id_alcaldia' => $request->query('id_alcaldia') ? (int) $request->query('id_alcaldia') : 0,
            'q'           => $request->query('q') ? trim((string) $request->query('q')) : '',
        ], static fn($v) => $v !== null && $v !== '' && $v !== 0);

        // Por defecto solo "En Búsqueda" en portada pública
        if (empty($filters['estatus']) && empty($filters['q'])) {
            $filters['estatus'] = STATUS_BUSQUEDA;
        }

        $limit = min(48, max(1, (int) ($request->query('limit') ?? 12)));
        $list  = (new MascotaDAO())->listAll($filters, $limit, 0);

        $public = array_map(static function (array $m): array {
            $foto = (string) ($m['foto_url'] ?? '');
            if ($foto !== '' && !str_starts_with($foto, '/assets/uploads/') && !str_starts_with($foto, 'assets/uploads/')) {
                $foto = '';
            } elseif ($foto !== '' && !str_starts_with($foto, '/')) {
                $foto = '/' . $foto;
            }
            return [
                'folio'          => (string) ($m['folio'] ?? ''),
                'nombre'         => (string) ($m['nombre'] ?? ''),
                'raza'           => (string) ($m['raza_nombre'] ?? ''),
                'especie'        => $m['especie'] ?? 'perro',
                'estatus'        => (string) ($m['estatus'] ?? ''),
                'alcaldia'       => (string) ($m['alcaldia_nombre'] ?? ''),
                'id_alcaldia'    => (int) ($m['id_alcaldia'] ?? 0),
                'fecha_extravio' => (string) ($m['fecha_extravio'] ?? ''),
                'foto_url'       => $foto !== '' ? $foto : (function_exists('mascota_foto_src') ? mascota_foto_src(null, (string)($m['especie'] ?? 'perro')) : ''),
                'colonia'        => (string) ($m['colonia'] ?? ''),
                'qr_token'       => (string) ($m['qr_token'] ?? ''),
            ];
        }, $list);

        Response::ok(['mascotas' => $public, 'total' => count($public)]);
    }

    /**
     * POST /api/public/contacto
     * Envío de mensaje de la home por correo (PHPMailer)
     */
    public function contacto(Request $request): void
    {
        if (!rate_limit_check('contacto', 8, 300)) {
            Response::error('Has enviado demasiados mensajes. Intenta más tarde.', 429);
        }

        $all = $request->all();
        $v = Validator::make($all)
            ->required('nombre', 'correo', 'asunto', 'mensaje')
            ->email('correo')
            ->min('nombre', 2)->max('nombre', 120)
            ->min('asunto', 3)->max('asunto', 150)
            ->min('mensaje', 10)->max('mensaje', 2000);
        $data = $v->validate();

        // reCAPTCHA: validar si hay token; si falla, registrar y continuar (rate limit protege)
        $token = $request->input('recaptcha_token');
        if (is_string($token) && $token !== '') {
            $rc = \App\Helpers\Recaptcha::verify($token, 'contacto');
            if (empty($rc['success']) && function_exists('app_log')) {
                app_log('warning', 'contacto reCAPTCHA', ['error' => $rc['error'] ?? '', 'score' => $rc['score'] ?? 0]);
            }
            // Solo bloquear si score muy bajo y token presente
            if (empty($rc['success']) && isset($rc['score']) && (float) $rc['score'] > 0 && (float) $rc['score'] < 0.3) {
                Response::error('Verificación de seguridad fallida. Intenta de nuevo.', 422);
            }
        }

        $nombre   = trim((string) $data['nombre']);
        $correo   = trim((string) $data['correo']);
        $asunto   = trim((string) $data['asunto']);
        $mensaje  = trim((string) $data['mensaje']);
        $telefono = function_exists('normalize_phone')
            ? normalize_phone((string) ($request->input('telefono') ?? ''))
            : trim((string) ($request->input('telefono') ?? ''));

        $destinatario = defined('MAIL_FROM') ? MAIL_FROM : 'admin@buscandoahuellitasrupe.com.mx';

        $html = '
          <p><strong>Nuevo mensaje desde Contacto y Soporte (portada RUPE CDMX)</strong></p>
          <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <tr><td style="padding:6px 0;color:#666;width:120px;">Nombre</td><td style="padding:6px 0;"><strong>' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</strong></td></tr>
            <tr><td style="padding:6px 0;color:#666;">Correo</td><td style="padding:6px 0;"><a href="mailto:' . htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') . '</a></td></tr>
            <tr><td style="padding:6px 0;color:#666;">Teléfono</td><td style="padding:6px 0;">' . htmlspecialchars($telefono !== '' ? $telefono : '—', ENT_QUOTES, 'UTF-8') . '</td></tr>
            <tr><td style="padding:6px 0;color:#666;">Asunto</td><td style="padding:6px 0;">' . htmlspecialchars($asunto, ENT_QUOTES, 'UTF-8') . '</td></tr>
          </table>
          <hr style="border:none;border-top:1px solid #eee;margin:16px 0;">
          <p style="white-space:pre-wrap;">' . nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8')) . '</p>
          <p style="color:#999;font-size:12px;margin-top:24px;">IP: ' . htmlspecialchars(function_exists('client_ip') ? client_ip() : '', ENT_QUOTES, 'UTF-8') . ' · ' . date('Y-m-d H:i:s') . '</p>
        ';

        $ok = false;
        try {
            $ok = \App\Helpers\Mailer::send(
                $destinatario,
                'Contacto web: ' . $asunto,
                $html,
                'RUPE Soporte',
                $correo,
                $nombre
            );
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'contacto mail exception', ['msg' => $e->getMessage()]);
            }
            $ok = false;
        }

        if ($ok && function_exists('is_valid_email') && is_valid_email($correo)) {
            try {
                \App\Helpers\Mailer::send(
                    $correo,
                    'Recibimos tu mensaje — RUPE CDMX',
                    '<p>Hola <strong>' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</strong>,</p>
                     <p>Recibimos tu mensaje sobre <em>' . htmlspecialchars($asunto, ENT_QUOTES, 'UTF-8') . '</em>.</p>
                     <p>Un administrador de RUPE CDMX te responderá pronto a este correo.</p>
                     <p style="color:#666;font-size:13px;">No respondas a este mensaje automático si no es necesario.</p>',
                    $nombre
                );
            } catch (\Throwable $e) {
                // no bloquear
            }
        }

        if (!$ok) {
            if (function_exists('app_log')) {
                app_log('error', 'Contacto mail failed', ['from' => $correo, 'asunto' => $asunto]);
            }
            Response::error(
                'No se pudo enviar el mensaje por correo. Verifica la configuración SMTP de Hostinger o intenta más tarde.',
                500
            );
        }

        try {
            if (class_exists(\App\Models\AuditoriaDAO::class)) {
                (new \App\Models\AuditoriaDAO())->log(null, 'contacto_web', 'contacto', null, [
                    'correo' => $correo,
                    'asunto' => $asunto,
                    'nombre' => $nombre,
                ]);
            }
        } catch (\Throwable $e) {
        }

        if (function_exists('app_log')) {
            app_log('info', 'Contacto enviado', ['from' => $correo, 'asunto' => $asunto]);
        }
        Response::ok(['enviado' => true], 'Mensaje enviado correctamente. Gracias por contactarnos.');
    }

    /**
     * POST /api/public/avistamiento
     * Captura ciudadana desde QR / ficha pública (sin sesión de tutor).
     */
    public function reportarAvistamiento(Request $request): void
    {
        if (!rate_limit_check('public_avist', 12, 60)) {
            Response::error('Demasiados reportes. Espera un momento.', 429);
        }

        $token = trim((string) ($request->input('qr_token') ?? $request->input('token') ?? ''));
        $folio = trim((string) ($request->input('folio') ?? ''));
        $condicion = trim((string) ($request->input('condicion') ?? 'Observado'));
        $referencia = trim((string) ($request->input('referencia') ?? ''));
        $nombre = trim((string) ($request->input('nombre') ?? ''));
        $apellidos = trim((string) ($request->input('apellidos') ?? ''));
        $contacto = trim((string) ($request->input('contacto') ?? ''));
        if ($contacto === '' && ($nombre !== '' || $apellidos !== '')) {
            $contacto = trim($nombre . ' ' . $apellidos);
        }
        $telefono = preg_replace('/\D+/', '', (string) ($request->input('telefono') ?? '')) ?: '';
        $correo = trim((string) ($request->input('correo') ?? ''));
        $lat = $request->input('latitud');
        $lng = $request->input('longitud');

        if ($token === '' && $folio === '') {
            Response::error('Falta el identificador del caso (token o folio).', 422);
        }
        if ($contacto === '' || mb_strlen($contacto) < 2) {
            Response::error('Indica tu nombre (quien reporta).', 422);
        }
        if ($referencia === '') {
            Response::error('Indica una referencia del lugar.', 422);
        }
        if (mb_strlen($referencia) > 60) {
            $referencia = mb_substr($referencia, 0, 60);
        }
        if (mb_strlen($contacto) > 20) {
            $contacto = mb_substr($contacto, 0, 20);
        }
        if ($telefono !== '' && strlen($telefono) !== 10) {
            Response::error('Teléfono debe tener 10 dígitos.', 422);
        }
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            Response::error('Correo electrónico no válido.', 422);
        }
        if ($correo !== '' && mb_strlen($correo) > 30) {
            $correo = mb_substr($correo, 0, 30);
        }

        $condsOk = ['Observado', 'Resguardado', 'En albergue', 'Herido', 'Localizado'];
        if (!in_array($condicion, $condsOk, true)) {
            $condicion = 'Observado';
        }

        try {
            $dao = new \App\Models\MascotaDAO();
            $m = null;
            if ($token !== '' && method_exists($dao, 'findByQrToken')) {
                $m = $dao->findByQrToken($token);
            }
            if (!$m && $folio !== '' && method_exists($dao, 'findByFolio')) {
                $m = $dao->findByFolio($folio);
            }
            if (!$m && $token !== '' && method_exists($dao, 'findByFolio')) {
                $m = $dao->findByFolio($token);
            }
            if (!$m) {
                Response::error('No se encontró la mascota de este código QR.', 404);
            }

            $idMascota = (int) ($m['id_mascota'] ?? $m['id'] ?? 0);
            $folioM = (string) ($m['folio'] ?? $folio);
            if ($idMascota <= 0) {
                Response::error('Caso inválido.', 404);
            }

            $fotoUrl = '';
            // Foto opcional base64 o upload simplificado
            $fotoB64 = (string) ($request->input('foto_base64') ?? '');
            if ($fotoB64 !== '' && str_starts_with($fotoB64, 'data:image/')) {
                if (preg_match('#^data:image/(jpeg|jpg|png|webp);base64,(.+)$#i', $fotoB64, $mm)) {
                    $bin = base64_decode($mm[2], true);
                    if ($bin !== false && strlen($bin) > 100 && strlen($bin) < 3_500_000) {
                        $ext = strtolower($mm[1]) === 'jpg' ? 'jpg' : strtolower($mm[1]);
                        $name = 'avist_pub_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $dir = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2)) . '/public/assets/uploads';
                        if (!is_dir($dir)) {
                            @mkdir($dir, 0755, true);
                        }
                        $path = $dir . '/' . $name;
                        if (@file_put_contents($path, $bin) !== false) {
                            $fotoUrl = '/assets/uploads/' . $name;
                        }
                    }
                }
            }

            $seg = new \App\Models\SeguimientoDAO();
            $idSeg = $seg->create([
                'id_mascota' => $idMascota,
                'id_usuario' => 0,
                'folio' => $folioM,
                'condicion' => $condicion,
                'referencia' => $referencia,
                'contacto' => $contacto,
                'telefono' => $telefono,
                'correo' => $correo,
                'latitud' => $lat,
                'longitud' => $lng,
                'foto_url' => $fotoUrl,
            ]);

            // Actualizar GPS de mascota si viene
            if ($lat !== null && $lat !== '' && $lng !== null && $lng !== '' && is_numeric($lat) && is_numeric($lng)) {
                try {
                    if (method_exists($dao, 'updateGps')) {
                        $dao->updateGps($idMascota, (float) $lat, (float) $lng);
                    } else {
                        $dao->update($idMascota, [
                            'latitud' => round((float) $lat, 7),
                            'longitud' => round((float) $lng, 7),
                        ]);
                    }
                } catch (\Throwable $e2) {
                }
            }

            Response::ok([
                'id_seguimiento' => (int) $idSeg,
                'folio' => $folioM,
            ], 'Avistamiento registrado. Gracias por ayudar.', 201);
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'public avistamiento', ['msg' => $e->getMessage()]);
            }
            Response::error('No se pudo guardar el avistamiento. Intenta de nuevo.', 500);
        }
    }

}
