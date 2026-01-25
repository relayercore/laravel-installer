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

        Artisan::call('migrate', ['--force' => true]);

        if (!empty($data['load_demo_data'])) {
            $seederClass = config('installer.seeder', 'DatabaseSeeder');
            Artisan::call('db:seed', ['--class' => $seederClass, '--force' => true]);
        }
        
        // TODO: Capture output if needed for UI logs
    }
}
