<?php

declare(strict_types=1);

namespace RelayerCore\LaravelInstaller\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RelayerCore\LaravelInstaller\Contracts\InstallationStateManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Global middleware that gates access based on installation status.
 *
 * When the app is NOT installed:
 *   - API/JSON requests receive a 503 JSON response (no confusing HTML redirects).
 *   - Livewire and /install routes are whitelisted so the wizard can function.
 *   - All other routes redirect to /install.
 *
 * When the app IS installed:
 *   - /install routes redirect to / to prevent re-running the wizard.
 */
class CheckInstallation
{
    /**
     * Routes accessible during installation.
     */
    protected array $exceptRoutes = [
        'install',
        'install/*',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isInstalled = app(InstallationStateManager::class)->isInstalled();

        // If not installed, only allow access to install routes
        if (!$isInstalled) {
            $path = $request->path();
            // Livewire 3 registers routes under a versioned hash prefix
            // (e.g. livewire-782928e6/update) so we match any path that
            // starts with 'livewire' rather than relying on exact patterns.
            if (str_starts_with($path, 'livewire')) {
                return $next($request);
            }

            foreach ($this->exceptRoutes as $route) {
                if ($request->is($route)) {
                    return $next($request);
                }
            }

            // Return a proper JSON 503 for API/AJAX requests instead of an HTML redirect
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(
                    ['error' => __('installer::installer.not_installed')],
                    Response::HTTP_SERVICE_UNAVAILABLE
                );
            }

            return new RedirectResponse(url('/install'));
        }

        // If installed, block install routes
        if ($isInstalled && $request->is('install*')) {
            return new RedirectResponse(url('/'));
        }

        return $next($request);
    }
}
