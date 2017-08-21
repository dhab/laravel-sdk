<?php

namespace DreamHack\SDK\Providers;

use Illuminate\Support\ServiceProvider;
use Gettext\Languages\Language;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Exception;

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
        Validator::extend('uuid', function($attribute, $value, $parameters, $validator) {
            try {
                $uuid = Uuid::fromString($value);
            } catch(Exception $e) {
                return false;
            }
            return true;
        });
    }

    /**
     * Register any application services.
     */
    public function register() { }
}
