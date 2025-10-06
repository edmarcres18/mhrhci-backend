<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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
        // Trust Cloudflare / proxy headers so Laravel detects HTTPS correctly
        // and generates secure asset URLs and cookies.
        // '*' trusts all proxies within the container/orchestration network.
        Request::setTrustedProxies([
            '*',
        ], Request::HEADER_X_FORWARDED_FOR
           | Request::HEADER_X_FORWARDED_HOST
           | Request::HEADER_X_FORWARDED_PORT
           | Request::HEADER_X_FORWARDED_PROTO
           | Request::HEADER_X_FORWARDED_AWS_ELB);

        // Force https scheme and root URL in production to avoid mixed-content issues
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
            if ($root = config('app.url')) {
                URL::forceRootUrl($root);
            }
        }
    }
}
