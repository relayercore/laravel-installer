<?php

namespace RelayerCore\LaravelInstaller\Steps;

use Illuminate\Support\Facades\File;
use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

class SelectIndustry implements InstallerStep
{
    protected array $availableVerticals = [];

    public function __construct()
    {
        $this->scanVerticals();
    }

    public function id(): string
    {
        return 'industry';
    }

    public function label(): string
    {
        return 'Business Type';
    }

    public function view(): string
    {
        return 'installer::steps.industry';
    }

    public function isSkipped(): bool
    {
        return empty($this->availableVerticals);
    }

    public function validate(array $state = []): bool
    {
        // If no verticals exist, this step is skipped/auto-valid so return true
        if (empty($this->availableVerticals)) {
            return true;
        }

        // Must select a vertical (or 'universal' if we offer that fallback)
        return !empty($state['vertical']) && (
            $state['vertical'] === 'universal' || 
            array_key_exists($state['vertical'], $this->availableVerticals)
        );
    }

    public function process(array $state = []): void
    {
        // This is called when moving to next step.
        // We will handle the actual activation in the finalizer.
        // Here we just ensure state is valid.
    }

    public function getAvailableVerticals(): array
    {
        return $this->availableVerticals;
    }

    public static function hasVerticals(): bool
    {
        $path = base_path('verticals');
        
        if (!File::isDirectory($path)) {
            return false;
        }

        // Check if any directory has module.json
        foreach (File::directories($path) as $directory) {
            if (File::exists($directory . '/module.json')) {
                return true;
            }
        }

        return false;
    }

    protected function scanVerticals(): void
    {
        $path = base_path('verticals');
        
        if (!File::isDirectory($path)) {
            return;
        }

        foreach (File::directories($path) as $directory) {
            $slug = basename($directory);

            $manifest = $directory . '/module.json';
            
            if (File::exists($manifest)) {
                $data = json_decode(File::get($manifest), true);

                $screenshots = $data['screenshots'] ?? [];

                // Auto-discover screenshot.png (WP Style)
                // We base64 encode it so it displays without needing to be in public/
                $screenMatch = null;
                if (File::exists($directory . '/screenshot.png')) {
                     $screenMatch = $directory . '/screenshot.png';
                } elseif (File::exists($directory . '/screenshot.jpg')) {
                     $screenMatch = $directory . '/screenshot.jpg';
                }
                
                if ($screenMatch) {
                    $mime = pathinfo($screenMatch, PATHINFO_EXTENSION) === 'png' ? 'image/png' : 'image/jpeg';
                    $base64 = 'data:' . $mime . ';base64,' . base64_encode(File::get($screenMatch));
                    array_unshift($screenshots, $base64);
                }

                $this->availableVerticals[$slug] = [
                    'name' => $data['name'] ?? $slug,
                    'description' => $data['description'] ?? '',
                    'icon' => $data['icon'] ?? 'heroicon-o-briefcase',
                    'screenshots' => $screenshots,
                ];
            }
        }
    }
    public function getDefaultScreenshot(): ?string
    {
        $screenMatch = null;
        if (File::exists(base_path('screenshot.png'))) {
             $screenMatch = base_path('screenshot.png');
        } elseif (File::exists(base_path('screenshot.jpg'))) {
             $screenMatch = base_path('screenshot.jpg');
        }
        
        if ($screenMatch) {
            $mime = pathinfo($screenMatch, PATHINFO_EXTENSION) === 'png' ? 'image/png' : 'image/jpeg';
            return 'data:' . $mime . ';base64,' . base64_encode(File::get($screenMatch));
        }

        return null;
    }
}
