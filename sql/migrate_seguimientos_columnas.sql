-- RUPE CDMX: reestructurar tabla seguimientos
-- Ejecutar en phpMyAdmin UNA vez. Si una línea falla porque la columna ya existe, continuar con la siguiente.

-- A) Agregar columnas nuevas
ALTER TABLE seguimientos ADD COLUMN folio VARCHAR(40) NULL;
ALTER TABLE seguimientos ADD COLUMN condicion VARCHAR(80) NOT NULL DEFAULT 'Observado';
ALTER TABLE seguimientos ADD COLUMN referencia VARCHAR(120) NULL;
ALTER TABLE seguimientos ADD COLUMN contacto VARCHAR(80) NULL;
ALTER TABLE seguimientos ADD COLUMN telefono VARCHAR(20) NULL;
ALTER TABLE seguimientos ADD COLUMN correo VARCHAR(100) NULL;

-- B) Si aún existen tipo/descripcion, copiar lo posible a condicion
-- UPDATE seguimientos SET condicion = LEFT(descripcion, 80) WHERE (condicion IS NULL OR condicion = 'Observado') AND descripcion IS NOT NULL;

-- C) Eliminar columnas antiguas (ejecutar solo si existen)
ALTER TABLE seguimientos DROP COLUMN tipo;
ALTER TABLE seguimientos DROP COLUMN descripcion;

-- Estructura final esperada:
-- id_seguimiento, id_mascota, id_usuario, folio, condicion, foto_url,
-- referencia, contacto, telefono, correo, latitud, longitud, created_at
