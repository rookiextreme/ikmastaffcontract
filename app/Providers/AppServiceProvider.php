<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laratrust\LaratrustFacade as Laratrust;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gunakan pagination Bootstrap 5 untuk semua halaman
        Paginator::useBootstrapFive();

        RedirectIfAuthenticated::redirectUsing(function () {
            $user = Auth::user();

            if ($user->hasRole('super-admin|admin')) {
                return route('admin.user.list', absolute: false);
            } elseif ($user->hasRole(['staff'])) {
                return route(
                    'staff.profile',
                    [
                        'user_id' => $user->id,
                        'page' => 'main',
                    ],
                    absolute: false
                );
            }
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}