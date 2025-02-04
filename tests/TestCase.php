<?php

namespace Opscale\NovaAuthorization\Tests;

use Illuminate\Support\Facades\Route;
use Opscale\NovaAuthorization\ToolServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();

        Route::middlewareGroup('nova', []);
    }

    protected function getPackageProviders($app)
    {
        return [
            ToolServiceProvider::class,
        ];
    }
}
