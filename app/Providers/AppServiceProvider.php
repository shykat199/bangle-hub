<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Services\FacebookConversionService;
use App\Services\TikTokConversionService;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('facebook-conversion', function ($app) {
            return new FacebookConversionService();
        });

        $this->app->singleton('tiktok-conversion', function ($app) {
            return new TikTokConversionService();
        });
    }

    public function boot()
    {
        Paginator::useBootstrap();

        // Who fires the Meta / TikTok browser events is an explicit choice, not a guess.
        // See App\Support\MetaEventSource for the three arrangements the shop owner
        // can pick between. $__hasGtm means "the blades must stay silent", which is
        // true for both GTM modes.
        // Gating on the mere presence of a GTM container was wrong: a container holding
        // only GA4 tags silently killed every Meta event with no way to notice.
        View::composer('*', function ($view) {
            static $flags = null;
            if ($flags === null) {
                $flags = ['__hasGtm' => false, '__ttFromGtm' => false, '__gtmId' => null];
                try {
                    $info = \App\Models\Information::first();
                    $legacyGtm = ($info && !empty($info->tracking_code)
                        && preg_match('/GTM-[A-Z0-9]+/', $info->tracking_code, $m));

                    $flags['__gtmId']     = $legacyGtm ? $m[0] : null;
                    $flags['__hasGtm']    = $info && $info->fb_event_source !== null
                        ? !\App\Support\MetaEventSource::themeFiresBrowserEvents()
                        : (bool) $legacyGtm;
                    $flags['__ttFromGtm'] = $info && $info->tt_event_source !== null
                        ? ($info->tt_event_source === 'gtm')
                        : false;
                } catch (\Throwable $e) {
                    // leave defaults
                }
            }
            $view->with($flags);
        });
    }
}