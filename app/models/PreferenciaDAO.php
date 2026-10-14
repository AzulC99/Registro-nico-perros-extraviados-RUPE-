<?php
declare(strict_types=1);

namespace App\Models;

final class PreferenciaDAO extends BaseDAO
{
    protected string $table = 'preferencias_tutor';
    protected string $primaryKey = 'id_preferencia';

    public function findByUsuario(int $idUsuario): ?array
    {
        $row = $this->query(
            'SELECT * FROM preferencias_tutor WHERE id_usuario = :id_usuario LIMIT 1',
            ['id_usuario' => $idUsuario]
        )->fetch();
        return $row ?: null;
    }

    public function upsert(int $idUsuario, array $data): void
    {
        $existing = $this->findByUsuario($idUsuario);
        $payload = [
            'notif_email'          => (int) ($data['notif_email'] ?? 1),
            'notif_sms'            => (int) ($data['notif_sms'] ?? 0),
            'notif_avistamiento'   => (int) ($data['notif_avistamiento'] ?? 1),
            'notif_cambio_estatus' => (int) ($data['notif_cambio_estatus'] ?? 1),
            'notif_boletin'        => (int) ($data['notif_boletin'] ?? 0),
            'idioma'               => $data['idioma'] ?? 'es',
            'id_alcaldia_default'  => $data['id_alcaldia_default'] ?? null,
        ];

        if ($existing) {
            $this->updateById((int) $existing['id_preferencia'], $payload);
        } else {
            $payload['id_usuario'] = $idUsuario;
            $this->insert($payload);
        }
    }
}
