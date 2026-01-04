<?php

namespace App\Exceptions;

use Exception;

class SeriesNotFoundException extends Exception
{
      public function __construct(
        string $message = 'Series not found',
        int $code = 502
    ) {
        parent::__construct($message, $code);
    }
}
