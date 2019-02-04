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

    public function notifyUser(string $userId, string $type, $data = null, array $options = [])
    {
        return $this->post('/1/socket/push', ['form_params' => array_merge($options, [
          'userId' => $userId,
          'type' => $type,
          'data' => $data,
        ])]);
    }

    public function notifyToken(string $token, string $type, $data = null, array $options = [])
    {
        return $this->post('/1/socket/push', ['form_params' => array_merge($options, [
          'token' => $token,
          'type' => $type,
          'data' => $data,
        ])]);
    }

    public function notifySession(string $session, string $type, $data = null, array $options = [])
    {
        return $this->post('/1/socket/push', ['form_params' => array_merge($options, [
          'session' => $session,
          'type' => $type,
          'data' => $data,
        ])]);
    }

    public function notifyChannel(string $channel, string $type, $data = null, array $options = [])
    {
        return $this->post('/1/socket/push', ['form_params' => array_merge($options, [
          'channel' => $channel,
          'type' => $type,
          'data' => $data,
        ])]);
    }
}
