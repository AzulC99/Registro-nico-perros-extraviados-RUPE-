-- Corregir especie felina y tamaños en catálogo razas
ALTER TABLE razas ADD COLUMN IF NOT EXISTS tamano VARCHAR(20) NULL;

UPDATE razas SET especie = 'gato' WHERE nombre IN (
  'Siamés','Persa','Maine Coon','Bengalí','Ragdoll',
  'British Shorthair','Sphynx','Abisinio','Scottish Fold'
) AND especie <> 'gato';

UPDATE razas SET tamano = 'Miniatura' WHERE nombre IN ('Chihuahua','French Poodle Toy','Yorkshire Terrier','Maltese') AND (tamano IS NULL OR tamano = '');
UPDATE razas SET tamano = 'Pequeño' WHERE nombre IN ('Pug','Poodle / Caniche','Bulldog Francés','Shih Tzu','Siamés','Persa','Abisinio','Scottish Fold') AND (tamano IS NULL OR tamano = '');
UPDATE razas SET tamano = 'Mediano' WHERE nombre IN ('Beagle','Cocker Spaniel','Schnauzer','Border Collie','Mestizo','Bengalí','British Shorthair','Sphynx') AND (tamano IS NULL OR tamano = '');
UPDATE razas SET tamano = 'Grande' WHERE nombre IN ('Labrador Retriever','Pastor Alemán','Golden Retriever','Husky Siberiano','Boxer','Pitbull / American Stafford','Dálmata','Rottweiler','Doberman','Akita','Maine Coon','Ragdoll') AND (tamano IS NULL OR tamano = '');
UPDATE razas SET tamano = 'Gigante' WHERE nombre IN ('San Bernardo') AND (tamano IS NULL OR tamano = '');
