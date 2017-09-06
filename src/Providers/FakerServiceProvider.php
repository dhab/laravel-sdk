<?php

namespace DreamHack\SDK\Providers;

use Faker\Generator;
use Faker\Factory;
use Illuminate\Support\ServiceProvider;

class FakerServiceProvider extends ServiceProvider
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
        $this->app->singleton(Generator::class, function ($app) {
            return Factory::create();
        });
    }

    public function provides()
    {
        return [
            Generator::class,
        ];
    }
}
