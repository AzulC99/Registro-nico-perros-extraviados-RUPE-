<?php
/**
 * RUPE CDMX — Validador (sin flash, errores en array)
 * app/helpers/Validator.php
 */

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Exceptions\ValidationException;

final class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    public function required(string ...$fields): self
    {
        foreach ($fields as $field) {
            $val = $this->data[$field] ?? null;
            if ($val === null || $val === '' || (is_array($val) && count($val) === 0)) {
                $this->errors[$field][] = 'El campo es obligatorio.';
            }
        }
        return $this;
    }

    public function email(string $field): self
    {
        $val = $this->data[$field] ?? null;
        if ($val !== null && $val !== '' && !is_valid_email((string) $val)) {
            $this->errors[$field][] = 'Correo electrónico no válido.';
        }
        return $this;
    }

    public function min(string $field, int $min): self
    {
        $val = $this->data[$field] ?? null;
        if ($val !== null && $val !== '' && mb_strlen((string) $val) < $min) {
            $this->errors[$field][] = "Mínimo {$min} caracteres.";
        }
        return $this;
    }

    public function max(string $field, int $max): self
    {
        $val = $this->data[$field] ?? null;
        if ($val !== null && $val !== '' && mb_strlen((string) $val) > $max) {
            $this->errors[$field][] = "Máximo {$max} caracteres.";
        }
        return $this;
    }

    public function in(string $field, array $allowed): self
    {
        $val = $this->data[$field] ?? null;
        if ($val !== null && $val !== '' && !in_array($val, $allowed, true)) {
            $this->errors[$field][] = 'Valor no permitido.';
        }
        return $this;
    }

    public function numeric(string $field): self
    {
        $val = $this->data[$field] ?? null;
        if ($val !== null && $val !== '' && !is_numeric($val)) {
            $this->errors[$field][] = 'Debe ser numérico.';
        }
        return $this;
    }

    public function phone(string $field): self
    {
        $val = normalize_phone((string) ($this->data[$field] ?? ''));
        if ($val !== '' && !preg_match('/^\d{10}$/', $val)) {
            $this->errors[$field][] = 'Teléfono debe tener 10 dígitos.';
        }
        return $this;
    }

    public function password(string $field, int $min = 8, ?int $max = 8): self
    {
        $val = (string) ($this->data[$field] ?? '');
        if ($val === '') {
            return $this;
        }
        $len = mb_strlen($val);
        // Límite exacto: 8 caracteres (mínimo y máximo)
        if ($max === null) {
            $max = $min;
        }
        if ($len < $min || $len > $max) {
            if ($min === $max) {
                $this->errors[$field][] = "La contraseña debe tener exactamente {$min} caracteres.";
            } else {
                $this->errors[$field][] = "La contraseña debe tener entre {$min} y {$max} caracteres.";
            }
        }
        if (!preg_match('/[A-Z]/', $val)) {
            $this->errors[$field][] = 'La contraseña debe incluir al menos una letra mayúscula.';
        }
        if (!preg_match('/[a-z]/', $val)) {
            $this->errors[$field][] = 'La contraseña debe incluir al menos una letra minúscula.';
        }
        if (!preg_match('/\d/', $val)) {
            $this->errors[$field][] = 'La contraseña debe incluir al menos un número.';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $val)) {
            $this->errors[$field][] = 'La contraseña debe incluir al menos un carácter especial (ej. ! @ # $ %).';
        }
        return $this;
    }

    public function confirmed(string $field): self
    {
        $val  = $this->data[$field] ?? null;
        $conf = $this->data[$field . '_confirmation'] ?? null;
        if ($val !== $conf) {
            $this->errors[$field][] = 'La confirmación no coincide.';
        }
        return $this;
    }

    public function fails(): bool
    {
        return count($this->errors) > 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Lanza ValidationException si hay errores
     */
    public function validate(): array
    {
        if ($this->fails()) {
            throw new ValidationException('Datos inválidos', $this->errors);
        }
        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
