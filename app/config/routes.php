<?php
/**
 * RUPE CDMX — Rutas web + REST
 * app/config/routes.php
 */

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\TutorController;
use App\Controllers\AdminController;
use App\Controllers\ApiController;

/** @var \App\Core\Router $router */

// ---------- Web públicas ----------
$router->get('/', [HomeController::class, 'index']);
$router->get('/conocer', [HomeController::class, 'conocer']);
$router->get('/blog', [HomeController::class, 'blog']);
$router->get('/mascota/{token}', [HomeController::class, 'fichaPublica']);
$router->get('/aviso-privacidad', [HomeController::class, 'avisoPrivacidad']);
$router->get('/terminos-servicio', [HomeController::class, 'terminosServicio']);

// Auth vistas
$router->get('/auth/logout', [AuthController::class, 'logoutWeb']);
$router->get('/auth/forgot', [AuthController::class, 'showForgot']);
$router->get('/auth/reset', [AuthController::class, 'showReset']);

// Paneles
$router->get('/tutor', [TutorController::class, 'panel']);
$router->get('/tutor/gps', [TutorController::class, 'gpsqr']);
$router->get('/tutor/gps-qr', [TutorController::class, 'gpsqr']);
$router->get('/tutor/gpsqr', [TutorController::class, 'gpsqr']);
$router->get('/tutor/{modulo}', [TutorController::class, 'modulo']);
$router->get('/admin', [AdminController::class, 'panel']);
$router->get('/admin/{modulo}', [AdminController::class, 'modulo']);

// ---------- API Auth ----------
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/auth/me', [AuthController::class, 'me']);
$router->post('/api/auth/forgot', [AuthController::class, 'forgot']);
$router->post('/api/auth/reset', [AuthController::class, 'reset']);

// ---------- API Tutor ----------
$router->get('/api/tutor/mascotas', [TutorController::class, 'listMascotas']);
$router->get('/api/tutor/mascotas/{id}', [TutorController::class, 'getMascota']);
$router->post('/api/tutor/mascotas', [TutorController::class, 'createMascota']);
$router->put('/api/tutor/mascotas/{id}', [TutorController::class, 'updateMascota']);
$router->post('/api/tutor/mascotas/{id}', [TutorController::class, 'updateMascota']); // multipart foto
$router->delete('/api/tutor/mascotas/{id}', [TutorController::class, 'deleteMascota']);
$router->post('/api/tutor/mascotas/{id}/eliminar', [TutorController::class, 'deleteMascota']);
$router->post('/api/tutor/mascotas/{id}/seguimiento', [TutorController::class, 'addSeguimiento']);
$router->post('/api/tutor/mascotas/{id}/gps', [TutorController::class, 'updateGpsMascota']);
$router->get('/api/tutor/perfil', [TutorController::class, 'perfil']);
$router->put('/api/tutor/perfil', [TutorController::class, 'updatePerfil']);
$router->post('/api/tutor/perfil', [TutorController::class, 'updatePerfil']);
$router->get('/api/tutor/preferencias', [TutorController::class, 'getPreferencias']);
$router->put('/api/tutor/preferencias', [TutorController::class, 'updatePreferencias']);
$router->post('/api/tutor/preferencias', [TutorController::class, 'updatePreferencias']);
$router->put('/api/tutor/password', [TutorController::class, 'changePassword']);
$router->post('/api/tutor/password', [TutorController::class, 'changePassword']);
$router->post('/api/tutor/ayuda-contacto', [TutorController::class, 'contactoAyuda']);
$router->post('/api/tutor/mensaje-ciudadano', [TutorController::class, 'mensajeCiudadano']);
$router->get('/api/tutor/albergues', [TutorController::class, 'listAlbergues']);
$router->post('/api/tutor/albergues', [TutorController::class, 'createAlbergue']);
$router->put('/api/tutor/albergues/{id}', [TutorController::class, 'updateAlbergue']);
$router->post('/api/tutor/albergues/{id}', [TutorController::class, 'updateAlbergue']);
$router->delete('/api/tutor/albergues/{id}', [TutorController::class, 'deleteAlbergue']);
$router->post('/api/tutor/albergues/{id}/eliminar', [TutorController::class, 'deleteAlbergue']);
$router->get('/api/catalogos/{tipo}', [TutorController::class, 'catalogos']);
$router->get('/api/geo/geocode', [TutorController::class, 'geocode']);

// ---------- API Admin ----------
$router->get('/api/admin/usuarios', [AdminController::class, 'listUsuarios']);
$router->post('/api/admin/usuarios', [AdminController::class, 'createUsuario']);
$router->patch('/api/admin/usuarios/{id}', [AdminController::class, 'updateUsuario']);
$router->get('/api/admin/alcaldias', [AdminController::class, 'listAlcaldias']);
$router->post('/api/admin/alcaldias', [AdminController::class, 'saveAlcaldia']);
$router->put('/api/admin/alcaldias/{id}', [AdminController::class, 'saveAlcaldia']);
$router->get('/api/admin/razas', [AdminController::class, 'listRazas']);
$router->post('/api/admin/razas', [AdminController::class, 'saveRaza']);
$router->put('/api/admin/razas/{id}', [AdminController::class, 'saveRaza']);
$router->post('/api/admin/razas/{id}', [AdminController::class, 'saveRaza']);
$router->get('/api/admin/reportes', [AdminController::class, 'reportes']);
$router->put('/api/admin/reportes/{id}', [AdminController::class, 'updateReporteEstatus']);
$router->post('/api/admin/reportes/{id}/validar', [AdminController::class, 'updateReporteEstatus']);
$router->get('/api/admin/auditoria', [AdminController::class, 'auditoria']);
$router->get('/api/admin/config', [AdminController::class, 'getConfig']);
$router->post('/api/admin/config', [AdminController::class, 'setConfig']);
$router->get('/api/admin/perfil', [AdminController::class, 'perfil']);
$router->put('/api/admin/perfil', [AdminController::class, 'updatePerfil']);
$router->put('/api/admin/password', [AdminController::class, 'changePassword']);
$router->post('/api/admin/password', [AdminController::class, 'changePassword']);
$router->post('/api/admin/perfil', [AdminController::class, 'updatePerfil']);
$router->get('/api/admin/ayuda', [AdminController::class, 'listAyuda']);
$router->post('/api/admin/ayuda', [AdminController::class, 'saveAyuda']);
$router->post('/api/admin/ayuda-contacto', [AdminController::class, 'contactoAyuda']);
$router->put('/api/admin/ayuda/{id}', [AdminController::class, 'saveAyuda']);

// ---------- API pública / util ----------
$router->get('/api/public/qr', [ApiController::class, 'qr']);
$router->get('/api/health', [ApiController::class, 'health']);
$router->get('/api/csrf', [ApiController::class, 'csrf']);
$router->get('/api/public/mascota/{folio}', [ApiController::class, 'mascotaPublica']);

$router->get('/api/public/mascotas', [ApiController::class, 'listMascotasPublicas']);
$router->get('/api/public/alcaldias', [ApiController::class, 'alcaldiasPublicas']);
$router->post('/api/public/contacto', [ApiController::class, 'contacto']);
$router->post('/api/public/avistamiento', [ApiController::class, 'reportarAvistamiento']);

