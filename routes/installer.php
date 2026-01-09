<?php

use Illuminate\Support\Facades\Route;
use RelayerCore\LaravelInstaller\Http\Livewire\Installer;

Route::get('/install', Installer::class)->name('installer.index');
