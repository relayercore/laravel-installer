<?php

use Illuminate\Support\Facades\Route;
use RelayerCore\LaravelInstaller\Http\Livewire\Installer;

Route::get('/install', Installer::class)->name('installer.index');

Route::get('/install/progress', function () {
    $progressFile = storage_path('framework/installer-progress.json');
    if (file_exists($progressFile)) {
        return response()->json(json_decode(file_get_contents($progressFile), true));
    }
    return response()->json(['messages' => [], 'timestamp' => 0]);
})->name('installer.progress');
