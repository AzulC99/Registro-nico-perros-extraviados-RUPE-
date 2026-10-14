<?php
/**
 * Si el document root apunta a la raíz del proyecto (no a public/),
 * redirigir al front controller correcto.
 */
$public = __DIR__ . '/public/index.php';
if (is_file($public)) {
    require $public;
    return;
}
http_response_code(200);
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>RUPE</title></head><body style="font-family:system-ui;padding:2rem">';
echo '<h1>RUPE CDMX</h1><p>Configura el document root de Hostinger a la carpeta <code>public/</code>.</p>';
echo '</body></html>';
