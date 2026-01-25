<?php

namespace RelayerCore\LaravelInstaller\Services;

use Illuminate\Support\Facades\File;
use RelayerCore\LaravelInstaller\Contracts\EnvironmentWriter;

class DotEnvWriter implements EnvironmentWriter
{
    protected string $path;
    protected string $content;
    protected array $variables = [];
    protected bool $hasChanges = false;

    public function __construct(string $path = null)
    {
        $this->path = $path ?? base_path('.env');
        $this->load();
    }

    protected function load(): void
    {
        if (!File::exists($this->path)) {
            $examplePath = base_path('.env.example');
            if (File::exists($examplePath)) {
                File::copy($examplePath, $this->path);
            } else {
                File::put($this->path, '');
            }
        }
        
        $this->content = File::get($this->path);
        // We do a simple parse primarily to populate our internal cache if needed, 
        // but for writing we want to preserve the original structure, so we mainly operate on $this->content.
    }

    public function get(string $key, $default = null): string|null
    {
        // Simple regex lookup
        if (preg_match("/^{$key}=(.*)$/m", $this->content, $matches)) {
            return trim($matches[1], "\"'");
        }
        return $default;
    }

    public function set(string $key, string $value): void
    {
        $value = $this->escapeValue($value);
        $pattern = "/^" . preg_quote($key, '/') . "=.*/m";
        
        if (preg_match($pattern, $this->content)) {
            $this->content = preg_replace($pattern, "{$key}={$value}", $this->content);
        } else {
            // Append to end if not exists
            $this->content .= "\n{$key}={$value}";
        }
        
        $this->hasChanges = true;
    }

    public function fill(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, (string)$value);
        }
    }

    public function save(): bool
    {
        if ($this->hasChanges) {
            return File::put($this->path, $this->content) !== false;
        }
        return true;
    }

    protected function escapeValue(string $value): string
    {
        if ($value === '' || preg_match('/[\s"#=\\\']/', $value)) {
            $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
            return '"' . $escaped . '"';
        }
        return $value;
    }
}
