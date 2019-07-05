<?php

namespace DreamHack\SDK\Services;

use DreamHack\SDK\Facades\Guzzle;
use GuzzleHttp\Client;
use Log;

class DHID extends Client
{

    private $updates = [];

    /**
     * Keep the users JWT token and do a request
     **/
    public function requestAsUser($method, $url, Array $params = []) {
        $params['auth'] = null; // Override default config for DHID singleton
        $params['headers'] = [
            'Authorization' => $this->getTokenFromRequest(request()), // Otherwise basic auth will be used
        ];

        return parent::request($method, $url, $params);
    }

    /**
     * Queue an update for a specific route
     */
    public function updateRoute($route, $args = [])
    {
        $url = str_replace(url("/"), "", route($route, $args));
        return $this->update($url);
    }

    /**
     * Queue an update request for an API URI
     */
    public function update($url)
    {
        $this->updates[$url] = true;
        return true;
    }

    /**
     * Clear a queued an update for an API URI
     */
    public function clearUpdate($url)
    {
        $this->updates[$url] = false;
        return true;
    }

    /**
     * Send all queued updates to the socket API
     */
    public function sendUpdates()
    {
        if (!config('dhid.socket_base_url')) {
            return;
        }

        foreach ($this->updates as $url => $bool) {
            if ($bool) {
                try {
                    Guzzle::request('GET', config('dhid.socket_base_url').'update'.$url);
                } catch (Exception $e) {
                    Log::info("Exception while updating url: ".$url.", ".$e->getMessage());
                }
            }
        }
    }

    // For convenience, get a useable token from the request.
    //
    // For all services but ID this should be in the Authorization-header, but
    // on ID (especially login/logout) it might be in a posted/getted parameter.
    private function getTokenFromRequest($request)
    {
        return $request->input('token') ?? 'Bearer '.$request->bearerToken();
    }

    private function sendNotificationToSocket($to, $json, $token = null)
    {
        if ($token === null) {
            $token = $this->getTokenFromRequest(request());
        }
        
        $params = [
            'json' => $json,

        ];

        if ($token) {
            $params['auth'] = null; // Override default config for DHID singleton
            $params['headers'] = [
                'Authorization' => $token, // Otherwise basic auth will be used
            ];
        }

        return $this->post('/1/socket/push/'.$to, $params);
    }

    public function notifyUser(string $user, string $type, $data = null, array $options = [], $token = null)
    {
        return $this->sendNotificationToSocket("user", [
            'options' => $options,
            'user' => $user,
            'type' => $type,
            'data' => $data,
        ], $token);
    }

    public function notifyToken(string $toToken, string $type, $data = null, array $options = [], $token = null)
    {
        return $this->sendNotificationToSocket("token", [
            'options' => $options,
            'token' => $toToken,
            'type' => $type,
            'data' => $data,
        ], $token);
    }

    public function notifySession(string $session, string $type, $data = null, array $options = [], $token = null)
    {
        return $this->sendNotificationToSocket("session", [
            'options' => $options,
            'session' => $session,
            'type' => $type,
            'data' => $data,
        ], $token);
    }

    public function notifyChannel(string $channel, string $type, $data = null, array $options = [], $token = false)
    {
        return $this->sendNotificationToSocket("channel", [
            'options' => $options,
            'channel' => $channel,
            'type' => $type,
            'data' => $data,
        ], $token);
    }

    public function Mfa(string $type, $userId, $title)
    {
        $request = [
            'json' => [
                'user' => $userId,
                'title' => $title,
                'action' => $type,
                'cookies' => $_COOKIE,
            ],
        ];

        $bearer = request()->bearerToken();
        if ($bearer) { // Forward the JWT token (used for every service except ID)
            $request['auth'] = null; // Override default config for DHID singleton
            $request['headers'] = [
                'Authorization' => 'Bearer '.$bearer, // Otherwise basic auth will be used
            ];
        } elseif ($_COOKIE) {
            $request['json']['cookies'] = $_COOKIE; // Forward the raw cookies (used as auth when the request comes ID)
        } else {
            throw new \Error("No authentication method found");
        }

        // Send the request to the socket service that will notify the user
        self::post('/1/socket/2fa', $request);
    }

    public function lookupUser($id)
    {
        $res = $this->get('/1/identity/users/'.$id);

        if ($res->getStatusCode() !== 200) {
            return false;
        }

        return json_decode($res->getBody(), true);
    }
}
