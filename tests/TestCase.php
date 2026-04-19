<?php

declare(strict_types=1);

namespace RakeshRai\LaravelNepaliDate\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RakeshRai\LaravelNepaliDate\LaravelNepaliDateServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelNepaliDateServiceProvider::class,
        ];
    }
}
