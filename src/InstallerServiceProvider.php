<?php

namespace RelayerCore\LaravelInstaller;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class InstallerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/installer.php', 'installer');

        // Register core services
        $this->app->bind(
            \RelayerCore\LaravelInstaller\Contracts\EnvironmentWriter::class,
            \RelayerCore\LaravelInstaller\Services\DotEnvWriter::class
        );

        // Early installation check - runs before other service providers boot
        $this->earlyInstallationCheck();
    }

    /**
     * Check installation status very early and set a flag.
     * This prevents database errors in other service providers.
     */
    protected function earlyInstallationCheck(): void
    {
        $installedFile = config('installer.installed_file') ?? storage_path('installed');
        $isInstalled = file_exists($installedFile);
        
        // Store installation status in app container for other providers to check
        $this->app->instance('installer.is_installed', $isInstalled);

        // Force Debug Mode if not installed
        // This ensures Livewire assets (non-minified) load correctly 
        // even if the user's default config has debug=false.
        if (!$isInstalled) {
            config(['app.debug' => true]);
        }
    }

    public function boot(): void
    {
        // Only load routes and views during boot
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

        // Register Livewire component
        \Livewire\Livewire::component('installer', \RelayerCore\LaravelInstaller\Http\Livewire\Installer::class);

        // Register middleware
        $this->registerMiddleware();
    }

    protected function registerMiddleware(): void
    {
        // Add to global middleware stack
        // This ensures the check runs for all requests (web, api, etc.)
        // preventing application errors if the app isn't installed yet.
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(Middleware\CheckInstallation::class);
    }
}
