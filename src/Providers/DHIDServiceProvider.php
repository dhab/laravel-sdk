<?php

namespace DreamHack\SDK\Providers;

use GuzzleHttp\Client;
use DreamHack\SDK\Services\DHID;
use Illuminate\Support\ServiceProvider;

class DHIDServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/dhid.php' => config_path('dhid.php'),
        ]);
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(DHID::class, function ($app) {
            $config = [
                'base_uri' => config('config.api_base_url'),
            ];

            if (config('dhid.api_client_id') && config('dhid.api_secret')) {
                $config['auth'] = [
                    config('dhid.api_client_id'),
                    hash_hmac('sha256', config('dhid.api_client_id'), config('dhid.api_secret'))
                ];
            }
            return new DHID($config);
        });
        $client = $this->app->make(DHID::class);
        $this->app->terminating(function () use ($client) {
            $client->sendUpdates();
        });
    }

    public function provides()
    {
        return [
            DHID::class
        ];
    }
}
