<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use App\Http\Middleware\AdminMiddleware;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ✅ Setup reset password link for frontend
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return url("http://localhost:5173/reset-password?token=$token&email=" . urlencode($user->email));
        });

        // ✅ Register route groups (web.php, api.php, etc.)
        Route::middleware('web')
            ->group(base_path('routes/web.php'));

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        // ✅ Register route-specific middleware like 'admin'
        Route::aliasMiddleware('admin', AdminMiddleware::class);
    }
}
