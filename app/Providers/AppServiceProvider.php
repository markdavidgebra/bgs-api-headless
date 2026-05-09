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

        // Frontend anonymous Blade components
        // - Site components remain usable without a prefix (e.g. <x-head />, <x-styles.head-home />)
        // - Optional prefixes allow explicit usage if needed (e.g. <x-site.head />)
        Blade::anonymousComponentPath(resource_path('views/frontend/components/site'));
        Blade::anonymousComponentPath(resource_path('views/frontend/components/site'), 'site');

        // Dashboard components can be referenced via <x-dashboard.* />
        Blade::anonymousComponentPath(resource_path('views/frontend/components/dashboard'), 'dashboard');

        View::composer('frontend.layouts.footer', function ($view) {
            $view->with('siteFooter', SiteFooterConfig::get());
        });
    }
}
