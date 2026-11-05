<?php

namespace App\Exceptions;

class ScryfallUnavailableException extends CardSourceUnavailableException
{
    public function __construct(string $message = 'The Magic card database (Scryfall) is unavailable right now. Please try again in a moment.')
    {
        parent::__construct($message);
    }
}
