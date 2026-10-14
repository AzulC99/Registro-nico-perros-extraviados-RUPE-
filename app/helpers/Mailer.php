<?php
/**
 * RUPE CDMX — PHPMailer wrapper (sin Composer, Hostinger SMTP)
 * app/helpers/Mailer.php
 */

declare(strict_types=1);

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

final class Mailer
{
    private static function loadPhpMailer(): void
    {
        $base = defined('PHPMAILER_PATH') ? PHPMAILER_PATH : (APP_PATH . '/lib/PHPMailer');
        if (!class_exists(PHPMailer::class)) {
            require_once $base . '/Exception.php';
            require_once $base . '/PHPMailer.php';
            require_once $base . '/SMTP.php';
        }
    }

    /**
     * Enviar correo HTML
     */
    public static function send(
        string $to,
        string $subject,
        string $htmlBody,
        string $toName = '',
        ?string $replyTo = null,
        ?string $replyName = null
    ): bool {
        self::loadPhpMailer();

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USER;
            $mail->Password   = MAIL_PASS;
            $mail->SMTPSecure = MAIL_ENCRYPTION === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = (int) MAIL_PORT;
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 25;
            $mail->SMTPAutoTLS = true;

            $from = defined('MAIL_FROM') ? MAIL_FROM : MAIL_USER;
            $fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'RUPE CDMX';
            $mail->setFrom($from, $fromName);
            $mail->addAddress($to, $toName !== '' ? $toName : $to);

            if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $mail->clearReplyTos();
                $mail->addReplyTo($replyTo, $replyName ?: $replyTo);
            } else {
                $mail->addReplyTo($from, $fromName);
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = self::wrapTemplate($htmlBody);
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

            $mail->send();
            if (function_exists('app_log')) {
                app_log('info', 'Mail sent', ['to' => $to, 'subject' => $subject]);
            }
            return true;
        } catch (MailException $e) {
            $err = isset($mail) ? $mail->ErrorInfo : $e->getMessage();
            if (function_exists('app_log')) {
                app_log('error', 'Mail failed: ' . $err, ['to' => $to]);
            }
            return false;
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'Mail exception: ' . $e->getMessage(), ['to' => $to]);
            }
            return false;
        }
    }

    private static function wrapTemplate(string $content): string
    {
        $year = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#F4F6F8;font-family:Segoe UI,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#F4F6F8;padding:24px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(107,29,47,.1);">
        <tr>
          <td style="background:#6B1D2F;padding:20px 28px;color:#fff;font-size:20px;font-weight:700;">
            RUPE CDMX
          </td>
        </tr>
        <tr>
          <td style="padding:28px;color:#333;font-size:15px;line-height:1.6;">
            {$content}
          </td>
        </tr>
        <tr>
          <td style="padding:16px 28px;background:#F1F3F5;color:#6C757D;font-size:12px;text-align:center;">
            © {$year} RUPE CDMX — Buscando a Huellitas<br>
            <a href="https://buscandoahuellitasrupe.com.mx" style="color:#6B1D2F;">buscandoahuellitasrupe.com.mx</a>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }

    public static function sendPasswordReset(string $to, string $name, string $token): bool
    {
        $url = rtrim(APP_URL, '/') . '/auth/reset?token=' . urlencode($token);
        $html = "
          <p>Hola <strong>" . htmlspecialchars($name) . "</strong>,</p>
          <p>Recibimos una solicitud para restablecer tu contraseña en RUPE CDMX.</p>
          <p style=\"text-align:center;margin:28px 0;\">
            <a href=\"{$url}\" style=\"background:#6B1D2F;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;\">
              Restablecer contraseña
            </a>
          </p>
          <p>Puedes abrir el enlace <strong>de inmediato</strong>. Si no solicitaste este cambio, ignora este correo.</p>
        ";
        return self::send($to, MAIL_SUBJECT_RESET, $html, $name);
    }


    /**
     * Credenciales de administrador recién creado (correo + contraseña en claro solo en este correo)
     */
    public static function sendAdminCredentials(string $to, string $name, string $password): bool
    {
        $loginUrl = rtrim(defined('APP_URL') ? APP_URL : '', '/') . '/';
        $html = '
          <p>Hola <strong>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</strong>,</p>
          <p>Se creó tu cuenta de <strong>administrador</strong> en <strong>RUPE CDMX</strong> (buscandoahuellitasrupe.com.mx).</p>
          <table style="width:100%;border-collapse:collapse;font-size:14px;margin:16px 0;background:#f8f9fa;border-radius:8px;">
            <tr>
              <td style="padding:10px 14px;color:#666;width:140px;">Correo registrado</td>
              <td style="padding:10px 14px;"><strong style="font-size:15px;">' . htmlspecialchars($to, ENT_QUOTES, 'UTF-8') . '</strong></td>
            </tr>
            <tr>
              <td style="padding:10px 14px;color:#666;">Contraseña generada (8 caracteres)</td>
              <td style="padding:10px 14px;"><code style="font-size:18px;font-weight:700;background:#fff;border:2px solid #6B1D2F;padding:8px 12px;border-radius:6px;letter-spacing:1px;user-select:all;">' . htmlspecialchars($password, ENT_QUOTES, 'UTF-8') . '</code></td>
            </tr>
            <tr>
              <td style="padding:10px 14px;color:#666;">Rol</td>
              <td style="padding:10px 14px;">Administrador</td>
            </tr>
          </table>
          <p style="margin:18px 0;">
            <a href="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#6B1D2F;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">Iniciar sesión</a>
          </p>
          <p style="margin:16px 0;font-size:14px;">
            Copia exactamente (8 caracteres, mayúsculas y números):<br>
            <strong style="font-family:Consolas,monospace;font-size:20px;letter-spacing:2px;">'
            . htmlspecialchars($password, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
            '</strong>
          </p>
          <p style="color:#666;font-size:13px;">Por seguridad, cambia la contraseña después de entrar. No compartas este correo.</p>
          <p style="color:#999;font-size:12px;margin-top:24px;">RUPE CDMX · buscandoahuellitasrupe.com.mx</p>
        ';
        $subject = defined('MAIL_SUBJECT_ADMIN_CREDENTIALS')
            ? MAIL_SUBJECT_ADMIN_CREDENTIALS
            : 'Tu acceso de administrador — RUPE CDMX';
        return self::send($to, $subject, $html, $name);
    }


    /** Notifica al usuario si su cuenta quedó Activa o Inactiva */
    public static function sendUserStatusChange(string $to, string $name, bool $activo): bool
    {
        $estado = $activo ? 'Activa' : 'Inactiva';
        $color = $activo ? '#198754' : '#6c757d';
        $html = '
          <p>Hola <strong>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</strong>,</p>
          <p>El estado de tu cuenta en <strong>RUPE CDMX</strong> fue actualizado.</p>
          <p style="margin:18px 0;">
            <span style="display:inline-block;background:' . $color . ';color:#fff;padding:8px 16px;border-radius:8px;font-weight:700;">
              Cuenta ' . $estado . '
            </span>
          </p>
          <table style="width:100%;border-collapse:collapse;font-size:14px;margin:12px 0;">
            <tr>
              <td style="padding:8px 0;color:#666;">Correo</td>
              <td style="padding:8px 0;"><strong>' . htmlspecialchars($to, ENT_QUOTES, 'UTF-8') . '</strong></td>
            </tr>
            <tr>
              <td style="padding:8px 0;color:#666;">Estado</td>
              <td style="padding:8px 0;"><strong>' . $estado . '</strong></td>
            </tr>
          </table>
          <p style="color:#666;font-size:13px;">' . (
            $activo
                ? 'Ya puedes iniciar sesión en la plataforma con normalidad.'
                : 'Tu acceso quedó suspendido. Si crees que es un error, contacta al administrador.'
          ) . '</p>
          <p style="color:#999;font-size:12px;margin-top:24px;">RUPE CDMX · buscandoahuellitasrupe.com.mx</p>
        ';
        $subject = $activo
            ? 'Tu cuenta está Activa — RUPE CDMX'
            : 'Tu cuenta está Inactiva — RUPE CDMX';
        return self::send($to, $subject, $html, $name);
    }

    public static function sendWelcome(string $to, string $name): bool
    {
        $html = "
          <p>Hola <strong>" . htmlspecialchars($name) . "</strong>,</p>
          <p>Tu cuenta de tutor en <strong>RUPE CDMX</strong> ha sido creada correctamente.</p>
          <p>Ya puedes registrar a tus mascotas y generar fichas con código QR.</p>
          <p style=\"text-align:center;margin:28px 0;\">
            <a href=\"" . APP_URL . "\" style=\"background:#6B1D2F;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;\">
              Ir al sistema
            </a>
          </p>
        ";
        return self::send($to, MAIL_SUBJECT_WELCOME, $html, $name);
    }

    public static function sendStatusChange(string $to, string $name, string $folio, string $estatus): bool
    {
        $html = "
          <p>Hola <strong>" . htmlspecialchars($name) . "</strong>,</p>
          <p>El estatus del reporte <strong>" . htmlspecialchars($folio) . "</strong> se actualizó a:</p>
          <p style=\"font-size:18px;font-weight:700;color:#6B1D2F;\">" . htmlspecialchars($estatus) . "</p>
          <p>Ingresa al sistema para ver más detalles.</p>
        ";
        return self::send($to, MAIL_SUBJECT_STATUS, $html, $name);
    }
}
