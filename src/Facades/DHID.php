<?php

namespace DreamHack\SDK\Facades;

use DreamHack\SDK\Services\DHID as Service;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Illuminate\Http\Request
 */
class DHID extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Service::class;
    }
}
