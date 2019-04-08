<?php

namespace DreamHack\SDK\Services;

use DreamHack\SDK\Facades\Guzzle;
use GuzzleHttp\Client;
use Log;

class DHID extends Client
{

    private $updates = [];

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
        return $request->input('token') ?? $request->bearerToken();
    }

    private function sendNotificationToSocket($to, $json, $token = null)
    {
        if (!$token) {
            $token = $this->getTokenFromRequest(request());
        }

        return $this->post('/1/socket/push/'.$to, [
            'auth' => null, // Override default config for DHID singleton
            'json' => $json,
            'headers' => [
                'Authorization' => $token, // Otherwise basic auth will be used
            ]
        ]);
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

    public function notifyChannel(string $channel, string $type, $data = null, array $options = [], $token = null)
    {
        return $this->sendNotificationToSocket("channel", [
            'options' => $options,
            'channel' => $channel,
            'type' => $type,
            'data' => $data,
        ], $token);
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
