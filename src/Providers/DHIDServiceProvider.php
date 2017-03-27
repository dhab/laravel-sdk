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
    public function boot() {

    }

    /**
     * Register any application services.
     */
    public function register() {
        $client = $this->app->make(DHID::class);
        $this->app->instance(DHID::class, $client);
        $this->app->terminating(function() use ($client) {
            $client->sendUpdates();
        });
    }

    public function provides() {
        return [
            DHID::class
        ];
    }
}
