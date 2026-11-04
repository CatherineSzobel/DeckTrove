<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Sleep;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // The Scryfall limiter waits between calls; tests shouldn't really sleep.
        Sleep::fake();
    }
}
