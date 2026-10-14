-- RUPE: columnas zona y cp_base en alcaldias (idempotente en MySQL 8+)
ALTER TABLE alcaldias ADD COLUMN IF NOT EXISTS zona VARCHAR(40) NULL AFTER clave;
ALTER TABLE alcaldias ADD COLUMN IF NOT EXISTS cp_base VARCHAR(10) NULL AFTER zona;
