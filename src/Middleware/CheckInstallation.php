<?php

namespace RelayerCore\LaravelInstaller\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Routes accessible during installation.
     */
    protected array $exceptRoutes = [
        'install',
        'install/*',
        'livewire/*',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $installedFile = config('installer.installed_file', storage_path('installed'));
        $isInstalled = File::exists($installedFile);

        // If not installed, only allow access to install routes
        if (!$isInstalled) {
            foreach ($this->exceptRoutes as $route) {
                if ($request->is($route)) {
                    return $next($request);
                }
            }
            return redirect()->route('installer.index');
        }

        // If installed, block install routes
        if ($isInstalled && $request->is('install*')) {
            return redirect('/');
        }

        return $next($request);
    }
}
