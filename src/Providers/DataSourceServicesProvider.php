<?php

declare(strict_types=1);

namespace Awtechs\Datasource\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the Awtechs Datasource package.
 */
class DatasourceServiceProvider extends ServiceProvider
{
    /**
     * Register services and merge configuration.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/datasource.php',
            'datasource'
        );
    }

    /**
     * Bootstrap services and publish configuration.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/datasource.php' => config_path('datasource.php'),
        ], 'config');
    }
}