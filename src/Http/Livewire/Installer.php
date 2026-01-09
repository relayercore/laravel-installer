<?php

namespace RelayerCore\LaravelInstaller\Http\Livewire;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('installer::layouts.installer')]
class Installer extends Component
{
    public int $step = 1;
    public int $totalSteps = 5;
    
    public array $requirements = [];
    public array $permissions = [];
    public bool $requirementsPassed = false;
    public bool $permissionsPassed = false;
    
    // Database
    #[Rule('required|string')]
    public string $dbConnection = 'mysql';
    
    #[Rule('required|string')]
    public string $dbHost = '127.0.0.1';
    
    #[Rule('required|string')]
    public string $dbPort = '3306';
    
    #[Rule('required|string')]
    public string $dbDatabase = '';
    
    #[Rule('required|string')]
    public string $dbUsername = 'root';
    
    public string $dbPassword = '';
    
    public string $dbError = '';
    public bool $dbConnected = false;
    
    // Admin
    #[Rule('required|string|max:255')]
    public string $adminName = '';
    
    #[Rule('required|email')]
    public string $adminEmail = '';
    
    #[Rule('required|min:8')]
    public string $adminPassword = '';
    
    #[Rule('required|same:adminPassword')]
    public string $adminPasswordConfirm = '';
    
    // Install
    public bool $installing = false;
    public array $installLog = [];
    public bool $installComplete = false;
    public string $installError = '';

