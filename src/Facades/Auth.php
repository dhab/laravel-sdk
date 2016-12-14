<?php

namespace DreamHack\SDK\Facades;

use Illuminate\Support\Facades\Facade;
use DreamHack\SDK\Auth\User;

/**
 * @see \Illuminate\Http\Request
 */
class Auth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return User::class;
    }
}
