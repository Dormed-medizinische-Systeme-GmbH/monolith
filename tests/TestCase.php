<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Assets are built out of band (no bundler in the test image); feature
        // tests assert on rendered markup, not on the Vite manifest.
        $this->withoutVite();
    }
}
