<?php

namespace DreamHack\SDK\Providers;

use Illuminate\Support\ServiceProvider;

class FakerServiceProvider extends ServiceProvider
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
		$this->app->singleton(\Faker\Generator::class, function ($app) {
            return \Faker\Factory::create();
        });
    }

    public function provides() {
        return [
            \Faker\Generator::class,
        ];
    }
}
