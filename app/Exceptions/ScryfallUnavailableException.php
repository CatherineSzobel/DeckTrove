<?php

namespace App\Exceptions;

use Exception;

class ScryfallUnavailableException extends Exception
{
    public function __construct(
        string $message = 'Scryfall API unavailable',
        int $code = 502
    ) {
        parent::__construct($message, $code);
    }
}
