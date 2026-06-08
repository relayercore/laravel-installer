<?php

namespace RelayerCore\LaravelInstaller\Steps;

use Illuminate\Support\Facades\Log;
use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

/**
 * Validates that the server meets minimum PHP version and extension requirements.
 *
 * Requirements are read from config('installer.requirements') so each host
 * application can declare its own minimum PHP version and required extensions.
 */
class CheckRequirements implements InstallerStep
{
    public function id(): string
    {
        return 'requirements';
    }

    public function label(): string
    {
        return __('installer::installer.step_requirements');
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
        $failures = array_keys(array_filter($requirements, fn($res) => !$res));

        if (!empty($failures)) {
            Log::warning("Installer: Requirements failed: " . implode(', ', $failures));
        }

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

        // Check memory limit
        $minMemory = config('installer.requirements.memory_limit', '128M');
        $memoryBytes = $this->memoryInBytes(ini_get('memory_limit'));
        $minBytes = $this->memoryInBytes($minMemory);
        $results["Memory Limit >= {$minMemory}"] = $memoryBytes >= $minBytes;

        // Check opcache
        $opcacheRequired = config('installer.requirements.opcache', false);
        if ($opcacheRequired) {
            $status = function_exists('opcache_get_status') ? opcache_get_status(false) : false;
            $results['OPcache Enabled'] = is_array($status) && ($status['opcache_enabled'] ?? false);
        }

        return $results;
    }

    protected function memoryInBytes(string $value): int
    {
        $value = strtolower(trim($value));
        $unit = substr($value, -1);
        $size = (int) $value;

        return match ($unit) {
            'g' => $size * 1024 * 1024 * 1024,
            'm' => $size * 1024 * 1024,
            'k' => $size * 1024,
            default => $size,
        };
    }
}
