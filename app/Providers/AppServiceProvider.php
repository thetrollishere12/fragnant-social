<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\UserMedia;
use App\Observers\UserMediaObserver;

use Laravel\Cashier\Cashier;



use App\Helper\AppHelper;

use Illuminate\Support\Facades\View;

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

        // Register view composer
        $this->registerViewComposers();

    }


    protected function registerViewComposers(): void
    {
        // Apply data to all views
        View::composer('*', function ($view) {
            $blocks = AppHelper::getWebsiteBlocks();
            $view->with('blocks', $blocks);
        });
    }

}
