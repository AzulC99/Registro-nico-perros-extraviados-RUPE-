<?php
/**
 * RUPE CDMX — reCAPTCHA v3 (Google)
 * app/helpers/Recaptcha.php
 *
 * Formularios protegidos:
 *  - login, register, forgot, reset, contacto
 *
 * Configura en app/config/config.php:
 *  RECAPTCHA_SITE_KEY  (clave del sitio)
 *  RECAPTCHA_SECRET    (clave secreta)
 *  RECAPTCHA_MIN_SCORE (0.0–1.0, recomendado 0.5)
 */

declare(strict_types=1);

namespace App\Helpers;

final class Recaptcha
{
    public static function isConfigured(): bool
    {
        $site = defined('RECAPTCHA_SITE_KEY') ? trim((string) RECAPTCHA_SITE_KEY) : '';
        $secret = defined('RECAPTCHA_SECRET') ? trim((string) RECAPTCHA_SECRET) : '';
        return $site !== '' && $secret !== ''
            && !str_contains($site, 'YOUR_')
            && !str_contains($secret, 'YOUR_');
    }

    /**
     * @return array{success: bool, score: float, action: string, error?: string}
     */
    public static function verify(?string $token, string $expectedAction = ''): array
    {
        if (!self::isConfigured()) {
            if (function_exists('app_log')) {
                app_log('warning', 'reCAPTCHA no configurado (faltan SITE_KEY/SECRET)');
            }
            return ['success' => true, 'score' => 1.0, 'action' => $expectedAction, 'error' => 'skip'];
        }

        if ($token === null || $token === '') {
            return ['success' => false, 'score' => 0.0, 'action' => '', 'error' => 'Token vacío'];
        }

        if (defined('APP_DEBUG') && APP_DEBUG && ($token === 'dev-bypass' || $token === 'skip')) {
            return ['success' => true, 'score' => 1.0, 'action' => $expectedAction];
        }

        $post = http_build_query([
            'secret'   => RECAPTCHA_SECRET,
            'response' => $token,
            'remoteip' => function_exists('client_ip') ? client_ip() : ($_SERVER['REMOTE_ADDR'] ?? ''),
        ]);

        $url = defined('RECAPTCHA_VERIFY_URL')
            ? RECAPTCHA_VERIFY_URL
            : 'https://www.google.com/recaptcha/api/siteverify';
        $raw = self::httpPost($url, $post);

        if ($raw === null || $raw === '') {
            if (function_exists('app_log')) {
                app_log('warning', 'reCAPTCHA: no se pudo contactar a Google');
            }
            return ['success' => true, 'score' => 0.0, 'action' => $expectedAction, 'error' => 'servicio_no_disponible'];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return ['success' => false, 'score' => 0.0, 'action' => '', 'error' => 'Respuesta inválida'];
        }

        $success = !empty($data['success']);
        $score   = (float) ($data['score'] ?? 0);
        $action  = (string) ($data['action'] ?? '');

        if (!$success) {
            $codes = isset($data['error-codes']) && is_array($data['error-codes'])
                ? implode(',', $data['error-codes'])
                : 'fail';
            if (function_exists('app_log')) {
                app_log('warning', 'reCAPTCHA falló', ['codes' => $codes]);
            }
            return ['success' => false, 'score' => $score, 'action' => $action, 'error' => 'Verificación fallida'];
        }

        $min = defined('RECAPTCHA_MIN_SCORE') ? (float) RECAPTCHA_MIN_SCORE : 0.5;
        if ($score < $min) {
            return ['success' => false, 'score' => $score, 'action' => $action, 'error' => 'Puntuación baja'];
        }

        if ($expectedAction !== '' && $action !== '' && strcasecmp($action, $expectedAction) !== 0) {
            if (function_exists('app_log')) {
                app_log('info', 'reCAPTCHA action mismatch', [
                    'expected' => $expectedAction,
                    'got' => $action,
                ]);
            }
        }

        return ['success' => true, 'score' => $score, 'action' => $action];
    }

    public static function assertValid(?string $token, string $action = ''): void
    {
        $result = self::verify($token, $action);
        if (!$result['success']) {
            throw new \App\Core\Exceptions\ValidationException(
                'Verificación de seguridad fallida (reCAPTCHA). Recarga la página e intenta de nuevo.',
                ['recaptcha' => [$result['error'] ?? 'Falló reCAPTCHA']]
            );
        }
    }

    private static function httpPost(string $url, string $body): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            ]);
            $raw = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($raw !== false && $raw !== '') {
                return $raw;
            }
            if ($err && function_exists('app_log')) {
                app_log('warning', 'reCAPTCHA cURL', ['err' => $err]);
            }
        }

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $body,
                'timeout' => 10,
            ],
        ]);
        $raw = @file_get_contents($url, false, $ctx);
        return $raw === false ? null : $raw;
    }
}
