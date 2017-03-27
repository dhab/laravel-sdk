<?php

namespace DreamHack\SDK\Services;
use GuzzleHttp\Client;
use Log;
class DHID {

    private $updates = [];
    private $guzzle;
    public function __construct(Client $guzzle) {
        $this->guzzle = $guzzle;
    }
    /**
     * Queue an update for a specific route
     */
    public function updateRoute($route, $args = []) {
        $url = str_replace(url("/"), "", route($route, $args));
        return $this->update($url);
    }

    /**
     * Send an update request for an API URI
     */
    public function update($url) {
        $this->updates[$url] = true;
        return true;
    }

    public function clearUpdate($url) {
        $this->updates[$url] = false;
        return true;
    }

    public function sendUpdates() {
        Log::info("Sending ".count($this->updates)." updates");
        foreach($this->updates as $url => $bool) {
            if($bool) {
                try {
                    $this->guzzle->request('GET', env('SOCKET_BASE_URL', 'http://localhost:8085/').'update'.$url);
                } catch(Exception $e) {
                    Log::info("Exception while updating url: ".$url.", ".$e->getMessage());
                }
            }
        }
    }
}
