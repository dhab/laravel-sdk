<?php

namespace DreamHack\SDK\Http\Responses;

use DreamHack\SDK\Facades\Fake;

/**
 * Returns the manifest containing information about all available routes. This endpoint is primarily used by the loadbalancer.
 *
 * @property string $uuid The uniqe identifier of the service
 * @property string $prefix URI prefix used before each endpoint
 * @property ManifestEndpoint[] $endpoints
 **/

class Manifest extends Response
{
    
    public static function fake()
    {
        return [
            'uuid' => Fake::uuid(),
            'prefix' => Fake::domainWord(),
            'endpoints' => [
                ManifestEndpoint::fake(),
                ManifestEndpoint::fake()
            ]
        ];
    }
}
