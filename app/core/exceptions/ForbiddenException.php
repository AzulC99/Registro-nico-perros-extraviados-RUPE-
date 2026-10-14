<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;

final class ForbiddenException extends Exception
{
    public function __construct(string $message = 'Acceso denegado')
    {
        parent::__construct($message, 403);
    }
}
