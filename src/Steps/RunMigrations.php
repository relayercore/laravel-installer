<?php

declare(strict_types=1);

namespace RelayerCore\LaravelInstaller\Steps;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

/**
 * Runs database migrations and optional demo data seeding.
 *
 * On failure, automatically wipes the database to leave a clean state,
 * then re-throws a user-friendly exception describing the root cause.
 */
class RunMigrations implements InstallerStep
{
    public function id(): string
    {
        return 'migrations';
    }

    public function label(): string
    {
        return __('installer::installer.step_migrations');
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
                Artisan::call('db:seed', ['--class' => $seederClass, '--force' => true]);
            }
        } catch (Exception $e) {
            Artisan::call('db:wipe', ['--force' => true]);

            Log::error('Installer migration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception($this->buildErrorMessage($e));
        }
    }

    private function buildErrorMessage(Exception $e): string
    {
        $message = __('installer::installer.migrations_error_prefix');

        $error = $e->getMessage();

        if (str_contains($error, 'Access denied')) {
            $message .= ' ' . __('installer::installer.migrations_error_access_denied');
        } elseif (str_contains($error, 'Base table or view already exists') || str_contains($error, 'SQLSTATE[42S01]')) {
            $message .= ' ' . __('installer::installer.migrations_error_table_exists');
        } elseif (str_contains($error, 'could not find driver')) {
            $message .= ' ' . __('installer::installer.migrations_error_no_driver');
        } elseif (str_contains($error, 'Connection refused')) {
            $message .= ' ' . __('installer::installer.migrations_error_connection_refused');
        } else {
            $message .= ' ' . __('installer::installer.migrations_error_fallback');
        }

        return $message;
    }
}
