-- Quitar razas duplicadas (mismo nombre+especie), conservar el id menor
-- y reasignar mascotas al id conservado.

-- 1) Asegurar columna tamano
ALTER TABLE razas ADD COLUMN IF NOT EXISTS tamano VARCHAR(20) NULL AFTER especie;

-- 2) Ejemplo de limpieza (ajustar según motor):
-- UPDATE mascotas m
-- INNER JOIN (
--   SELECT nombre, especie, MIN(id_raza) AS keep_id
--   FROM razas GROUP BY nombre, especie
-- ) k ON 1=1
-- INNER JOIN razas r ON r.nombre = k.nombre AND r.especie = k.especie AND r.id_raza <> k.keep_id
-- SET m.id_raza = k.keep_id
-- WHERE m.id_raza = r.id_raza;
