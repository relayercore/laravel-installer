<?php

declare(strict_types=1);

namespace RelayerCore\LaravelInstaller\Steps;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

class RunMigrations implements InstallerStep
{
    public function id(): string
    {
        return 'migrations';
    }

    public function label(): string
    {
        return 'Migrate Database';
    }

    public function view(): string
    {
        return 'installer::steps.migrations';
    }

    public function isSkipped(): bool
    {
        return false;
    }

    public function validate(array $data = []): bool
    {
        return true;
    }

    public function process(array $data = []): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(300);

        try {
            Artisan::call('migrate', ['--force' => true]);

            if (!empty($data['load_demo_data'])) {
                $seederClass = config('installer.seeder', 'DatabaseSeeder');

                if (!empty($data['vertical']) && $data['vertical'] !== 'universal') {
                    $manifestPath = base_path("verticals/{$data['vertical']}/module.json");
                    if (file_exists($manifestPath)) {
                        $manifest = json_decode(file_get_contents($manifestPath), true);
                        if (!empty($manifest['seeder'])) {
                            $seederClass = $manifest['seeder'];
                        }
                    }
                }

                Artisan::call('db:seed', ['--class' => $seederClass, '--force' => true]);
            }
        } catch (\Exception $e) {
            Artisan::call('db:wipe', ['--force' => true]);

            Log::error('Installer migration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception($this->buildErrorMessage($e));
        }
    }

    private function buildErrorMessage(\Exception $e): string
    {
        $message = 'Database setup failed. We have reverted all changes to leave a clean state.';

        $error = $e->getMessage();

        if (str_contains($error, 'Access denied')) {
            $message .= ' The database credentials in the previous step appear to be incorrect.';
        } elseif (str_contains($error, 'Base table or view already exists')) {
            $message .= ' A table already exists. Please drop the database and start fresh.';
        } elseif (str_contains($error, 'SQLSTATE[42S01]')) {
            $message .= ' A table already exists. Please drop the database and start fresh.';
        } elseif (str_contains($error, 'could not find driver')) {
            $message .= ' The PHP extension for the selected database type is not installed.';
        } elseif (str_contains($error, 'Connection refused')) {
            $message .= ' The database server refused the connection. Please check if it is running.';
        } else {
            $message .= ' Please check your database settings and try again. If the issue persists, review the logs at storage/logs/laravel.log.';
        }

        return $message;
    }
}
