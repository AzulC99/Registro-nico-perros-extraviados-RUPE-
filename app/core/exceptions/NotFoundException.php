<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;

final class NotFoundException extends Exception
{
    public function __construct(string $message = 'Recurso no encontrado')
    {
        parent::__construct($message, 404);
    }
}
