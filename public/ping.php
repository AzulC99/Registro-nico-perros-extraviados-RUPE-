<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
http_response_code(200);
echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>RUPE ping</title></head><body style="font-family:system-ui;padding:2rem">';
echo '<h1>PHP OK</h1>';
echo '<p>Versión: <strong>' . htmlspecialchars(PHP_VERSION) . '</strong></p>';
echo '<p>Fecha: ' . date('c') . '</p>';
echo '<p>ROOT padre de public: <code>' . htmlspecialchars(dirname(__DIR__)) . '</code></p>';
echo '<p>¿Existe app/? ' . (is_dir(dirname(__DIR__) . '/app') ? 'SÍ' : 'NO') . '</p>';
echo '<p>¿Existe config.php? ' . (is_file(dirname(__DIR__) . '/app/config/config.php') ? 'SÍ' : 'NO') . '</p>';
echo '<p>PDO MySQL: ' . (extension_loaded('pdo_mysql') ? 'SÍ' : 'NO') . '</p>';
echo '<p><a href="/">Ir a portada</a></p>';
echo '</body></html>';
