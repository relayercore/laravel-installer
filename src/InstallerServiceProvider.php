<?php

namespace RelayerCore\LaravelInstaller;

use Illuminate\Support\ServiceProvider;

class InstallerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/installer.php', 'installer');
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/installer.php' => config_path('installer.php'),
        ], 'installer-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/installer'),
        ], 'installer-views');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'installer');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/installer.php');

        // Register middleware
        $this->registerMiddleware();
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', \RelayerCore\LaravelInstaller\Middleware\CheckInstallation::class);
    }
}
