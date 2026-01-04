<?php

namespace App\Exceptions;

use Exception;

class ResourceNotFoundException extends Exception
{
   
    public function __construct(
        string $message = 'Resource not found',
        int $code = 502
    ) {
        parent::__construct($message, $code);
    }
}
