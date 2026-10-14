<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;

final class ValidationException extends Exception
{
    private array $errors;

    public function __construct(string $message = 'Datos inválidos', array $errors = [])
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
