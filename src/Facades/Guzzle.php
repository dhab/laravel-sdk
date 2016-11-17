<?php

namespace DreamHack\SDK\Facades;

use Illuminate\Support\Facades\Facade;
use GuzzleHttp\Client;

/**
 * @see \Illuminate\Http\Request
 */
class Guzzle extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}
