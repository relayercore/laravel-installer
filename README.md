# Laravel Installer

A beautiful, reusable web-based installer for Laravel applications. Perfect for CodeCanyon products and commercial Laravel apps.

## Features

- 🎨 Beautiful, modern UI
- 📋 Configurable installation steps
- ✅ Server requirements checking
- 🗄️ Database configuration & testing
- 👤 Admin account creation
- 🔌 Custom step support
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

### Custom Steps

Add custom steps in config:

```php
'custom_steps' => [
    [
        'name' => 'License',
        'view' => 'installer.steps.license',
        'handler' => \App\Installer\LicenseStep::class,
    ],
],
```

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

## Security

After installation, the `/install` route is automatically blocked. Optionally delete the route for extra security.

## License

MIT
