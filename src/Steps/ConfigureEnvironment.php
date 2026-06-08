<?php

declare(strict_types=1);

namespace RelayerCore\LaravelInstaller\Steps;

use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;
use RelayerCore\LaravelInstaller\Contracts\EnvironmentWriter;
use RelayerCore\LaravelInstaller\Contracts\InstallerStep;
use RuntimeException;

/**
 * Handles database configuration and .env file updates during installation.
 *
 * Validates the database connection via raw PDO (before Laravel's config is
 * hydrated), auto-creates the database if it doesn't exist, and persists
 * the connection settings plus any host-app-defined extra environment
 * fields to the .env file.
 */
class ConfigureEnvironment implements InstallerStep
{
    private const PDO_ERROR_MESSAGES = [
        1045 => 'The database username or password is incorrect.',
        1044 => 'The database user does not have permission to access this database.',
        1049 => 'The specified database does not exist and could not be created.',
        2002 => 'Could not connect to the database server. Please check the host and port.',
        2003 => 'Could not connect to the database server. Please check the host and port.',
        2005 => 'The database host address could not be resolved.',
        2013 => 'The connection to the database server was lost. Please check your network.',
    ];

    public function __construct(
        protected EnvironmentWriter $env
    ) {}

    public function id(): string
    {
        return 'environment';
    }

    public function label(): string
    {
        return __('installer::installer.step_environment');
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
            throw $e;
        }
    }

    public function process(array $data = []): void
    {
        $connection = $data['connection'] ?? 'mysql';
        $database = $data['database'] ?? 'laravel';
        
        if ($connection === 'sqlite') {
            if ($database === 'laravel' || $database === 'laravel_app' || empty($database)) {
                $database = database_path('database.sqlite');
            } elseif (!str_starts_with($database, '/') && !preg_match('/^[a-zA-Z]:\\\\/', $database)) {
                $database = database_path(basename($database));
            }
        }

        $envValues = [
            'DB_CONNECTION' => $connection,
            'DB_HOST' => $data['host'] ?? '127.0.0.1',
            'DB_PORT' => $data['port'] ?? '3306',
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $data['username'] ?? 'root',
            'DB_PASSWORD' => $data['password'] ?? '',
        ];

        // Merge any extra environment fields defined by the host application
        foreach (config('installer.environment_fields', []) as $envKey => $fieldConfig) {
            $stateKey = $fieldConfig['state_key'] ?? strtolower($envKey);
            $type = $fieldConfig['type'] ?? 'text';

            if ($type === 'checkbox') {
                $envValues[$envKey] = !empty($data[$stateKey]) ? 'true' : 'false';
            } else {
                $envValues[$envKey] = $data[$stateKey] ?? ($fieldConfig['default'] ?? '');
            }
        }

        $this->env->fill($envValues);
        $this->env->save();

        DB::purge();
    }

    public function testConnection(array $data): bool
    {
        $connection = $data['connection'] ?? 'mysql';
        $host = $data['host'] ?? '127.0.0.1';
        $port = $data['port'] ?? $this->defaultPort($connection);
        $database = $data['database'] ?? '';
        $username = $data['username'] ?? 'root';
        $password = $data['password'] ?? '';

        try {
            $pdo = $this->createPdo($connection, $host, $port, $database, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if ($connection === 'sqlite') {
                return true;
            }

            if ($database) {
                $this->ensureDatabaseExists($pdo, $connection, $database);
            }

            return true;
        } catch (PDOException $e) {
            throw new RuntimeException($this->friendlyErrorMessage($e, $connection, $host, $port));
        }
    }

    private function defaultPort(string $connection): string
    {
        return match ($connection) {
            'pgsql' => '5432',
            'sqlsrv' => '1433',
            default => '3306',
        };
    }

    private function createPdo(string $connection, string $host, string $port, string $database, string $username, string $password): PDO
    {
        if ($connection === 'sqlite') {
            if ($database === 'laravel' || $database === 'laravel_app' || empty($database)) {
                $database = database_path('database.sqlite');
            } elseif (!str_starts_with($database, '/') && !preg_match('/^[a-zA-Z]:\\\\/', $database)) {
                $database = database_path(basename($database));
            }
            $dsn = 'sqlite:' . $database;
        } else {
            $dsn = match ($connection) {
                'mysql', 'mariadb' => "mysql:host={$host};port={$port};charset=utf8mb4",
                'pgsql' => "pgsql:host={$host};port={$port};dbname={$database}",
                'sqlsrv' => "sqlsrv:Server={$host},{$port};Database={$database}",
                default => "{$connection}:host={$host};port={$port}",
            };
        }

        return new PDO($dsn, $username, $password);
    }

    private function ensureDatabaseExists(PDO $pdo, string $connection, string $database): void
    {
        $exists = match ($connection) {
            'mysql', 'mariadb' => $this->mysqlDatabaseExists($pdo, $database),
            'pgsql' => $this->pgsqlDatabaseExists($pdo, $database),
            'sqlsrv' => $this->sqlsrvDatabaseExists($pdo, $database),
            default => true,
        };

        if (!$exists) {
            $this->createDatabase($pdo, $connection, $database);
        }
    }

    private function mysqlDatabaseExists(PDO $pdo, string $database): bool
    {
        $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
        $stmt->execute([$database]);

        return (bool) $stmt->fetch();
    }

    private function pgsqlDatabaseExists(PDO $pdo, string $database): bool
    {
        $stmt = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = ?');
        $stmt->execute([$database]);

        return (bool) $stmt->fetch();
    }

    private function sqlsrvDatabaseExists(PDO $pdo, string $database): bool
    {
        $stmt = $pdo->prepare('SELECT name FROM sys.databases WHERE name = ?');
        $stmt->execute([$database]);

        return (bool) $stmt->fetch();
    }

    private function createDatabase(PDO $pdo, string $connection, string $database): void
    {
        $sql = match ($connection) {
            'mysql', 'mariadb' => "CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
            'pgsql' => "CREATE DATABASE \"{$database}\"",
            'sqlsrv' => "CREATE DATABASE [{$database}]",
            default => null,
        };

        if ($sql) {
            $pdo->exec($sql);
        }
    }

    private function friendlyErrorMessage(PDOException $e, string $connection, string $host, string $port): string
    {
        $code = (int) $e->getCode();

        if (isset(self::PDO_ERROR_MESSAGES[$code])) {
            $message = self::PDO_ERROR_MESSAGES[$code];
        } else {
            $message = 'Database connection failed. Please verify your settings and try again.';
        }

        return $message;
    }
}
