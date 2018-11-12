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

    /**
     * Append a row in user_logs in ID, type is a unique identifier for your
     * thing, and data is serialized to json before it's saved.
     */
    public static function Userlog($type, $data)
    {
        $request = request();
        $auth = $request->header('Authorization');

        // Sanity-check, are we even logged in?
        if (!$auth) {
            return;
        }

        return Guzzle::request(
            'POST',
            self::getUrl().'/1/identity/users/log',
            [
                "json" => [
                    "type" => $type,
                    "data" => $data,
                ],
                "headers" => [
                    "Authorization" => $auth,
                ],
            ]
        );
    }

    /**
     * Maybe notify a user for getting an achievement
     */
    public static function AchievementNotify($userAchievement)
    {
        $request = request();
        $auth = $request->header('Authorization');

        // Sanity-check, are we even logged in?
        if (!$auth) {
            return;
        }

        $userAchievement->load('achievement');

        return Guzzle::request(
            'POST',
            self::getUrl().'/1/content/notifications/achievement',
            [
                "json" => [
                    "userAchievement" => $userAchievement,
                ],
                "headers" => [
                    "Authorization" => $auth,
                ],
            ]
        );
    }
}
