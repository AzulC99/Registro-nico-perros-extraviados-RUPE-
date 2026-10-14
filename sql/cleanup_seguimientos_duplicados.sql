-- Eliminar avistamientos duplicados (mismo mascota + GPS cercano en la misma hora)
-- Revisar antes de ejecutar en producción

DELETE s1 FROM seguimientos s1
INNER JOIN seguimientos s2
  ON s1.id_mascota = s2.id_mascota
 AND s1.id_seguimiento > s2.id_seguimiento
 AND ABS(COALESCE(s1.latitud,0) - COALESCE(s2.latitud,0)) < 0.0002
 AND ABS(COALESCE(s1.longitud,0) - COALESCE(s2.longitud,0)) < 0.0002
 AND TIMESTAMPDIFF(MINUTE, s2.created_at, s1.created_at) BETWEEN 0 AND 15;
