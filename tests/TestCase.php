<?php

namespace Asciito\Browser\Tests;

use Asciito\Browser\BrowserServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            BrowserServiceProvider::class,
        ];
    }
}
