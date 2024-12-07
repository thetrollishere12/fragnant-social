<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\UserMedia;
use App\Observers\UserMediaObserver;

use Laravel\Cashier\Cashier;

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
        UserMedia::observe(UserMediaObserver::class);
        Cashier::calculateTaxes();
    }
}
