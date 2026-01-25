<?php

namespace RelayerCore\LaravelInstaller\Steps;

use Illuminate\Support\Facades\Hash;
use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

class CreateAdmin implements InstallerStep
{
    public function id(): string
    {
        return 'admin';
    }

    public function label(): string
    {
        return 'Create Admin';
    }

    public function view(): string
    {
        return 'installer::steps.admin';
    }

    public function isSkipped(): bool
    {
        return false;
    }

    public function validate(array $data = []): bool
    {
        // In real impl, would use Validator façade
        return !empty($data['email']) && !empty($data['password']);
    }

    public function process(array $data = []): void
    {
        $modelClass = config('installer.admin_model');
        // Simple check
        if (!class_exists($modelClass)) {
            // Log warning or throw, but don't fail hard if user model is weird
            return;
        }

        $user = $modelClass::where('email', $data['email'])->first() ?? new $modelClass();
        
        $user->email = $data['email'];
        $user->name = $data['name'];
        $user->password = Hash::make($data['password']);
        
        // Handle Role if configured
        $roleField = config('installer.admin_role.field');
        if ($roleField) {
            $user->$roleField = config('installer.admin_role.value', 'admin');
        }

        // Handle common flags
        if (\Illuminate\Support\Facades\Schema::hasColumn($user->getTable(), 'is_active')) {
            $user->is_active = true;
        }
        
        $user->save();
    }
}
