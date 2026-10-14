<<<<<<< HEAD
# RUPE CDMX — Buscando a Huellitas

Sistema de registro y seguimiento de mascotas extraviadas (CDMX).

**Stack:** PHP 8.3.33 · PDO · MySQL · JS ES6 · sin Composer · sin .env

## Requisitos Hostinger

- PHP 8.3+
- MySQL / MariaDB
- Extensiones: pdo_mysql, json, mbstring, openssl
- SMTP Hostinger (ya configurado en `app/config/config.php`)

## Instalación

1. Subir el contenido al hosting (document root = `public/`).
2. Importar `sql/schema.sql` en la base `u977404351_buscandohuella`.
3. Verificar credenciales en `app/config/config.php` (DB, SMTP, reCAPTCHA).
4. Permisos: `storage/` y `public/assets/uploads/` escribibles (755/775).
5. `.htaccess` en `public/` debe reescribir a `index.php`.

## Roles

| Rol | Constante | Acceso |
|-----|-----------|--------|
| `tutor` | ROLE_TUTOR | Panel tutor, mascotas, GPS/QR, ficha PDF |
| `administrador` | ROLE_ADMIN | Usuarios, catálogos, reportes, auditoría, config |

## Módulo Tutor (`/tutor`)

Panel del tutor (rol `tutor`) para registrar y dar seguimiento a mascotas extraviadas en CDMX. Interfaz SPA por secciones (menú superior / hamburguesa en móvil), datos reales vía API REST y MySQL.

### Acceso

- Login en la portada con cuenta **activa** (`usuarios.rol = tutor`, `activo = 1`).
- Ruta web: `GET /tutor` → `TutorController::panel`.
- Sesión + CSRF; sin sesión válida redirige al inicio.

### Secciones del menú

| # | Módulo | Vista / clave | Descripción |
|---|--------|---------------|-------------|
| 1 | **Panel analítico** | `analisis` | KPIs, gráficas (barras/pastel), filtros, insights, exportación CSV/Excel y PDF del análisis. |
| 2 | **Registro** | `registro` | Formulario completo de extravío (datos de mascota, raza, alcaldía, foto, contacto). Genera folio RUPE y guarda en `mascotas`. |
| 3 | **Tabla CRUD** | `crud` | Listado de reportes del tutor: folio, foto, mascota, raza, alcaldía, fecha, estatus. Editar / eliminar con persistencia en BD. |
| 4 | **Ficha PDF + QR** | `ficha-pdf` | Ficha oficial tamaño carta: datos, foto, GPS, QR público (`/mascota/{token}`). Impresión / guardar como PDF. |
| 5 | **GPS / QR** | `gps-qr` | Captura de ubicación (geolocalización o mapa), búsqueda de dirección, envío de **avistamiento** al tutor y guardado de lat/lng. |
| 6 | **Seguimiento** | `seguimiento` | Bitácora de avistamientos y reportes: fecha, condición, foto, GPS, referencia, mapa y detalle. |
| 7 | **Estatus** | `estatus` | Consulta y actualización de estatus del caso (En Búsqueda, En Resguardo, Localizado) y bitácora. |
| 8 | **Albergues** | `albergues` | Directorio de albergues, filtros por alcaldía y asignación de casos en resguardo. |
| — | **Mi perfil** | `perfil` | Datos de contacto del tutor, estadísticas, select de folios, ver ficha / PDF. |
| — | **Configuración** | `configuracion` / `cuenta` | Cambio de contraseña, notificaciones, preferencias (idioma, alcaldía por defecto). |
| — | **Ayuda** | `ayuda` | FAQ y formulario de contacto (correo a buscandoahuellitasrupe.com.mx). |

### Flujos principales

1. **Registrar extravío** → folio único + foto + catálogos (`razas`, `alcaldias`) → fila en `mascotas`.
2. **Capturar GPS / avistamiento** → coordenadas en mascota o `seguimientos` → visible en ficha y seguimiento.
3. **Actualizar estatus** → cambia estado del caso y deja rastro en bitácora.
4. **Imprimir ficha** → PDF carta con QR para consulta pública.
5. **Analítica** → totales y tasas a partir de los reportes del propio tutor.

### API usada por el tutor

