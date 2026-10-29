<?php

namespace App\Providers;

use App\Models\Warga;
use App\Observers\WargaObserver;
use Illuminate\Support\ServiceProvider;

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
        Warga::observe(WargaObserver::class);
    }
}
