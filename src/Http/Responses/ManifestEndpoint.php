<?php

namespace DreamHack\SDK\Http\Responses;

use DreamHack\SDK\Facades\Fake;

/**
 * @property string $method GET|POST|PUT|DELETE
 * @property string $version Version number for the endpoint, normaly a single number like 1 or 2...
 * @property string $url Regular expression describing the endpoint
 * @property boolean $skipAuth? If true, allow anonymous access and dont go thru the auth process
 * @property boolean $cachable? If true, enables caching in the service proxy.
 **/
class ManifestEndpoint extends Response
{

    public static function fake()
    {
        return [
            'method' => Fake::randomElement(['GET','POST','PUT','DELETE']),
            'version' => Fake::randomDigit(),
            'url' => '^/event/*/tickets/*/remove_booking/?$'
        ];
    }
}