```
GET/POST/PUT/DELETE  /api/tutor/mascotas[/{id}]
POST                 /api/tutor/mascotas/{id}/seguimiento
POST                 /api/tutor/mascotas/{id}/gps
GET/POST             /api/tutor/perfil
GET/POST             /api/tutor/preferencias
POST                 /api/tutor/password
POST                 /api/tutor/ayuda-contacto
GET                  /api/catalogos/{alcaldias|razas|albergues|ayuda}
GET                  /api/geo/geocode?q=
```

### Archivos clave

- Controlador: `app/controllers/TutorController.php`
- Vistas: `app/views/tutor/*.php` (núcleo en `crud.php` + módulos)
- JS ES6: `public/assets/js/tutor-*.js` (registro, CRUD, GPS, ficha, perfil, cuenta, ayuda, albergues, analítico)
- Modelos: `MascotaDAO`, `SeguimientoDAO`, `PreferenciaDAO`, `AlcaldiaDAO`, `RazaDAO`, `AlbergueDAO`

### Notas

- Solo ve y edita **sus** mascotas (`id_tutor` de la sesión).
- Cuenta **inactiva** no puede iniciar sesión (mensaje en login).
- Menú responsive: hamburguesa en pantallas &lt; 1200px.

## Consultas SQL del backend del tutor

Todas las consultas usan **PDO prepared statements** (`:param`). El tutor solo opera sobre filas con `id_tutor = sesión`.

### 1. Listado CRUD / panel (`MascotaDAO::listByTutor`)

```sql
SELECT
  m.id_mascota, m.folio, m.nombre, m.id_raza, m.especie, m.sexo,
  m.color, m.edad_aprox, m.tamano, m.collar, m.microchip,
  m.senas_particulares, m.foto_url, m.id_alcaldia, m.colonia,
  m.direccion_ref, m.latitud, m.longitud, m.fecha_extravio,
  m.estatus, m.activo, m.created_at,
  r.nombre AS raza_nombre,
  a.nombre AS alcaldia_nombre,
  (SELECT CAST(s.latitud AS CHAR) FROM seguimientos s
     WHERE s.id_mascota = m.id_mascota
     ORDER BY s.created_at DESC LIMIT 1) AS gps_lat_ultimo,
  (SELECT CAST(s.longitud AS CHAR) FROM seguimientos s
     WHERE s.id_mascota = m.id_mascota
     ORDER BY s.created_at DESC LIMIT 1) AS gps_lng_ultimo
FROM mascotas m
LEFT JOIN razas r ON r.id_raza = m.id_raza
LEFT JOIN alcaldias a ON a.id_alcaldia = m.id_alcaldia
WHERE m.id_tutor = :id_tutor
  AND m.activo = 1
ORDER BY m.created_at DESC;
```

### 2. Ficha / PDF / detalle (`MascotaDAO::findWithJoins` / `findByFolio`)

```sql
SELECT m.*,
       r.nombre AS raza_nombre,
       a.nombre AS alcaldia_nombre,
       u.nombre AS tutor_nombre,
       u.correo AS tutor_correo,
       u.telefono AS tutor_telefono
FROM mascotas m
LEFT JOIN razas r ON r.id_raza = m.id_raza
LEFT JOIN alcaldias a ON a.id_alcaldia = m.id_alcaldia
LEFT JOIN usuarios u ON u.id_usuario = m.id_tutor
WHERE m.id_mascota = :id   -- o m.folio = :folio / m.qr_token = :token
LIMIT 1;
```

### 3. Foto por id (`MascotaDAO::fotoById`)

```sql
SELECT foto_url, especie
FROM mascotas
WHERE id_mascota = :id AND activo = 1
LIMIT 1;
```

### 4. Alta de reporte (`MascotaDAO::create` → INSERT)

```sql
INSERT INTO mascotas (
  folio, qr_token, id_tutor, nombre, id_raza, especie, sexo,
  color, edad_aprox, senas_particulares, collar, microchip, tamano,
  foto_url, id_alcaldia, colonia, direccion_ref, latitud, longitud,
  fecha_extravio, estatus, id_albergue, notas, activo
) VALUES (
  :folio, :qr_token, :id_tutor, :nombre, :id_raza, :especie, :sexo,
  :color, :edad_aprox, :senas_particulares, :collar, :microchip, :tamano,
  :foto_url, :id_alcaldia, :colonia, :direccion_ref, :latitud, :longitud,
  :fecha_extravio, :estatus, :id_albergue, :notas, 1
);
```

