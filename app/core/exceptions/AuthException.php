<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

use Exception;

final class AuthException extends Exception
{
    public function __construct(string $message = 'No autenticado')
    {
        parent::__construct($message, 401);
    }
}
