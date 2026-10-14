<?php
declare(strict_types=1);

namespace App\Models;

final class PasswordResetDAO extends BaseDAO
{
    protected string $table = 'password_resets';
    protected string $primaryKey = 'id_reset';

    private function ensureTable(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        try {
            $this->db->exec(
                "CREATE TABLE IF NOT EXISTS password_resets (
                  id_reset     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  id_usuario   INT UNSIGNED NOT NULL,
                  token_hash   VARCHAR(64) NOT NULL,
                  expires_at   DATETIME NOT NULL,
                  used_at      DATETIME NULL,
                  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  KEY idx_pr_token (token_hash),
                  KEY idx_pr_usuario (id_usuario)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (\Throwable $e) {
            // tabla ya existe o sin permisos CREATE
        }
    }

    public function createToken(int $userId, string $tokenPlain, int $secondsValid = 604800): int
    {
        $this->ensureTable();
        $userId = (int) $userId;
        $tokenPlain = trim($tokenPlain);
        if ($userId <= 0 || strlen($tokenPlain) < 32) {
            return 0;
        }

        $hash = hash('sha256', $tokenPlain);
        $secondsValid = max(3600, (int) $secondsValid);
        $expires = date('Y-m-d H:i:s', time() + $secondsValid);

        try {
            $this->query(
                'UPDATE password_resets SET used_at = COALESCE(used_at, NOW())
                 WHERE id_usuario = :id_usuario AND used_at IS NULL',
                ['id_usuario' => $userId]
            );
        } catch (\Throwable $e) {
        }

        try {
            $this->query(
                'INSERT INTO password_resets (id_usuario, token_hash, expires_at, used_at)
                 VALUES (:id_usuario, :token_hash, :expires_at, NULL)',
                [
                    'id_usuario' => $userId,
                    'token_hash' => $hash,
                    'expires_at' => $expires,
                ]
            );
            $id = (int) $this->db->lastInsertId();
            if ($id > 0) {
                return $id;
            }
        } catch (\Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'password_resets createToken', ['msg' => $e->getMessage()]);
            }
        }

        // Verificar si el hash quedó guardado
        try {
            $row = $this->query(
                'SELECT id_reset FROM password_resets
                 WHERE token_hash = :h AND used_at IS NULL
                 ORDER BY id_reset DESC LIMIT 1',
                ['h' => $hash]
            )->fetch();
            if ($row) {
                return (int) ($row['id_reset'] ?? 0);
            }
        } catch (\Throwable $e) {
        }

        return 0;
    }

    public function findValid(string $tokenPlain): ?array
    {
        $this->ensureTable();
        $tokenPlain = trim(preg_replace('/\s+/', '', $tokenPlain) ?? '');
        if ($tokenPlain === '' || strlen($tokenPlain) < 32) {
            return null;
        }
        $hash = hash('sha256', $tokenPlain);

        // 1) Con JOIN usuarios
        try {
            $row = $this->query(
                'SELECT pr.*, u.correo, u.nombre
                 FROM password_resets pr
                 INNER JOIN usuarios u ON u.id_usuario = pr.id_usuario
                 WHERE pr.token_hash = :token_hash AND pr.used_at IS NULL
                 ORDER BY pr.id_reset DESC LIMIT 1',
                ['token_hash' => $hash]
            )->fetch();
            if ($row) {
                return $row;
            }
        } catch (\Throwable $e) {
        }

        // 2) Sin JOIN
        try {
            $row = $this->query(
                'SELECT * FROM password_resets
                 WHERE token_hash = :token_hash AND used_at IS NULL
                 ORDER BY id_reset DESC LIMIT 1',
                ['token_hash' => $hash]
            )->fetch();
            if ($row) {
                $uid = (int) ($row['id_usuario'] ?? 0);
                if ($uid > 0) {
                    try {
                        $u = $this->query(
                            'SELECT correo, nombre FROM usuarios WHERE id_usuario = :id LIMIT 1',
                            ['id' => $uid]
                        )->fetch();
                        if ($u) {
                            $row['correo'] = $u['correo'] ?? '';
                            $row['nombre'] = $u['nombre'] ?? '';
                        }
                    } catch (\Throwable $e2) {
                    }
                }
                return $row;
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    public function findAny(string $tokenPlain): ?array
    {
        $this->ensureTable();
        $tokenPlain = trim(preg_replace('/\s+/', '', $tokenPlain) ?? '');
        if ($tokenPlain === '' || strlen($tokenPlain) < 32) {
            return null;
        }
        $hash = hash('sha256', $tokenPlain);
        try {
            $row = $this->query(
                'SELECT pr.*, u.correo, u.nombre,
                        CASE
                          WHEN pr.used_at IS NOT NULL THEN \'used\'
                          ELSE \'valid\'
                        END AS token_status
                 FROM password_resets pr
                 LEFT JOIN usuarios u ON u.id_usuario = pr.id_usuario
                 WHERE pr.token_hash = :token_hash
                 ORDER BY pr.id_reset DESC LIMIT 1',
                ['token_hash' => $hash]
            )->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function markUsed(int $idReset): void
    {
        $idReset = (int) $idReset;
        if ($idReset <= 0) {
            return;
        }
        try {
            $this->query(
                'UPDATE password_resets SET used_at = NOW() WHERE id_reset = :id',
                ['id' => $idReset]
            );
        } catch (\Throwable $e) {
        }
    }
}
