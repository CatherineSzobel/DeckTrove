<?php

namespace App\Exceptions;

use RuntimeException;

class ScryfallUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'The Magic card database (Scryfall) is unavailable right now. Please try again in a moment.')
    {
        parent::__construct($message);
    }
}
