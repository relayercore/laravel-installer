# Laravel Installer

[![Tests](https://github.com/relayercore/laravel-installer/actions/workflows/tests.yml/badge.svg)](https://github.com/relayercore/laravel-installer/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-10%20%7C%2011%20%7C%2012-FF2D20.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A beautiful, reusable, and highly customizable web-based installer wizard for Laravel applications. Drop it into any project to provide a professional onboarding experience for your users — zero bloat, maximum flexibility.

## Features

- 🎨 **Beautiful UI** — Modern, responsive, Tailwind and Livewire-powered wizard.
- 📋 **Config-Driven Pipeline** — Add, remove, or reorder steps easily via configuration.
- ✅ **Server Requirements** — Automatic checking for PHP version and required extensions.
- 📂 **Permissions Checking** — Verifies required directories are writable (e.g., `storage`, `bootstrap/cache`).
- 🗄️ **Database Setup** — Instant connection testing and automatic database creation for MySQL, PostgreSQL, SQL Server, and SQLite.
- 👤 **Admin Creation** — Built-in admin account creation with hook-based role assignment.
- 🛠️ **Custom Step Generator** — Scaffold custom steps instantly using the `php artisan make:installer-step` command.
- 🌐 **Localization (i18n)** — 100% of the UI strings are translatable.
- 🔌 **Dynamic `.env` Injection** — Easily prompt users for custom environment variables during the database setup step.
- 🛡️ **Secure** — Automatically runs `key:generate` and protects your app with API-friendly 503 middleware until installation is complete.
- 🔄 **Forward Compatible** — Works flawlessly with Laravel 10, 11, 12 & 13.

---

## Installation

Require the package via Composer:

```bash
composer require relayercore/laravel-installer
```

## Quick Start

### 1. Publish Assets
To customize the installer for your application, you should publish the configuration file. You can also publish views and translations if you want full control over the UI and text.

```bash
# Required: Publish the configuration file
php artisan vendor:publish --tag=installer-config

# Optional: Publish views (to customize the HTML/CSS)
php artisan vendor:publish --tag=installer-views

# Optional: Publish translations (to change text or add languages)
php artisan vendor:publish --tag=installer-lang
```

### 2. Configure the Installer
Open `config/installer.php`. This file is the heart of the installer. You can configure the app name, logo, theme colors, and the exact steps your users will walk through.

```php
return [
    'name' => env('APP_NAME', 'My App'),
    
    // Path to your logo (relative to public/)
    'logo' => '/images/logo.png',
    
    // Custom favicon (defaults to asset('favicon.png'))
    'favicon' => asset('favicon.png'),

    // Define the exact order of installation steps
    'steps' => [
        \RelayerCore\LaravelInstaller\Steps\CheckRequirements::class,
        \RelayerCore\LaravelInstaller\Steps\CheckPermissions::class,
        \RelayerCore\LaravelInstaller\Steps\ConfigureEnvironment::class,
        \RelayerCore\LaravelInstaller\Steps\RunMigrations::class,
        \RelayerCore\LaravelInstaller\Steps\CreateAdmin::class,
    ],

    // Hook: Assign roles or permissions after the admin is created
    'on_admin_created' => function ($user) {
        $user->assignRole('super-admin');
    },

    // Hook: Run logic when the installer completely finishes
    'after_install' => function () {
        \Artisan::call('cache:clear');
    },
];
```

### 3. Launch the Installer
Simply navigate to `/install` in your browser. The package will automatically intercept traffic to your application if the app hasn't been installed yet.

---

## Adding Custom Steps

The installer is designed to be fully extensible. If your application requires users to select a pricing plan, configure a third-party API key, or choose an industry, you can add a custom step!

### 1. Scaffold the Step
Use our provided Artisan command to generate the boilerplate:

```bash
php artisan make:installer-step SelectPlan
```

This will create a new class at `app/Installer/Steps/SelectPlan.php`:

```php
<?php

namespace App\Installer\Steps;

use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

class SelectPlan implements InstallerStep
{
    public function id(): string { return 'plan'; }
    public function label(): string { return 'Select Plan'; }
    public function view(): string { return 'installer.steps.plan'; }
    public function isSkipped(): bool { return false; }

    public function validate(array $data = []): bool
    {
        // Add your validation rules
        return !empty($data['plan']);
    }

    public function process(array $data = []): void
    {
        // Process and save the selected plan to the database or .env
    }
}
```

### 2. Create the View
Create the Livewire view file referenced in your step (e.g., `resources/views/installer/steps/plan.blade.php`). Use `wire:model="state.plan"` to bind inputs to the `$data` array passed to your `validate()` and `process()` methods.

### 3. Register the Step
Add your new step to the `steps` array in `config/installer.php`:

```php
'steps' => [
    \RelayerCore\LaravelInstaller\Steps\CheckRequirements::class,
    \RelayerCore\LaravelInstaller\Steps\CheckPermissions::class,
    \RelayerCore\LaravelInstaller\Steps\ConfigureEnvironment::class,
    \RelayerCore\LaravelInstaller\Steps\RunMigrations::class,
    \App\Installer\Steps\SelectPlan::class, // <-- Your custom step
    \RelayerCore\LaravelInstaller\Steps\CreateAdmin::class,
],
```

---

## Customizing the Admin Step

By default, the built-in `CreateAdmin` step expects your User model to have `name`, `email`, and `password` fields. 

**What if your User model uses `username`, or `first_name` and `last_name`?**
Because the installer is modular, you shouldn't try to hack the core. Instead, simply swap out the default admin step with your own!

1. Generate your custom admin step:
   ```bash
   php artisan make:installer-step SetupAdmin
   ```
2. Implement your custom user creation logic in the `process()` method of `app/Installer/Steps/SetupAdmin.php`.
3. Update `config/installer.php` to remove the default step and use yours:

```php
'steps' => [
    // ...
    \RelayerCore\LaravelInstaller\Steps\RunMigrations::class,
    // \RelayerCore\LaravelInstaller\Steps\CreateAdmin::class, <-- Remove this
    \App\Installer\Steps\SetupAdmin::class,                    // <-- Add this
],
```

---

## Extra Environment Fields

If you just need to prompt the user for a few extra `.env` variables (like Timezone or Multi-Tenancy mode), you don't need a custom step! You can inject them directly into the built-in Database Setup screen via `config/installer.php`:

```php
'environment_fields' => [
    'MULTI_TENANT' => [
        'type' => 'checkbox',
        'label' => 'Enable Multi-Tenancy',
        'description' => 'Host multiple businesses under one installation.',
        'default' => false,
        'state_key' => 'multi_tenant', // Binds to wire:model="state.multi_tenant"
    ],
    'APP_TIMEZONE' => [
        'type' => 'text',
        'label' => 'Timezone',
        'placeholder' => 'UTC',
        'default' => 'UTC',
        'state_key' => 'timezone',
    ],
],
```

---

## How It Works Under the Hood

1. **Middleware (`CheckInstallation`):** Checks for the existence of the `storage/installed` file.
2. **Interception:** If missing, it redirects web routes to `/install`. API requests receive a `503 Service Unavailable` JSON response.
3. **Wizard:** Guides the user through your configured pipeline of steps.
4. **Completion:** Creates the `storage/installed` marker file and automatically runs `php artisan key:generate` to secure the application.
5. **Success:** The application runs normally for all future requests.

## Security & Distribution Best Practices

If you are distributing your application (e.g., via CodeCanyon, GitHub, or zip file):

1. **Include a generic `.env` file** in your distribution with an empty or generic `APP_KEY`.
2. Do **NOT** include the `storage/installed` file in your distribution.
3. When the user finishes the installer, the package will automatically generate a **unique, secure `APP_KEY`** for their specific installation.

## License

The Laravel Installer is open-sourced software licensed under the [MIT license](LICENSE).
