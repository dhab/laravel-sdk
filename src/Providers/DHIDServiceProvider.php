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
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(DHID::class, function ($app) {
            $config = [
                'base_uri' => env('API_BASE_URL', 'https://api.dreamhack.com'),
            ];

            if (env('API_CLIENT_ID') && env('API_SECRET')) {
                $config['auth'] = [
                    env('API_CLIENT_ID'),
                    hash_hmac('sha256', env('API_CLIENT_ID'), env('API_SECRET'))
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
