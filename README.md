# Laravel Installer

[![Tests](https://github.com/relayercore/laravel-installer/actions/workflows/tests.yml/badge.svg)](https://github.com/relayercore/laravel-installer/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-10%20%7C%2011%20%7C%2012-FF2D20.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A beautiful, reusable web-based installer for Laravel applications. Perfect for CodeCanyon products and commercial Laravel apps.

## Features

- 🎨 Beautiful, modern UI
- 📋 Configurable installation steps
- ✅ Server requirements checking
- 🗄️ Database configuration & **Instant Connection Testing**
- 👤 Admin account creation
- 🔄 Modular SOLID Architecture (Easy to extend)
- 🎯 Works with Laravel 10, 11 & 12

## Installation

```bash
composer require relayercore/laravel-installer
```

## Usage

### 1. Publish Configuration

```bash
php artisan vendor:publish --tag=installer-config
```

### 2. Publish Views (Optional - for customization)

```bash
php artisan vendor:publish --tag=installer-views
```

### 3. Configure

Edit `config/installer.php`:

```php
return [
    'name' => 'My App',
    'logo' => '/images/logo.png',
    
    'requirements' => [
        'php' => '8.2',
        'extensions' => ['pdo', 'mbstring', 'openssl', 'json'],
    ],
    
    'writable_directories' => [
        'storage/app',
        'storage/framework',
        'storage/logs',
        'bootstrap/cache',
    ],
    
    'admin_model' => \App\Models\User::class,
    
    'after_install' => function() {
        // Custom post-install logic
    },
];
```

### 4. Access Installer

Navigate to `/install` in your browser.

## Customization


### Styling

Publish views and modify the Blade templates:

```bash
php artisan vendor:publish --tag=installer-views
```

Views are published to `resources/views/vendor/installer/`.

## How It Works

1. Middleware checks for `storage/installed` file
2. If missing, redirects all routes to `/install`
3. Wizard guides through: Requirements → Permissions → Database → Admin → Done
4. Creates `storage/installed` marker file
5. App runs normally

## Security & Best Practices for CodeCanyon

If you are distributing your application (e.g., via CodeCanyon or zip file), follow these steps to ensure a secure installation experience:

1.  **Include a `.env` file**: Your zip file MUST include a `.env` file in the root.
2.  **Set a "Boot Key"**: Inside that `.env`, set a generic `APP_KEY` which acts as the "boot key" to allow the installer wizard to load.
    ```env
    APP_KEY=base64:YOUR_GENERIC_KEY_HERE
    ```
3.  **Automatic Rotation**: This installer automatically runs `php artisan key:generate` as the final step, replacing your generic key with a **unique, secure key** for the customer.

This ensures your customers don't face "No application key" errors, while maintaining top-tier security.

MIT
