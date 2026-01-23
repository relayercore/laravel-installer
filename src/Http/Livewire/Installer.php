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
    
    // Raw PDO/database error (for technical details)
    public string $dbError = '';
    // Human-friendly error summary for non-technical users
    public string $dbFriendlyError = '';
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
    public int $installProgress = 0;

    #[Rule('nullable|boolean')]
    public bool $loadDemoData = false;

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
        $this->dbFriendlyError = '';
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
            $this->dbFriendlyError = $this->humanizeDbError($e->getMessage());
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
     * Create a human-friendly error message from a raw PDO error string.
     */
    protected function humanizeDbError(string $message): string
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'access denied')) {
            return 'Could not connect to the database: please check the database username and password.';
        }

        if (str_contains($lower, 'unknown database')) {
            return 'The database name appears to be incorrect or the user has no permission to access it.';
        }

        if (str_contains($lower, 'could not find driver') || str_contains($lower, 'driver not found')) {
            return 'The required database driver is missing on this server. Ask your hosting provider to enable the PDO extension for your database type.';
        }

        if (str_contains($lower, 'connection refused') || str_contains($lower, 'no such file or directory')) {
            return 'Unable to reach the database server. Please verify the host and port or contact your hosting provider.';
        }

        // Fallback generic message
        return 'We could not connect to the database with the details you provided. Please double-check them or contact your hosting provider.';
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

    public function installStep1_Env(): void
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
        $this->installProgress = 5;
        
        try {
            $this->log('Updating environment configuration...');
            $this->updateEnv();
            $this->installProgress = 20;
            
            // Config clearing removed to prevent server restart issues
            
        } catch (\Exception $e) {
            // Capture error for display but avoid crashing the Livewire component
            $this->installError = $e->getMessage();
            $this->installing = false;
            $this->log('Installation error: ' . $e->getMessage());
        }
    }

    public function installStep2_Migrate(): void
    {
        // Preventing timeout or memory issues during migration
        ini_set('memory_limit', '-1');
        set_time_limit(300);

        try {
            $this->installProgress = 30;
            $this->log('Running database migrations...');
            $this->configureDatabase();
            Artisan::call('migrate', ['--force' => true]);
            
            if ($this->loadDemoData) {
                $this->log('Loading demo data...');
                $seederClass = config('installer.seeder', 'DatabaseSeeder');
                Artisan::call('db:seed', ['--class' => $seederClass, '--force' => true]);
                $this->log('Demo data loaded.');
            }

            $this->installProgress = 60;
            $this->log('Migrations completed.');
            
        } catch (\Exception $e) {
            $this->installError = $e->getMessage();
            $this->installing = false;
            $this->log('Migration error: ' . $e->getMessage());
        }
    }

    public function installStep3_Admin(): void
    {
        try {
            $this->installProgress = 70;
            $this->configureDatabase();
            $this->log('Creating admin account...');
            $this->createAdminUser();
            $this->installProgress = 85;
            $this->log('Admin account created.');
            
        } catch (\Exception $e) {
            $this->installError = $e->getMessage();
            $this->installing = false;
            $this->log('Admin creation error: ' . $e->getMessage());
        }
    }

    public function installStep4_Finalize(): void
    {
        try {
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
            $this->installing = false;
            $this->installProgress = 100;
            
        } catch (\Exception $e) {
            $this->installError = $e->getMessage();
            $this->installing = false;
            $this->log('Finalize error: ' . $e->getMessage());
        }
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

        // Ensure .env exists; if not, create it from .env.example or as a new file
        if (!File::exists($envPath)) {
            $examplePath = base_path('.env.example');
            if (File::exists($examplePath)) {
                File::copy($examplePath, $envPath);
            } else {
                File::put($envPath, "");
            }
        }

        $envContent = File::get($envPath);
        $originalContent = $envContent;
        
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
        
        if ($envContent !== $originalContent) {
            File::put($envPath, $envContent);
        }
    }

    protected function createAdminUser(): void
    {
        $modelClass = config('installer.admin_model');

        if (!$modelClass || !class_exists($modelClass)) {
            throw new \RuntimeException('Installer configuration error: "installer.admin_model" is not set to a valid User model class.');
        }

        $roleField = config('installer.admin_role.field', 'role');
        $roleValue = config('installer.admin_role.value', 'admin');
        
        // Check if user exists to avoid duplicate entry errors
        /** @var \Illuminate\Database\Eloquent\Model $user */
        $user = $modelClass::where('email', $this->adminEmail)->first();
        if (!$user) {
            $user = new $modelClass();
            $user->email = $this->adminEmail;
        }
        
        $user->name = $this->adminName;
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
