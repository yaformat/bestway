<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageManager::class, function () {
            return new ImageManager(new GdDriver());
            // Или: return new ImageManager(new ImagickDriver());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!\App::environment('local')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
