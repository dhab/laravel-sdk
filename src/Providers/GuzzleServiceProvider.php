<?php

namespace DreamHack\SDK\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class GuzzleServiceProvider extends ServiceProvider
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
        $this->app->singleton(Client::class, function ($app) {
            $config = isset($app['config']['guzzle']) ? $app['config']['guzzle'] : [];
            return new Client($config);
        });
    }

    public function provides()
    {
        return [
            Client::class,
        ];
    }
}
