<?php

namespace DreamHack\SDK\Facades;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Illuminate\Http\Request
 */
class Manifest extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'annotations.manifest.scanner';
    }
}
