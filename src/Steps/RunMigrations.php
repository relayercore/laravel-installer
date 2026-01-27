<?php

namespace RelayerCore\LaravelInstaller\Steps;

use Illuminate\Support\Facades\Artisan;
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

                // Dynamic Seeder: Check if selected vertical has a specific seeder
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
            // Critical: If any step fails, we must rollback to leave a clean state.
            Artisan::call('db:wipe', ['--force' => true]);
            
            // Log the full technical error for debugging
            \Illuminate\Support\Facades\Log::error("Installer Failed: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());

            // Throw a friendly error for the user interface
            throw new \Exception("Installation failed while setting up the database. We have cleaned up the system. Please click 'Try Again'. If the issue persists, check the logs.");
        }
    }
}
