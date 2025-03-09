<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting()
    {
        // // Global API Rate Limiting (Default: 60 requests per minute)
        // RateLimiter::for('api', function (Request $request) {
        //     return Limit::perMinute(60);
        // });

        // // Custom Rate Limit for Login (5 requests per minute per user)
        RateLimiter::for('login', function (Request $request) {
            dd($request);
            return Limit::perMinute(5)->by($request->email ?: $request->ip());
        });
    }
}
