<?php

namespace RelayerCore\LaravelInstaller;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use RelayerCore\LaravelInstaller\Contracts\EnvironmentWriter;
use RelayerCore\LaravelInstaller\Contracts\InstallationStateManager;
use RelayerCore\LaravelInstaller\Contracts\InstallerStep;
use RelayerCore\LaravelInstaller\Http\Livewire\Installer;
use RelayerCore\LaravelInstaller\Services\DotEnvWriter;
use RelayerCore\LaravelInstaller\Services\FileInstallationStateManager;
use RelayerCore\LaravelInstaller\Services\StepManager;

/**
 * Registers all installer services, middleware, routes, views, and translations.
 *
 * This provider is auto-discovered via composer.json's extra.laravel.providers.
 * It bootstraps the installer wizard for any Laravel application, reading the
 * ordered step pipeline from config('installer.steps') so that host apps can
 * add, remove, or reorder steps without modifying this package.
 */
class InstallerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/installer.php', 'installer');

        // Register core services
        $this->app->bind(EnvironmentWriter::class, DotEnvWriter::class);

        // Bind default InstallationStateManager (can be overridden by host app)
        $this->app->bindIf(InstallationStateManager::class, FileInstallationStateManager::class);

        // StepManager is a singleton so every consumer sees the same registered steps
        $this->app->singleton(StepManager::class);

        // Early installation check — runs before other service providers boot
        $this->earlyInstallationCheck();
    }

    /**
     * Bind the dynamic installation check.
     * This defers execution so the host application can override the InstallationStateManager
     * before the installation status is evaluated.
     */
    protected function earlyInstallationCheck(): void
    {
        $this->app->bind('installer.is_installed', function ($app) {
            return $app->make(InstallationStateManager::class)->isInstalled();
        });
    }

    public function boot(): void
    {
        // Force Debug Mode if not installed.
        // Doing this in boot() ensures that the host app's AppServiceProvider has had
        // a chance to override the InstallationStateManager.
        if (!$this->app->make('installer.is_installed')) {
            config(['app.debug' => true]);
        }

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/installer.php' => config_path('installer.php'),
        ], 'installer-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/installer'),
        ], 'installer-views');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/installer'),
        ], 'installer-lang');

        // Publish CSS assets
        $this->publishes([
            __DIR__ . '/../resources/css/installer.css' => public_path('vendor/installer/installer.css'),
        ], 'installer-assets');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'installer');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'installer');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/installer.php');

        // Register Livewire component
        Livewire::component('installer', Installer::class);

        // Boot the step pipeline from config
        $this->bootSteps();

        // Register middleware
        $this->registerMiddleware();

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \RelayerCore\LaravelInstaller\Console\Commands\MakeInstallerStepCommand::class,
            ]);
        }
    }

    /**
     * Resolve each step class from config and register it with the StepManager.
     *
     * Steps are resolved through the container so they benefit from
     * automatic dependency injection (e.g. EnvironmentWriter in ConfigureEnvironment).
     */
    protected function bootSteps(): void
    {
        $stepManager = $this->app->make(StepManager::class);

        foreach (config('installer.steps', []) as $stepClass) {
            if (!is_string($stepClass) || !class_exists($stepClass)) {
                throw new \RuntimeException("Installer step [{$stepClass}] does not exist.");
            }

            $step = $this->app->make($stepClass);

            if (!$step instanceof InstallerStep) {
                throw new \RuntimeException("Installer step [{$stepClass}] must implement " . InstallerStep::class);
            }

            $stepManager->register($step);
        }
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
