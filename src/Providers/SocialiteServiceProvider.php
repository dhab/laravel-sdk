<?php

namespace DreamHack\SDK\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;

class SocialiteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register database migrations
        $this->loadMigrationsFrom(__DIR__.'/../../migrations/socialite');

        // Register routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/socialite.php');

        // Register views
        $this->loadViewsFrom(__DIR__.'/../../views/', 'DHID');
        $this->publishes([
            __DIR__.'/../../views/' => resource_path('views/vendor/dhid'),
        ]);

        // Make sure socialite is registerd
        $this->app->register(\Laravel\Socialite\SocialiteServiceProvider::class);

        // And add the Socialite alias
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Socialite', \Laravel\Socialite\Facades\Socialite::class);

        // Register our DHID SocialiteProvider
        $socialite = $this->app->make(\Laravel\Socialite\Contracts\Factory::class);
        $socialite->extend(
            'dhid',
            function ($app) use ($socialite) {
                $config = [
                    'client_id' => config('dhid.dhid_client'),
                    'client_secret' => config('dhid.dhid_secret'),
                    'redirect' => config('dhid.dhid_redirect'),
                ];
                return $socialite->buildProvider(SocialiteProvider::class, $config);
            }
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
