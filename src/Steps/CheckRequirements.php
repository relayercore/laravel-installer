<?php

namespace RelayerCore\LaravelInstaller\Steps;

use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

class CheckRequirements implements InstallerStep
{
    public function id(): string
    {
        return 'requirements';
    }

    public function label(): string
    {
        return 'Server Requirements';
    }

    public function view(): string
    {
        return 'installer::steps.requirements';
    }

    public function isSkipped(): bool
    {
        return false;
    }

    public function validate(array $data = []): bool
    {
        $requirements = $this->check();
        return !in_array(false, $requirements);
    }

    public function process(array $data = []): void
    {
        // No side effects, just validation
    }

    public function check(): array
    {
        $results = [];
        $minPhp = config('installer.requirements.php_version', '8.2');
        $results["PHP >= {$minPhp}"] = version_compare(PHP_VERSION, $minPhp, '>=');
        
        foreach (config('installer.requirements.extensions', []) as $ext) {
            $results[ucfirst($ext) . ' Extension'] = extension_loaded($ext);
        }

        return $results;
    }
}
