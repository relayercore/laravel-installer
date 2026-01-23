<?php

namespace RelayerCore\LaravelInstaller\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        'livewire/message/*',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use file_exists directly for performance (avoid facades in middleware)
        $installedFile = config('installer.installed_file') ?? storage_path('installed');
        $isInstalled = file_exists($installedFile);

        // If not installed, only allow access to install routes
        if (!$isInstalled) {
            foreach ($this->exceptRoutes as $route) {
                if ($request->is($route)) {
                    return $next($request);
                }
            }
            
            // Return redirect response directly
            return new \Illuminate\Http\RedirectResponse(url('/install'));
        }

        // If installed, block install routes
        if ($isInstalled && $request->is('install*')) {
            return new \Illuminate\Http\RedirectResponse(url('/'));
        }

        return $next($request);
    }
}
