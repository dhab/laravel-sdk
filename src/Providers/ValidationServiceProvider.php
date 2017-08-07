<?php

namespace DreamHack\SDK\Providers;

use Illuminate\Support\ServiceProvider;
use Gettext\Languages\Language;

class ValidationServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        \Gettext\Languages\Language::getAll();
    }

    /**
     * Register any application services.
     */
    public function register() { }
}
