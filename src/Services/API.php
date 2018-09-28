<?php

namespace DreamHack\SDK\Services;

use DreamHack\SDK\Facades\Guzzle;
use GuzzleHttp\Client;
use Log;

/**
 *
 *     Simple wrappers around our own API
 *
 */
class API extends Client
{
    private static function getUrl()
    {
        return config('dhid.api_internal_base_url');
    }

    /**
     * Ask ID API for names of a bunch of users
     */
    public static function lookupMultipleUsers($ids)
    {
        $res = Guzzle::request(
            'POST',
            self::getUrl().'/1/identity/users/multiple/public',
            [
                "json" => ["ids" => $ids],
            ]
        );

        if ($res->getStatusCode() !== 200) {
            // Throw something?
            return [];
        }

        return json_decode($res->getBody());
    }
}
