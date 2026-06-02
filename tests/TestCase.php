<?php

namespace SameOldNick\OAuth\Tests;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Router;
use Orchestra\Testbench\Concerns\WithLaravelMigrations;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;
    use WithLaravelMigrations;
    use WithWorkbench;

    /**
     * Automatically enables package discoveries.
     *
     * @var bool
     */
    protected $enablesPackageDiscoveries = true;

    public function getEnvironmentSetUp($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('database.default', 'testing');

            $config->set('oauth', require __DIR__.'/../config/oauth.php');
        });
    }

    /**
     * Define routes setup.
     *
     * @param  Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        require __DIR__.'/../routes/oauth.php';
    }
}
