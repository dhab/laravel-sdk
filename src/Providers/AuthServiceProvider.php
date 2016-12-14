<?php

namespace DreamHack\SDK\Providers;

use DreamHack\SDK\Auth\User;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() { }

    /**
     * Register any application services.
     */
    public function register() {
        $this->app->singleton(User::class, function ($app) {
            return new User($app['request']);
        });
    }

    public function provides() {
        return [
            User::class,
        ];
    }
}