- `id_raza` y `id_alcaldia` deben ser IDs del catálogo (no texto).
- `folio` se genera en servidor (`RUPE-YYYYMMDD-XXXX`).

### 5. Edición CRUD (`MascotaDAO::update`)

```sql
UPDATE mascotas SET
  nombre = :nombre,
  id_raza = :id_raza,
  -- ... campos permitidos ...
  estatus = :estatus
WHERE id_mascota = :id;
```

Campos permitidos: `nombre`, `id_raza`, `especie`, `sexo`, `color`, `edad_aprox`, `senas_particulares`, `collar`, `microchip`, `tamano`, `foto_url`, `id_alcaldia`, `colonia`, `direccion_ref`, `latitud`, `longitud`, `fecha_extravio`, `estatus`, `id_albergue`, `notas`, `activo`.

### 6. KPIs del tutor (`MascotaDAO::statsByTutor`)

```sql
SELECT estatus, COUNT(*) AS total
FROM mascotas
WHERE id_tutor = :id_tutor AND activo = 1
GROUP BY estatus;
```

### 7. Bitácora / seguimiento (`SeguimientoDAO::listByMascota`)

```sql
SELECT s.id_seguimiento, s.id_mascota,
       COALESCE(s.tipo, 'nota') AS tipo,
       COALESCE(s.descripcion, '') AS descripcion,
       COALESCE(s.latitud, '') AS latitud,
       COALESCE(s.longitud, '') AS longitud,
       COALESCE(s.foto_url, '') AS foto_url,
       COALESCE(s.created_at, '') AS created_at,
       COALESCE(u.nombre, '') AS usuario_nombre
FROM seguimientos s
LEFT JOIN usuarios u ON u.id_usuario = s.id_usuario
WHERE s.id_mascota = :id_mascota
ORDER BY s.created_at DESC;
```

### 8. Avistamiento / GPS (`SeguimientoDAO` INSERT + UPDATE mascota)

```sql
INSERT INTO seguimientos (
  id_mascota, id_usuario, tipo, descripcion,
  latitud, longitud, foto_url
) VALUES (
  :id_mascota, :id_usuario, 'avistamiento', :descripcion,
  :latitud, :longitud, :foto_url
);

-- Opcional: sincronizar último GPS en la mascota
UPDATE mascotas
SET latitud = :latitud, longitud = :longitud
WHERE id_mascota = :id AND id_tutor = :id_tutor;
```

### 9. Tabla seguimiento del tutor (`SeguimientoDAO::listBitacoraByTutor`)

```sql
SELECT m.id_mascota,
       COALESCE(m.folio, '') AS folio,
       COALESCE(m.nombre, '') AS mascota,
       COALESCE(m.estatus, 'En Búsqueda') AS estatus,
       -- último seguimiento (subconsulta / JOIN)
       s.descripcion, s.latitud, s.longitud, s.foto_url, s.created_at
FROM mascotas m
LEFT JOIN seguimientos s ON s.id_mascota = m.id_mascota
WHERE m.id_tutor = :id_tutor AND m.activo = 1
ORDER BY m.created_at DESC, s.created_at DESC;
```

### 10. Preferencias (`PreferenciaDAO`)

```sql
SELECT * FROM preferencias_tutor WHERE id_usuario = :id_usuario LIMIT 1;

-- Alta o actualización (upsert)
INSERT INTO preferencias_tutor (id_usuario, notif_email, notif_sms, ...)
VALUES (...);
-- o
UPDATE preferencias_tutor SET notif_email = :notif_email, ...
WHERE id_preferencia = :id;
```

### 11. Catálogos usados en registro (filtros del formulario)

```sql
SELECT * FROM razas WHERE activo = 1 ORDER BY especie, nombre;
SELECT * FROM alcaldias WHERE activo = 1 ORDER BY nombre;
SELECT * FROM albergues WHERE activo = 1 ORDER BY nombre;
```

### Endpoints que disparan estas consultas

| Endpoint | Consulta principal |
|----------|--------------------|
| `GET /api/tutor/mascotas` | `listByTutor` |
| `POST /api/tutor/mascotas` | `create` (INSERT) |
| `PUT /api/tutor/mascotas/{id}` | `update` |
| `GET /api/tutor/mascotas/{id}` | `findWithJoins` |
| `POST .../seguimiento` o GPS | INSERT `seguimientos` + UPDATE GPS |
| `GET /api/tutor/perfil` | `usuarios` + `statsByTutor` |
| `PUT /api/tutor/preferencias` | `PreferenciaDAO::upsert` |

