<?php

namespace RelayerCore\LaravelInstaller\Steps;

use Illuminate\Support\Facades\DB;
use RelayerCore\LaravelInstaller\Contracts\EnvironmentWriter;
use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

class ConfigureEnvironment implements InstallerStep
{
    public function __construct(
        protected EnvironmentWriter $env
    ) {}

    public function id(): string
    {
        return 'environment';
    }

    public function label(): string
    {
        return 'Database Setup';
    }

    public function view(): string
    {
        return 'installer::steps.environment';
    }

    public function isSkipped(): bool
    {
        return false;
    }

    public function validate(array $data = []): bool
    {
        try {
            return $this->testConnection($data);
        } catch (\Exception $e) {
            throw $e; // Re-throw to show error in UI
        }
    }

    public function process(array $data = []): void
    {
        // Update .env
        $this->env->fill([
            'DB_CONNECTION' => $data['connection'] ?? 'mysql',
            'DB_HOST' => $data['host'] ?? '127.0.0.1',
            'DB_PORT' => $data['port'] ?? '3306',
            'DB_DATABASE' => $data['database'] ?? 'laravel',
            'DB_USERNAME' => $data['username'] ?? 'root',
            'DB_PASSWORD' => $data['password'] ?? '',
            'BOOKFLOW_MULTI_TENANT' => isset($data['multi_tenant']) && $data['multi_tenant'] ? 'true' : 'false',
        ]);
        
        $this->env->save();
        
        // Purge to ensure next steps use new credentials
        DB::purge();
    }

    public function testConnection(array $data): bool
    {
        // ... (Connection logic similar to original but cleaner)
        // For brevity in this refactor, implying simplified logic or use of dedicated service
        $connection = $data['connection'] ?? 'mysql';
        $host = $data['host'] ?? '127.0.0.1';
        $port = $data['port'] ?? '3306';
        $database = $data['database'] ?? '';
        $username = $data['username'] ?? 'root';
        $password = $data['password'] ?? '';

        $dsn = "{$connection}:host={$host};port={$port}";
        
        try {
            $pdo = new \PDO($dsn, $username, $password);
            // Optionally try to create Database
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // In SQLite, the database is the file, so we skip creation check usually.
            if ($connection === 'sqlite') {
                return true;
            }
            
            // Check if DB exists or create it
            // Note: This query might fail if user doesn't have privileges, which is a valid test result
            if ($database) {
                $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$database}'");
                if (!$stmt->fetch()) {
                     $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                }
            }
            
            return true;
        } catch (\PDOException $e) {
             throw new \RuntimeException("Connection failed: " . $e->getMessage());
        }
    }
}