    public function mount()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }
        
        $this->dbDatabase = strtolower(str_replace(' ', '_', config('installer.name', 'laravel')));
        $this->checkRequirements();
        $this->checkPermissions();
    }

    protected function isInstalled(): bool
    {
        return File::exists(config('installer.installed_file', storage_path('installed')));
    }

    protected function checkRequirements(): void
    {
        $minPhp = config('installer.requirements.php_version', '8.2');
        $this->requirements["PHP >= {$minPhp}"] = version_compare(PHP_VERSION, $minPhp, '>=');
        
        foreach (config('installer.requirements.extensions', []) as $ext) {
            $this->requirements[ucfirst($ext) . ' Extension'] = extension_loaded($ext);
        }
        
        $this->requirementsPassed = !in_array(false, $this->requirements);
    }

    protected function checkPermissions(): void
    {
        foreach (config('installer.writable_directories', []) as $dir) {
            $path = base_path($dir);
            $this->permissions[$dir] = is_writable($path);
        }
        
        $this->permissions['.env file'] = is_writable(base_path('.env'));
        $this->permissionsPassed = !in_array(false, $this->permissions);
    }

    public function nextStep(): void
    {
        if ($this->step === 1 && !$this->requirementsPassed) return;
        if ($this->step === 2 && !$this->permissionsPassed) return;
        if ($this->step === 3 && !$this->dbConnected) return;
        
        $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function testConnection(): void
    {
        $this->dbError = '';
        $this->dbConnected = false;
        
        // Sanitize database name to prevent SQL injection
        $safeDatabaseName = $this->sanitizeDatabaseName($this->dbDatabase);
        
        try {
            $dsn = $this->dbConnection === 'sqlite' 
                ? "sqlite:" . database_path($safeDatabaseName . '.sqlite')
                : "{$this->dbConnection}:host={$this->dbHost};port={$this->dbPort}";
            
            $pdo = new \PDO($dsn, $this->dbUsername, $this->dbPassword);
            
            if ($this->dbConnection !== 'sqlite') {
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDatabaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo = new \PDO("{$this->dbConnection}:host={$this->dbHost};port={$this->dbPort};dbname={$safeDatabaseName}", $this->dbUsername, $this->dbPassword);
            }
            
            $this->dbConnected = true;
        } catch (\PDOException $e) {
            $this->dbError = $e->getMessage();
        }
    }

    /**
     * Sanitize database name to prevent SQL injection.
     * Only allows alphanumeric characters and underscores.
     */
    protected function sanitizeDatabaseName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $name);
    }

    /**
     * Escape a value for safe inclusion in .env file.
     * Wraps in quotes if value contains special characters.
     */
    protected function escapeEnvValue(string $value): string
    {
        // If value contains spaces, quotes, #, or = it needs quoting
        if ($value === '' || preg_match('/[\s"#=\\\']/', $value)) {
            // Escape backslashes and double quotes, then wrap in double quotes
            $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
            return '"' . $escaped . '"';
        }
        return $value;
    }

    public function install(): void
    {
        $this->validate([
            'adminName' => 'required|string|max:255',
            'adminEmail' => 'required|email',
            'adminPassword' => 'required|min:8',
            'adminPasswordConfirm' => 'required|same:adminPassword',
        ]);
        
        $this->installing = true;
        $this->installLog = [];
        $this->installError = '';
        
        try {
            $this->log('Updating environment configuration...');
            $this->updateEnv();
            
            $this->log('Clearing configuration cache...');
            Artisan::call('config:clear');
            
            $this->log('Running database migrations...');
            $this->configureDatabase();
            Artisan::call('migrate', ['--force' => true]);
            $this->log('Migrations completed.');
            
            $this->log('Creating admin account...');
            $this->createAdminUser();
            $this->log('Admin account created.');
            
            if (empty(config('app.key'))) {
                $this->log('Generating application key...');
                Artisan::call('key:generate', ['--force' => true]);
            }
            
            if (!file_exists(public_path('storage'))) {
                $this->log('Creating storage link...');
                Artisan::call('storage:link');
            }
            
            $this->log('Finalizing installation...');
            $this->markAsInstalled();
            
            // Run custom callback
            $callback = config('installer.after_install');
            if (is_callable($callback)) {
                $callback();
            }
            
            $this->log('✅ Installation completed successfully!');
            $this->installComplete = true;
            
        } catch (\Exception $e) {
            $this->installError = $e->getMessage();
            $this->log('❌ Error: ' . $e->getMessage());
        }
        
        $this->installing = false;
    }

    protected function log(string $message): void
    {
        $this->installLog[] = $message;
    }

    protected function configureDatabase(): void
    {
        config(['database.default' => $this->dbConnection]);
        config(["database.connections.{$this->dbConnection}.host" => $this->dbHost]);
        config(["database.connections.{$this->dbConnection}.port" => $this->dbPort]);
        config(["database.connections.{$this->dbConnection}.database" => $this->dbDatabase]);
        config(["database.connections.{$this->dbConnection}.username" => $this->dbUsername]);
        config(["database.connections.{$this->dbConnection}.password" => $this->dbPassword]);
        
        DB::purge();
        DB::reconnect();
    }

    protected function updateEnv(): void
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);
        
        // Sanitize database name for consistency
        $safeDatabaseName = $this->sanitizeDatabaseName($this->dbDatabase);
        
        $replacements = [
            'DB_CONNECTION' => $this->dbConnection,
            'DB_HOST' => $this->escapeEnvValue($this->dbHost),
            'DB_PORT' => $this->dbPort,
            'DB_DATABASE' => $safeDatabaseName,
            'DB_USERNAME' => $this->escapeEnvValue($this->dbUsername),
            'DB_PASSWORD' => $this->escapeEnvValue($this->dbPassword),
        ];
        
        foreach ($replacements as $key => $value) {
            // Escape the key for regex safety
            $escapedKey = preg_quote($key, '/');
            $pattern = "/^{$escapedKey}=.*/m";
            $replacement = "{$key}={$value}";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }
        
        File::put($envPath, $envContent);
    }

    protected function createAdminUser(): void
    {
        $modelClass = config('installer.admin_model', \App\Models\User::class);
        $roleField = config('installer.admin_role.field', 'role');
        $roleValue = config('installer.admin_role.value', 'admin');
        
        $user = new $modelClass();
        $user->name = $this->adminName;
        $user->email = $this->adminEmail;
        $user->password = Hash::make($this->adminPassword);
        $user->{$roleField} = $roleValue;
        
        if (Schema::hasColumn($user->getTable(), 'is_active')) {
            $user->is_active = true;
        }
        if (Schema::hasColumn($user->getTable(), 'email_verified_at')) {
            $user->email_verified_at = now();
        }
        
        $user->save();
    }

    protected function markAsInstalled(): void
    {
        $installedFile = config('installer.installed_file', storage_path('installed'));
        File::put($installedFile, now()->toDateTimeString());
    }

    public function render()
    {
        return view('installer::installer');
    }
}
