<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    | The name displayed in the installer wizard.
    */
    'name' => env('APP_NAME', 'Laravel App'),

    /*
    |--------------------------------------------------------------------------
    | Logo Path
    |--------------------------------------------------------------------------
    | Path to your logo image (relative to public/).
    */
    'logo' => null,

    /*
    |--------------------------------------------------------------------------
    | PHP Requirements
    |--------------------------------------------------------------------------
    */
    'requirements' => [
        'php_version' => '8.2',
        'extensions' => [
            'bcmath',
            'ctype',
            'fileinfo',
            'json',
            'mbstring',
            'openssl',
            'pdo',
            'pdo_mysql',
            'tokenizer',
            'xml',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Writable Directories
    |--------------------------------------------------------------------------
    | Directories that must be writable for installation.
    */
    'writable_directories' => [
        'storage/app',
        'storage/framework',
        'storage/logs',
        'bootstrap/cache',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin User Model
    |--------------------------------------------------------------------------
    | The model class for creating admin users.
    */
    'admin_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Admin Role Field
    |--------------------------------------------------------------------------
    | The field name and value for the admin role.
    */
    'admin_role' => [
        'field' => 'role',
        'value' => 'admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Installation Lock File
    |--------------------------------------------------------------------------
    | The file that marks the application as installed.
    */
    'installed_file' => storage_path('installed'),

    /*
    |--------------------------------------------------------------------------
    | Theme Colors
    |--------------------------------------------------------------------------
    */
    'theme' => [
        'primary' => '#6366f1',
        'primary_dark' => '#4f46e5',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Steps
    |--------------------------------------------------------------------------
    | Add custom installation steps.
    | Each step needs: name, view, handler (optional)
    */
    'custom_steps' => [],

    /*
    |--------------------------------------------------------------------------
    | After Install Callback
    |--------------------------------------------------------------------------
    | Called after successful installation.
    */
    'after_install' => null,

    /*
    |--------------------------------------------------------------------------
    | Redirect After Install
    |--------------------------------------------------------------------------
    */
    'redirect_after_install' => '/admin/dashboard',
];