Archivos: `app/models/MascotaDAO.php`, `SeguimientoDAO.php`, `PreferenciaDAO.php`, `app/controllers/TutorController.php`.

## 11 tablas (DAO)

1. `usuarios` — UsuarioDAO  
2. `password_resets` — PasswordResetDAO  
3. `alcaldias` — AlcaldiaDAO  
4. `razas` — RazaDAO  
5. `albergues` — AlbergueDAO  
6. `mascotas` — MascotaDAO  
7. `seguimientos` — SeguimientoDAO  
8. `preferencias_tutor` — PreferenciaDAO  
9. `auditoria` — AuditoriaDAO  
10. `configuraciones` — ConfiguracionDAO  
11. `ayuda_faqs` — AyudaDAO  

## API REST (resumen)

```
POST /api/auth/login|register|logout|forgot|reset
GET  /api/auth/me
GET  /api/tutor/mascotas
POST /api/tutor/mascotas
PUT  /api/tutor/mascotas/{id}
POST /api/tutor/mascotas/{id}/seguimiento
GET  /api/tutor/perfil
PUT  /api/tutor/perfil|preferencias
GET  /api/catalogos/{alcaldias|razas|albergues|ayuda}
GET  /api/geo/geocode?q=
GET  /api/admin/usuarios|alcaldias|razas|reportes|auditoria|config|ayuda
GET  /api/public/mascota/{folio}
GET  /api/health
```

## Seguridad

- Sesión: HttpOnly, Secure, SameSite=Lax  
- CSRF en mutaciones (header `X-CSRF-Token` o `_csrf`)  
- reCAPTCHA v3 en login/registro/forgot  
- Rate limit por IP (storage/rate)  
- Passwords bcrypt cost 12  
- Validaciones servidor (Validator) + cliente sin flash  
- Headers: CSP, X-Frame-Options, nosniff  
- PDO prepared statements  
- Excepciones tipadas → JSON estructurado  

## Frontend JS ES6

Incluir en vistas autenticadas:

```html
<script>
  window.__CSRF__ = '<?= e(csrf_token()) ?>';
  window.__RECAPTCHA_SITE_KEY__ = '<?= RECAPTCHA_SITE_KEY ?>';
</script>
<script src="https://www.google.com/recaptcha/api.js?render=SITE_KEY"></script>
<script type="module" src="/assets/js/api.js"></script>
```

Uso:

```js
import RUPE from '/assets/js/api.js';
// o window.RUPE
const { data } = await RUPE.listMascotas();
await RUPE.createMascota({ nombre: 'Firulais', especie: 'perro' }, formEl);
```

## Librerías gratis incluidas

- PHPMailer (app/lib/PHPMailer) — SMTP Hostinger  
- jsPDF (public/assets/lib)  
- qrcode.min.js  
- Nominatim OSM (geocoding)  
- Leaflet / Chart.js (CDN en vistas)  
- reCAPTCHA v3  

## Admin por defecto

Correo: `admin@buscandoahuellitasrupe.com.mx`  
**Cambiar la contraseña** tras el primer acceso (el hash del schema es placeholder).

## Estructura

```
app/
  config/   config.php database.php mail.php apis.php routes.php
  core/     App Router Request Response Database ExceptionHandler
  controllers/
  models/   *DAO (11)
  helpers/  functions Validator Recaptcha Mailer
  views/
public/
  index.php  assets/js/api.js  assets/lib/
sql/schema.sql
storage/logs  storage/rate
```

## Correcciones aplicadas (Hostinger PHP 8.3.33)

1. **api.js**: eliminado `export default` (rompía scripts clásicos sin type=module). API global en window.RUPE.
2. **Request.php / BaseDAO.php**: parámetros nullable explícitos (?string) compatibles PHP 8.3 (sin deprecations).
3. **UsuarioDAO / AuditoriaDAO**: LIMIT/OFFSET interpolados como enteros (PDO nativo + MySQL Hostinger).
4. Vistas tutor/admin usan rutas /assets/... correctas con document root = public/.

=======
# Registro-nico-perros-extraviados-RUPE-
Macroproyecto por parte de la UnADM para titulación 
>>>>>>> f7b96c1376874cd7391048f87bf266bb01af91ce
