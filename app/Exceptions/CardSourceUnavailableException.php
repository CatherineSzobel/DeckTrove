<?php

namespace App\Exceptions;

use RuntimeException;

/** A series' card source (an outside API) can't be reached; each series' provider throws its own subclass. */
class CardSourceUnavailableException extends RuntimeException {}
