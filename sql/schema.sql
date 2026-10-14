-- RUPE CDMX — Schema 11 tablas
-- MySQL 8 / MariaDB · utf8mb4 · PHP 8.3.31
-- Hostinger compatible

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. usuarios (admin + tutor)
CREATE TABLE IF NOT EXISTS usuarios (
  id_usuario        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre            VARCHAR(120) NOT NULL,
  correo            VARCHAR(180) NOT NULL,
  telefono          VARCHAR(20)  NULL,
  password_hash     VARCHAR(255) NOT NULL,
  rol               ENUM('administrador','tutor') NOT NULL DEFAULT 'tutor',
  activo            TINYINT(1) NOT NULL DEFAULT 1,
  acepta_privacidad TINYINT(1) NOT NULL DEFAULT 0,
  ultimo_acceso     DATETIME NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_usuarios_correo (correo),
  KEY idx_usuarios_rol (rol),
  KEY idx_usuarios_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. password_resets
CREATE TABLE IF NOT EXISTS password_resets (
  id_reset     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario   INT UNSIGNED NOT NULL,
  token_hash   VARCHAR(64) NOT NULL,
  expires_at   DATETIME NOT NULL,
  used_at      DATETIME NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_pr_token (token_hash),
  KEY idx_pr_usuario (id_usuario),
  CONSTRAINT fk_pr_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. alcaldias (CDMX)
CREATE TABLE IF NOT EXISTS alcaldias (
  id_alcaldia INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(80) NOT NULL,
  clave       VARCHAR(10) NULL,
  zona        VARCHAR(40) NULL,
  cp_base     VARCHAR(10) NULL,
  activo      TINYINT(1) NOT NULL DEFAULT 1,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_alcaldia_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. razas
CREATE TABLE IF NOT EXISTS razas (
  id_raza    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre     VARCHAR(80) NOT NULL,
  especie    ENUM('perro','gato','otro') NOT NULL DEFAULT 'perro',
  tamano     VARCHAR(20) NULL,
  activo     TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_raza_nombre_esp (nombre, especie)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. albergues
CREATE TABLE IF NOT EXISTS albergues (
  id_albergue  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre       VARCHAR(150) NOT NULL,
  direccion    VARCHAR(255) NULL,
  id_alcaldia  INT UNSIGNED NULL,
  telefono     VARCHAR(20) NULL,
  correo       VARCHAR(180) NULL,
  latitud      DECIMAL(10,7) NULL,
  longitud     DECIMAL(10,7) NULL,
  capacidad    INT UNSIGNED NULL,
  activo       TINYINT(1) NOT NULL DEFAULT 1,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_albergue_alcaldia (id_alcaldia),
  CONSTRAINT fk_albergue_alcaldia FOREIGN KEY (id_alcaldia) REFERENCES alcaldias(id_alcaldia) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. mascotas / reportes
CREATE TABLE IF NOT EXISTS mascotas (
  id_mascota       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  folio            VARCHAR(32) NOT NULL,
  id_tutor         INT UNSIGNED NOT NULL,
  nombre           VARCHAR(80) NOT NULL,
  id_raza          INT UNSIGNED NULL,
  especie          ENUM('perro','gato','otro') NOT NULL DEFAULT 'perro',
  sexo             ENUM('macho','hembra','desconocido') NOT NULL DEFAULT 'desconocido',
  color            VARCHAR(80) NULL,
  edad_aprox       VARCHAR(40) NULL,
  senas_particulares TEXT NULL,
  collar           VARCHAR(120) NULL,
  microchip        VARCHAR(120) NULL,
  tamano           VARCHAR(40) NULL,
  foto_url         VARCHAR(255) NULL,
  id_alcaldia      INT UNSIGNED NULL,
  colonia          VARCHAR(120) NULL,
  direccion_ref    VARCHAR(255) NULL,
  latitud          DECIMAL(10,7) NULL,
  longitud         DECIMAL(10,7) NULL,
  fecha_extravio   DATE NULL,
  hora_extravio    TIME NULL,
  estatus          ENUM('En Búsqueda','En Resguardo','Localizado') NOT NULL DEFAULT 'En Búsqueda',
  id_albergue      INT UNSIGNED NULL,
  notas            TEXT NULL,
  qr_token         VARCHAR(64) NOT NULL,
  activo           TINYINT(1) NOT NULL DEFAULT 1,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_mascota_folio (folio),
  UNIQUE KEY uk_mascota_qr (qr_token),
  KEY idx_mascota_tutor (id_tutor),
  KEY idx_mascota_estatus (estatus),
  KEY idx_mascota_alcaldia (id_alcaldia),
  CONSTRAINT fk_mascota_tutor FOREIGN KEY (id_tutor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_mascota_raza FOREIGN KEY (id_raza) REFERENCES razas(id_raza) ON DELETE SET NULL,
  CONSTRAINT fk_mascota_alcaldia FOREIGN KEY (id_alcaldia) REFERENCES alcaldias(id_alcaldia) ON DELETE SET NULL,
  CONSTRAINT fk_mascota_albergue FOREIGN KEY (id_albergue) REFERENCES albergues(id_albergue) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. seguimientos (avistamientos / bitácora)
CREATE TABLE IF NOT EXISTS seguimientos (
  id_seguimiento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_mascota     INT UNSIGNED NOT NULL,
  id_usuario     INT UNSIGNED NULL,
  folio          VARCHAR(40) NULL,
  condicion      VARCHAR(80) NOT NULL DEFAULT 'Observado',
  foto_url       VARCHAR(255) NULL,
  referencia     VARCHAR(120) NULL,
  contacto       VARCHAR(80) NULL,
  telefono       VARCHAR(20) NULL,
  correo         VARCHAR(100) NULL,
  latitud        DECIMAL(10,7) NULL,
  longitud       DECIMAL(10,7) NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_seg_mascota (id_mascota),
  KEY idx_seg_usuario (id_usuario),
  KEY idx_seg_folio (folio),
  CONSTRAINT fk_seg_mascota FOREIGN KEY (id_mascota) REFERENCES mascotas(id_mascota) ON DELETE CASCADE,
  CONSTRAINT fk_seg_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. preferencias_tutor
CREATE TABLE IF NOT EXISTS preferencias_tutor (
  id_preferencia         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario             INT UNSIGNED NOT NULL,
  notif_email            TINYINT(1) NOT NULL DEFAULT 1,
  notif_sms              TINYINT(1) NOT NULL DEFAULT 0,
  notif_avistamiento     TINYINT(1) NOT NULL DEFAULT 1,
  notif_cambio_estatus   TINYINT(1) NOT NULL DEFAULT 1,
  notif_boletin          TINYINT(1) NOT NULL DEFAULT 0,
  idioma                 VARCHAR(5) NOT NULL DEFAULT 'es',
  id_alcaldia_default    INT UNSIGNED NULL,
  updated_at             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_pref_usuario (id_usuario),
  CONSTRAINT fk_pref_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_pref_alcaldia FOREIGN KEY (id_alcaldia_default) REFERENCES alcaldias(id_alcaldia) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. auditoria
CREATE TABLE IF NOT EXISTS auditoria (
  id_auditoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario   INT UNSIGNED NULL,
  accion       VARCHAR(80) NOT NULL,
  tabla        VARCHAR(60) NULL,
  id_registro  INT UNSIGNED NULL,
  detalle      JSON NULL,
  ip           VARCHAR(45) NULL,
  user_agent   VARCHAR(255) NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_aud_usuario (id_usuario),
  KEY idx_aud_accion (accion),
  KEY idx_aud_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. configuraciones (clave-valor sistema)
CREATE TABLE IF NOT EXISTS configuraciones (
  id_config   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  clave       VARCHAR(80) NOT NULL,
  valor       TEXT NULL,
  tipo        ENUM('string','int','bool','json') NOT NULL DEFAULT 'string',
  descripcion VARCHAR(255) NULL,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_config_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. ayuda_faqs (módulo ayuda)
CREATE TABLE IF NOT EXISTS ayuda_faqs (
  id_faq      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria   VARCHAR(60) NOT NULL DEFAULT 'general',
  pregunta    VARCHAR(255) NOT NULL,
  respuesta   TEXT NOT NULL,
  orden       INT NOT NULL DEFAULT 0,
  activo      TINYINT(1) NOT NULL DEFAULT 1,
  rol_visible ENUM('todos','tutor','administrador') NOT NULL DEFAULT 'todos',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_faq_cat (categoria),
  KEY idx_faq_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Datos semilla alcaldías CDMX
INSERT IGNORE INTO alcaldias (nombre, clave) VALUES
('Álvaro Obregón','AO'),('Azcapotzalco','AZ'),('Benito Juárez','BJ'),
('Coyoacán','COY'),('Cuajimalpa','CUA'),('Cuauhtémoc','CUH'),
('Gustavo A. Madero','GAM'),('Iztacalco','IZC'),('Iztapalapa','IZP'),
('La Magdalena Contreras','MC'),('Miguel Hidalgo','MH'),('Milpa Alta','MA'),
('Tláhuac','TLH'),('Tlalpan','TLP'),('Venustiano Carranza','VC'),
('Xochimilco','XO');

-- Razas comunes
INSERT IGNORE INTO razas (nombre, especie) VALUES
('Mestizo','perro'),('Chihuahua','perro'),('Poodle / Caniche','perro'),('Labrador Retriever','perro'),
('Pastor Alemán','perro'),('Bulldog Francés','perro'),('Beagle','perro'),('Schnauzer','perro'),
('Golden Retriever','perro'),('Husky Siberiano','perro'),('Boxer','perro'),('Pitbull / American Stafford','perro'),
('Cocker Spaniel','perro'),('Dálmata','perro'),('Rottweiler','perro'),('Doberman','perro'),
('Shih Tzu','perro'),('Yorkshire Terrier','perro'),('Pug','perro'),('Border Collie','perro'),
('Akita','perro'),('San Bernardo','perro'),('Maltese','perro'),('French Poodle Toy','perro'),
('Mestizo','gato'),('Siamés','gato'),('Persa','gato'),('Maine Coon','gato'),
('Bengalí','gato'),('Ragdoll','gato'),('British Shorthair','gato'),('Sphynx','gato'),
('Abisinio','gato'),('Scottish Fold','gato');

-- Rellenar tamaños de catálogo (si columna existe)
UPDATE razas SET tamano = 'Miniatura' WHERE nombre IN ('Chihuahua','French Poodle Toy','Yorkshire Terrier','Maltese') AND (tamano IS NULL OR tamano = '');
UPDATE razas SET tamano = 'Pequeño' WHERE nombre IN ('Pug','Poodle / Caniche','Bulldog Francés','Shih Tzu','Siamés','Persa','Abisinio','Scottish Fold') AND (tamano IS NULL OR tamano = '');
UPDATE razas SET tamano = 'Mediano' WHERE nombre IN ('Beagle','Cocker Spaniel','Schnauzer','Border Collie','Mestizo','Bengalí','British Shorthair','Sphynx') AND (tamano IS NULL OR tamano = '');
UPDATE razas SET tamano = 'Grande' WHERE nombre IN ('Labrador Retriever','Pastor Alemán','Golden Retriever','Husky Siberiano','Boxer','Pitbull / American Stafford','Dálmata','Rottweiler','Doberman','Akita','Maine Coon','Ragdoll') AND (tamano IS NULL OR tamano = '');
UPDATE razas SET tamano = 'Gigante' WHERE nombre IN ('San Bernardo') AND (tamano IS NULL OR tamano = '');

-- Config por defecto
INSERT IGNORE INTO configuraciones (clave, valor, tipo, descripcion) VALUES
('app_mantenimiento','0','bool','Modo mantenimiento'),
('max_fotos_mascota','3','int','Máximo de fotos por mascota'),
('dias_expiracion_reset','1','int','Días de vigencia del token de reset');

-- Admin inicial (password: AdminRUPE2026!  — cambiar en producción)
-- hash bcrypt cost 12
INSERT IGNORE INTO usuarios (nombre, correo, telefono, password_hash, rol, activo, acepta_privacidad)
VALUES (
  'Administrador RUPE',
  'admin@buscandoahuellitasrupe.com.mx',
  '5550000000',
  '$2y$12$R9o9q7x4SFQJaSUARu3tHuxmD82S7W5TLrIH9/NoJSRU//IGfuej6',
  'administrador',
  1,
  1
);

-- ============================================================
-- DATOS REALES DE PRUEBA (20 mascotas + tutores + albergues + bitácora)
-- Contraseñas demo:
--   Admin:  AdminRUPE2026!
--   Tutores: TutorRUPE2026!
-- ============================================================

-- Tutores (5) — password: TutorRUPE2026!
INSERT IGNORE INTO usuarios (id_usuario, nombre, correo, telefono, password_hash, rol, activo, acepta_privacidad) VALUES
(2, 'María Fernanda López García', 'maria.lopez@email.com', '5512345678', '$2y$12$ETSweL7wGcEWHM1fjKIcF.JLdjT1xB8Wu98c1C3a8qWiQemFlS8Ta', 'tutor', 1, 1),
(3, 'Carlos Alberto Ramírez Sánchez', 'carlos.ramirez@email.com', '5523456789', '$2y$12$ETSweL7wGcEWHM1fjKIcF.JLdjT1xB8Wu98c1C3a8qWiQemFlS8Ta', 'tutor', 1, 1),
(4, 'Ana Patricia Mendoza Ruiz', 'ana.mendoza@email.com', '5534567890', '$2y$12$ETSweL7wGcEWHM1fjKIcF.JLdjT1xB8Wu98c1C3a8qWiQemFlS8Ta', 'tutor', 1, 1),
(5, 'José Luis Hernández Pérez', 'jose.hernandez@email.com', '5545678901', '$2y$12$ETSweL7wGcEWHM1fjKIcF.JLdjT1xB8Wu98c1C3a8qWiQemFlS8Ta', 'tutor', 1, 1),
(6, 'Sofía Isabel Torres Vargas', 'sofia.torres@email.com', '5556789012', '$2y$12$ETSweL7wGcEWHM1fjKIcF.JLdjT1xB8Wu98c1C3a8qWiQemFlS8Ta', 'tutor', 1, 1);

-- Actualizar hash admin a AdminRUPE2026!
UPDATE usuarios SET password_hash = '$2y$12$R9o9q7x4SFQJaSUARu3tHuxmD82S7W5TLrIH9/NoJSRU//IGfuej6'
WHERE correo = 'admin@buscandoahuellitasrupe.com.mx';

-- Preferencias de tutores
INSERT IGNORE INTO preferencias_tutor (id_usuario, notif_email, notif_sms, notif_avistamiento, notif_cambio_estatus, notif_boletin, idioma, id_alcaldia_default) VALUES
(2, 1, 0, 1, 1, 1, 'es', 4),
(3, 1, 1, 1, 1, 0, 'es', 6),
(4, 1, 0, 1, 1, 0, 'es', 9),
(5, 1, 0, 1, 1, 1, 'es', 14),
(6, 1, 0, 1, 1, 0, 'es', 11);

-- Albergues CDMX (6)
INSERT IGNORE INTO albergues (id_albergue, nombre, direccion, id_alcaldia, telefono, correo, latitud, longitud, capacidad, activo) VALUES
(1, 'Albergue Municipal Coyoacán', 'Av. Miguel Ángel de Quevedo 555, Coyoacán', 4, '5556581111', 'albergue.coyoacan@cdmx.gob.mx', 19.3467000, -99.1618000, 80, 1),
(2, 'Centro de Control Canino Iztapalapa', 'Calle Ermita Iztapalapa 1400, Iztapalapa', 9, '5556582222', 'control.iztapalapa@cdmx.gob.mx', 19.3575000, -99.0928000, 120, 1),
(3, 'Refugio Amigos de los Animales', 'Calle Lago Alberto 320, Miguel Hidalgo', 11, '5551234567', 'contacto@amigosanimales.mx', 19.4402000, -99.2001000, 45, 1),
(4, 'Albergue Tlalpan Sur', 'Carretera Picacho-Ajusco km 1.5, Tlalpan', 14, '5556583333', 'albergue.tlalpan@cdmx.gob.mx', 19.2789000, -99.2095000, 60, 1),
(5, 'Patitas Felices A.C.', 'Calle Dr. Lucio 45, Cuauhtémoc', 6, '5559876543', 'info@patitasfelices.org', 19.4285000, -99.1486000, 35, 1),
(6, 'Rescate Animal Xochimilco', 'Calle Nuevo León 210, Xochimilco', 16, '5554567890', 'rescate@xochimilco.mx', 19.2576000, -99.1036000, 50, 1);

-- 20 mascotas reales (folios RUPE)
INSERT IGNORE INTO mascotas (
  id_mascota, folio, id_tutor, nombre, id_raza, especie, sexo, color, edad_aprox,
  senas_particulares, foto_url, id_alcaldia, colonia, direccion_ref, latitud, longitud,
  fecha_extravio, estatus, id_albergue, notas, qr_token, activo
) VALUES
(1,  'RUPE-20260301-A1B2', 2, 'Firulais',  4, 'perro', 'macho',  'Dorado',           '3 años',
 'Collar rojo con placa "Firulais"; mancha blanca en el pecho', NULL, 4, 'Del Carmen',
 'Parque Centenario', 19.3501000, -99.1625000, '2026-03-01', 'En Búsqueda', NULL,
 'Se escapó al abrir la reja de la casa', 'qr_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', 1),

(2,  'RUPE-20260302-C3D4', 2, 'Michi',     10, 'gato',  'hembra', 'Gris atigrado',    '2 años',
 'Oreja izquierda recortada (esterilización); muy dócil', NULL, 4, 'Copilco Universidad',
 'Cerca del Metro Copilco', 19.3358000, -99.1762000, '2026-03-02', 'En Búsqueda', NULL,
 'Salió por la ventana del 2º piso', 'qr_c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8', 1),

(3,  'RUPE-20260303-E5F6', 3, 'Rocky',     5, 'perro', 'macho',  'Negro y café',     '5 años',
 'Cicatriz en pata trasera derecha; collar azul', NULL, 6, 'Roma Norte',
 'Calle Orizaba y Álvaro Obregón', 19.4194000, -99.1620000, '2026-03-03', 'En Resguardo', 5,
 'Rescatado por vecinos y llevado a Patitas Felices', 'qr_e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0', 1),

(4,  'RUPE-20260304-G7H8', 3, 'Luna',      3, 'perro', 'hembra', 'Blanco rizado',    '4 años',
 'Poodle toy; lazo rosa en la cabeza', NULL, 6, 'Condesa',
 'Parque México', 19.4118000, -99.1705000, '2026-03-04', 'Localizado', NULL,
 'Encontrada por un caminante y devuelta al tutor', 'qr_g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2', 1),

(5,  'RUPE-20260305-I9J0', 4, 'Max',       1, 'perro', 'macho',  'Café claro',       '1 año',
 'Mestizo mediano; collar de cuero sin placa', NULL, 9, 'Santa Cruz Meyehualco',
 'Mercado de Santa Cruz', 19.3402000, -99.0489000, '2026-03-05', 'En Búsqueda', NULL,
 'Se soltó de la correa en el mercado', 'qr_i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4', 1),

(6,  'RUPE-20260306-K1L2', 4, 'Nala',      9, 'gato',  'hembra', 'Naranja',          '3 años',
 'Gata mestiza; collar cascabel dorado', NULL, 9, 'Ermita Zaragoza',
 'Av. Ermita Iztapalapa', 19.3580000, -99.0780000, '2026-03-06', 'En Búsqueda', NULL,
 'No regresó después de salir al patio', 'qr_k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6', 1),

(7,  'RUPE-20260307-M3N4', 5, 'Toby',      8, 'perro', 'macho',  'Gris sal y pimienta','6 años',
 'Schnauzer miniatura; barba blanca característica', NULL, 14, 'Pedregal de San Nicolás',
 'Av. de los Insurgentes Sur', 19.2901000, -99.1750000, '2026-03-07', 'En Resguardo', 4,
 'En albergue Tlalpan Sur desde el 8 de marzo', 'qr_m3n4o5p6q7r8s9t0u1v2w3x4y5z6a7b8', 1),

(8,  'RUPE-20260308-O5P6', 5, 'Coco',      2, 'perro', 'hembra', 'Café chocolate',   '8 años',
 'Chihuahua; temblorosa; usa suéter rosa', NULL, 14, 'Fuentes Brotantes',
 'Parque Fuentes Brotantes', 19.2956000, -99.1823000, '2026-03-08', 'En Búsqueda', NULL,
 'Se asustó con fuegos artificiales', 'qr_o5p6q7r8s9t0u1v2w3x4y5z6a7b8c9d0', 1),

(9,  'RUPE-20260309-Q7R8', 6, 'Simba',     11, 'gato',  'macho',  'Crema y blanco',   '4 años',
 'Persa de pelo largo; ojos azules', NULL, 11, 'Polanco',
 'Parque Lincoln', 19.4335000, -99.1912000, '2026-03-09', 'En Búsqueda', NULL,
 'Salió del departamento por la puerta entreabierta', 'qr_q7r8s9t0u1v2w3x4y5z6a7b8c9d0e1f2', 1),

(10, 'RUPE-20260310-S9T0', 6, 'Bella',     4, 'perro', 'hembra', 'Negro',            '2 años',
 'Labrador negra; collar verde con cascabel', NULL, 11, 'Anzures',
 'Campo Marte', 19.4250000, -99.1850000, '2026-03-10', 'Localizado', NULL,
 'Localizada en un estacionamiento cercano', 'qr_s9t0u1v2w3x4y5z6a7b8c9d0e1f2g3h4', 1),

(11, 'RUPE-20260311-U1V2', 2, 'Rex',       5, 'perro', 'macho',  'Negro y fuego',    '7 años',
 'Pastor Alemán; cicatriz en hocico; muy protector', NULL, 3, 'Del Valle Centro',
 'Parque Hundido', 19.3805000, -99.1710000, '2026-03-11', 'En Búsqueda', NULL,
 'Se escapó durante paseo matutino', 'qr_u1v2w3x4y5z6a7b8c9d0e1f2g3h4i5j6', 1),

(12, 'RUPE-20260312-W3X4', 3, 'Pelusa',    12, 'gato',  'hembra', 'Atigrado marrón',  '1 año',
 'Maine Coon joven; cola muy espesa', NULL, 7, 'Lindavista',
 'Plaza Lindavista', 19.4902000, -99.1250000, '2026-03-12', 'En Búsqueda', NULL,
 'Perdida tras mudanza del tutor', 'qr_w3x4y5z6a7b8c9d0e1f2g3h4i5j6k7l8', 1),

(13, 'RUPE-20260313-Y5Z6', 4, 'Chispa',    7, 'perro', 'hembra', 'Tricolor',         '3 años',
 'Beagle; orejas largas; muy olfativa', NULL, 1, 'San Ángel',
 'Plaza San Jacinto', 19.3456000, -99.1890000, '2026-03-13', 'En Resguardo', 1,
 'En Albergue Municipal Coyoacán', 'qr_y5z6a7b8c9d0e1f2g3h4i5j6k7l8m9n0', 1),

(14, 'RUPE-20260314-A7B8', 5, 'Gordo',     6, 'perro', 'macho',  'Blanco y café',    '5 años',
 'Bulldog inglés; respira ruidoso; collar ancho negro', NULL, 15, 'Jardín Balbuena',
 'Parque México (Venustiano Carranza)', 19.4200000, -99.1100000, '2026-03-14', 'En Búsqueda', NULL,
 'Se soltó en zona de feria', 'qr_a7b8c9d0e1f2g3h4i5j6k7l8m9n0o1p2', 1),

(15, 'RUPE-20260315-C9D0', 6, 'Mimi',      10, 'gato',  'hembra', 'Siamesa classic',  '6 años',
 'Puntos oscuros en cara y patas; collar plateado', NULL, 5, 'Contadero',
 'Av. Vasco de Quiroga', 19.3350000, -99.2500000, '2026-03-15', 'En Búsqueda', NULL,
 'No volvió de su salida nocturna habitual', 'qr_c9d0e1f2g3h4i5j6k7l8m9n0o1p2q3r4', 1),

(16, 'RUPE-20260316-E1F2', 2, 'Thor',      1, 'perro', 'macho',  'Negro sólido',     '4 años',
 'Mestizo grande; cicatriz en oreja derecha', NULL, 8, 'Agrícola Oriental',
 'Canal de Tezontle', 19.3950000, -99.0700000, '2026-03-16', 'En Búsqueda', NULL,
 'Se asustó con truenos y corrió', 'qr_e1f2g3h4i5j6k7l8m9n0o1p2q3r4s5t6', 1),

(17, 'RUPE-20260317-G3H4', 3, 'Kiara',     9, 'gato',  'hembra', 'Blanco puro',      '2 años',
 'Ojos verde claro; muy sociable con personas', NULL, 2, 'Clavería',
 'Parque Tezozómoc', 19.4800000, -99.1850000, '2026-03-17', 'Localizado', NULL,
 'Encontrada en azotea de edificio vecino', 'qr_g3h4i5j6k7l8m9n0o1p2q3r4s5t6u7v8', 1),

(18, 'RUPE-20260318-I5J6', 4, 'Bruno',     4, 'perro', 'macho',  'Chocolate',        '9 años',
 'Labrador senior; cojea ligeramente pata trasera izq.', NULL, 13, 'San Lorenzo Tezonco',
 'Av. Tláhuac', 19.3050000, -99.0550000, '2026-03-18', 'En Resguardo', 2,
 'En Centro de Control Canino Iztapalapa', 'qr_i5j6k7l8m9n0o1p2q3r4s5t6u7v8w9x0', 1),

(19, 'RUPE-20260319-K7L8', 5, 'Canela',    3, 'perro', 'hembra', 'Apricot',          '1 año',
 'Poodle mini; recién cortada; lazo café', NULL, 12, 'Villa Milpa Alta',
 'Plaza principal', 19.1920000, -99.0230000, '2026-03-19', 'En Búsqueda', NULL,
 'Se perdió en fiesta del pueblo', 'qr_k7l8m9n0o1p2q3r4s5t6u7v8w9x0y1z2', 1),

(20, 'RUPE-20260320-M9N0', 6, 'Shadow',    1, 'perro', 'macho',  'Gris oscuro',      '3 años',
 'Mestizo mediano; mancha blanca en forma de estrella en pecho', NULL, 10, 'San Jerónimo Lídice',
 'Parque Ecológico de la Ciudad de México', 19.3200000, -99.2100000, '2026-03-20', 'En Búsqueda', NULL,
 'Escapó durante caminata en el parque', 'qr_m9n0o1p2q3r4s5t6u7v8w9x0y1z2a3b4', 1);

-- Seguimientos / bitácora (varios por mascota)
INSERT IGNORE INTO seguimientos (id_mascota, id_usuario, folio, condicion, foto_url, referencia, contacto, telefono, correo, latitud, longitud, created_at) VALUES
(1, 2, 'RUPE-20260301-A1B2', 'Observado', NULL, 'Parque de los Venados', 'Vecino', NULL, NULL, 19.3480000, -99.1550000, '2026-03-02 19:20:00'),
(5, 4, 'RUPE-20260305-I9J0', 'Observado', NULL, 'Metro Santa Martha', 'Ciudadano', NULL, NULL, 19.3350000, -99.0400000, '2026-03-06 13:15:00'),
(9, 6, 'RUPE-20260309-Q7R8', 'Observado', NULL, 'Calle Homero, Polanco', 'Ciudadano', NULL, NULL, 19.4340000, -99.1920000, '2026-03-10 08:00:00'),
(20, 6, 'RUPE-20260320-M9N0', 'Observado', NULL, 'Parque ecológico', 'Ciudadano', NULL, NULL, 19.3180000, -99.2080000, '2026-03-20 16:30:00');

-- FAQs ayuda
INSERT IGNORE INTO ayuda_faqs (categoria, pregunta, respuesta, orden, activo, rol_visible) VALUES
('general', '¿Qué es RUPE CDMX?', 'RUPE CDMX (Registro Único de Mascotas Extraviadas) es una plataforma gratuita del programa Buscando a Huellitas para registrar y dar seguimiento a mascotas perdidas en la Ciudad de México.', 1, 1, 'todos'),
('general', '¿Cuánto cuesta registrar a mi mascota?', 'El registro, la ficha PDF con código QR, los avistamientos y el seguimiento son completamente gratuitos.', 2, 1, 'todos'),
('registro', '¿Qué datos necesito para registrar un reporte?', 'Nombre de la mascota, especie, sexo, color, fecha y lugar aproximado del extravío, alcaldía y datos de contacto del tutor. La foto es recomendable pero no obligatoria.', 3, 1, 'tutor'),
('registro', '¿Cómo obtengo el folio y el código QR?', 'Al guardar el reporte el sistema genera automáticamente un folio único (RUPE-AAAAMMDD-XXXX) y un token QR. Puedes descargar la ficha PDF desde el panel.', 4, 1, 'tutor'),
('seguimiento', '¿Qué hago si alguien encuentra a mi mascota?', 'La persona puede escanear el QR de la ficha o reportar un avistamiento. Tú recibirás notificación (si activaste alertas) y podrás actualizar el estatus a Localizado.', 5, 1, 'tutor'),
('seguimiento', '¿Qué significan los estatus?', 'En Búsqueda: aún no localizada. En Resguardo: está en un albergue o centro de control. Localizado: ya fue devuelta o confirmada con el tutor.', 6, 1, 'todos'),
('cuenta', '¿Olvidé mi contraseña?', 'Usa la opción «Recuperar contraseña» en el inicio de sesión. Recibirás un enlace al correo registrado (válido por tiempo limitado).', 7, 1, 'todos'),
('admin', '¿Cómo gestiono usuarios y catálogos?', 'Desde el panel de administrador puedes activar/desactivar usuarios, mantener alcaldías, razas, FAQs, ver reportes globales y la bitácora de auditoría.', 8, 1, 'administrador');

-- Auditoría de ejemplo
INSERT IGNORE INTO auditoria (id_usuario, accion, tabla, id_registro, detalle, ip, user_agent, created_at) VALUES
(1, 'login', 'usuarios', 1, '{"msg":"Acceso administrador"}', '187.190.1.10', 'Mozilla/5.0', '2026-03-01 08:00:00'),
(2, 'mascota_crear', 'mascotas', 1, '{"folio":"RUPE-20260301-A1B2"}', '201.145.20.5', 'Mozilla/5.0', '2026-03-01 10:15:00'),
(3, 'mascota_crear', 'mascotas', 3, '{"folio":"RUPE-20260303-E5F6"}', '189.203.10.22', 'Mozilla/5.0', '2026-03-03 11:30:00'),
(3, 'mascota_estatus', 'mascotas', 3, '{"estatus":"En Resguardo"}', '189.203.10.22', 'Mozilla/5.0', '2026-03-04 14:00:00'),
(4, 'mascota_crear', 'mascotas', 5, '{"folio":"RUPE-20260305-I9J0"}', '200.68.140.3', 'Mozilla/5.0', '2026-03-05 15:00:00'),
(5, 'mascota_crear', 'mascotas', 7, '{"folio":"RUPE-20260307-M3N4"}', '187.141.55.8', 'Mozilla/5.0', '2026-03-07 10:00:00'),
(6, 'mascota_crear', 'mascotas', 9, '{"folio":"RUPE-20260309-Q7R8"}', '201.175.33.90', 'Mozilla/5.0', '2026-03-09 11:00:00'),
(1, 'config_update', 'configuraciones', NULL, '{"clave":"max_fotos_mascota"}', '187.190.1.10', 'Mozilla/5.0', '2026-03-10 09:00:00');



-- Reparar datos demo si se insertaron sin color/señas
UPDATE mascotas SET
  color = COALESCE(NULLIF(color, ''), 'Sin color registrado'),
  senas_particulares = COALESCE(NULLIF(senas_particulares, ''), 'Sin señas registradas')
WHERE activo = 1 AND (color IS NULL OR color = '' OR senas_particulares IS NULL OR senas_particulares = '');


-- Migración: collar, microchip, tamaño (si la tabla ya existía)
-- Ejecutar en phpMyAdmin si las columnas no existen:
-- ALTER TABLE mascotas ADD COLUMN collar VARCHAR(120) NULL AFTER senas_particulares;
-- ALTER TABLE mascotas ADD COLUMN microchip VARCHAR(120) NULL AFTER collar;
-- ALTER TABLE mascotas ADD COLUMN tamano VARCHAR(40) NULL AFTER microchip;
