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
    
    static function fake()
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

/**
 * @property string $method GET|POST|PUT|DELETE
 * @property string $version Version number for the endpoint, normaly a single number like 1 or 2...
 * @property string $url Regular expression describing the endpoint
 * @property boolean $skipAuth? If true, allow anonymous access and dont go thru the auth process
 * @property boolean $cachable? If true, enables caching in the service proxy.
 **/
class ManifestEndpoint extends Response
{

    static function fake()
    {
        return [
            'method' => Fake::randomElement(['GET','POST','PUT','DELETE']),
            'version' => Fake::randomDigit(),
            'url' => '^/event/*/tickets/*/remove_booking/?$'
        ];
    }
}
