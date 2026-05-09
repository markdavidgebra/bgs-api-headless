<?php

namespace App\Providers;

use App\Support\SiteFooterConfig;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
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
        // Legacy/default frontend components (e.g. <x-strickyHeader />, <x-mobileMenu />)
        Blade::anonymousComponentPath(resource_path('views/frontend/components'));

        View::composer('frontend.layouts.footer', function ($view) {
            $view->with('siteFooter', SiteFooterConfig::get());
        });
    }
}
