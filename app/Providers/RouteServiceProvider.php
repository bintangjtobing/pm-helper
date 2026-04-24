<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        // Constrain {provider} parameter globally. This is set BEFORE route
        // registration so new routes pick it up automatically.
        Route::pattern('provider', 'facebook|github|google|twitter|microsoft|azure|apple|linkedin|microsoft-graph|slack');

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        // Retroactively apply the {provider} constraint to any route that was
        // already registered by an earlier-booting service provider (e.g.
        // Filament Socialite auto-discovered at position ~20 while this
        // RouteServiceProvider sits at ~45 in the boot order).
        $providerPattern = 'facebook|github|google|twitter|microsoft|azure|apple|linkedin|microsoft-graph|slack';
        foreach (Route::getRoutes() as $route) {
            if (in_array('provider', $route->parameterNames(), true)) {
                $route->where('provider', $providerPattern);
            }
        }
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('public-feedback', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
