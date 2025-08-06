<?php

namespace smukhidev\ShurjopayLaravelPackage;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ShurjopayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/shurjopay.php', 'shurjopay');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Register routes
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/shurjopay.php' => config_path('shurjopay.php'),
        ], 'config');
    }
}
