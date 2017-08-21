<?php

namespace DreamHack\SDK\Providers;

use Illuminate\Support\ServiceProvider;
use Gettext\Languages\Language;
use Illuminate\Support\Facades\Validator;


class ValidationServiceProvider extends ServiceProvider
{
    protected static $languages = [];
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {

        foreach(\Gettext\Languages\Language::getAll() as $lang) {
            static::$languages[$lang->id] = $lang->name;
        }
        Validator::extend('language', function ($attribute, $value, $parameters, $validator) {
            return isset(static::$languages[$value]);
        });
    }

    /**
     * Register any application services.
     */
    public function register() { }
}
