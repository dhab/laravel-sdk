<?php

namespace DreamHack\SDK\Providers;

use Response;
use DreamHack\SDK\Auth\User;
use DreamHack\SDK\Http\Responses\BooleanResponse;
use Illuminate\Support\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        Response::macro('bool', function(bool $value) {
            return new BooleanResponse($value);
        });
        Response::macro('boolean', function(bool $value) {
            return response()->bool($value);
        });
        Response::macro('true', function() {
            return response()->bool(true);
        });
        Response::macro('false', function() {
            return response()->bool(false);
        });
    }

    /**
     * Register any application services.
     */
    public function register() { }
}
