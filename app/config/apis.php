<?php
/**
 * RUPE CDMX — APIs externas gratuitas
 * app/config/apis.php
 * Nominatim (OSM), reCAPTCHA v3, sin claves de pago
 */

declare(strict_types=1);

/** OpenStreetMap Nominatim — geocoding gratuito (uso responsable) */
define('NOMINATIM_URL', 'https://nominatim.openstreetmap.org');
define('NOMINATIM_USER_AGENT', 'RUPE-CDMX/1.0 (https://buscandoahuellitasrupe.com.mx; contacto@buscandoahuellitasrupe.com.mx)');
define('NOMINATIM_EMAIL', 'admin@buscandoahuellitasrupe.com.mx');

/** Leaflet / tiles gratuitos */
define('LEAFLET_TILE_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
define('LEAFLET_ATTRIBUTION', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>');

/** Rate-limit simple para APIs internas (requests por minuto por IP) */
define('API_RATE_LIMIT', 60);
define('API_RATE_WINDOW', 60); // segundos
