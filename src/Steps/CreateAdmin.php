<?php

namespace RelayerCore\LaravelInstaller\Steps;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

/**
 * Creates the initial administrator account during installation.
 *
 * Uses the model class from config('installer.admin_model') and delegates
 * role/permission assignment to the host application via the
 * config('installer.on_admin_created') closure, keeping this step
 * completely agnostic of any specific auth/role system.
 */
class CreateAdmin implements InstallerStep
{
    public function id(): string
    {
        return 'admin';
    }

    public function label(): string
    {
        return __('installer::installer.step_admin');
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
        return !empty($data['email']) && !empty($data['password']);
    }

    public function process(array $data = []): void
    {
        $modelClass = config('installer.admin_model');

        if (!class_exists($modelClass)) {
            return;
        }

        $user = $modelClass::where('email', $data['email'])->first() ?? new $modelClass();

        $user->email = $data['email'];
        $user->name = $data['name'];
        $user->password = Hash::make($data['password']);

        // Handle common flags present on many User models
        if (Schema::hasColumn($user->getTable(), 'is_active')) {
            $user->is_active = true;
        }

        $user->save();

        // Delegate role/permission assignment to the host application
        $callbackClass = config('installer.on_admin_created');
        if (is_string($callbackClass) && class_exists($callbackClass)) {
            $callback = app($callbackClass);
            if (is_callable($callback)) {
                $callback($user);
            }
        }
    }
}
