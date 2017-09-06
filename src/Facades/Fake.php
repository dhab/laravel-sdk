<?php

namespace DreamHack\SDK\Facades;

use Illuminate\Support\Facades\Facade;

/**
 */
class Fake extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Faker\Generator::class;
    }
}
