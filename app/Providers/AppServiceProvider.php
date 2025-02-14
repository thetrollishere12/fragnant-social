<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


use Laravel\Cashier\Cashier;



use App\Helper\AppHelper;

use Illuminate\Support\Facades\View;

use Illuminate\Support\Facades\Event;

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

        Cashier::calculateTaxes();

        // Register view composer
        $this->registerViewComposers();

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('tiktok', \SocialiteProviders\TikTok\Provider::class);
            $event->extendSocialite('facebook', \SocialiteProviders\Facebook\Provider::class);
            $event->extendSocialite('instagram', \SocialiteProviders\Instagram\Provider::class);
            $event->extendSocialite('youtube', \SocialiteProviders\YouTube\Provider::class);
            $event->extendSocialite('etsy', \SocialiteProviders\Etsy\Provider::class);
            $event->extendSocialite('shopify', \SocialiteProviders\Shopify\Provider::class);
        });

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
