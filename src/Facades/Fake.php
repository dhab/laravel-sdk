<?php

namespace DreamHack\SDK\Facades;

use Illuminate\Support\Facades\Facade;

/**
 */
class Fake extends Facade
{
	static $faker;

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
		if ( !isset(self::$faker) )
			self::$faker = \Faker\Factory::create();
	
		return self::$faker;
    }
}
