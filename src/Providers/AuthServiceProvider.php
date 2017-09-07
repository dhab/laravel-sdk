<?php

namespace DreamHack\SDK\Providers;

use DreamHack\SDK\Auth\Guard;
use DreamHack\SDK\Auth\Provider;
use DreamHack\SDK\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Auth::extend('dhid', function ($app, $name, array $config) {
        //     return new Guard(Auth::createUserProvider($config['provider']), $app['request']);
        // });
        // Auth::provider('dhid', function ($app, array $config) {
        //     return new Provider();
        // });
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(User::class, function ($app) {
            return new User($app['request']);
        });
    }

    public function provides()
    {
        return [
            User::class,
        ];
    }
}
