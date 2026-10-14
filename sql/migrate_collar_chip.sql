-- RUPE CDMX: columnas collar, microchip, tamano en mascotas
-- Ejecutar en phpMyAdmin UNA VEZ. Si una columna ya existe, omitir esa línea.

ALTER TABLE mascotas ADD COLUMN collar VARCHAR(120) NOT NULL DEFAULT '' AFTER senas_particulares;
ALTER TABLE mascotas ADD COLUMN microchip VARCHAR(120) NOT NULL DEFAULT '' AFTER collar;
ALTER TABLE mascotas ADD COLUMN tamano VARCHAR(40) NOT NULL DEFAULT '' AFTER microchip;

-- Rellenar desde senas_particulares (registros viejos)
UPDATE mascotas
SET tamano = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(senas_particulares, 'Tamaño:', -1), '|', 1))
WHERE (tamano IS NULL OR tamano = '')
  AND senas_particulares LIKE '%Tamaño:%';

UPDATE mascotas
SET collar = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(senas_particulares, 'Collar:', -1), '|', 1))
WHERE (collar IS NULL OR collar = '')
  AND senas_particulares LIKE '%Collar:%';

UPDATE mascotas
SET microchip = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(senas_particulares, 'Chip/tatuaje:', -1), '|', 1))
WHERE (microchip IS NULL OR microchip = '')
  AND senas_particulares LIKE '%Chip/tatuaje:%';

-- Quitar NULL restantes
UPDATE mascotas SET collar = '' WHERE collar IS NULL;
UPDATE mascotas SET microchip = '' WHERE microchip IS NULL;
UPDATE mascotas SET tamano = '' WHERE tamano IS NULL;
UPDATE mascotas SET color = '' WHERE color IS NULL;
UPDATE mascotas SET edad_aprox = '' WHERE edad_aprox IS NULL;
UPDATE mascotas SET senas_particulares = '' WHERE senas_particulares IS NULL;
UPDATE mascotas SET colonia = '' WHERE colonia IS NULL;
