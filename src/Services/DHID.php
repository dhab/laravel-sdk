<?php

namespace DreamHack\SDK\Services;
use DreamHack\SDK\Facades\Guzzle;
use Log;
class DHID {

    private $updates = [];

    /**
     * Queue an update for a specific route
     */
    public function updateRoute($route, $args = []) {
        $url = str_replace(url("/"), "", route($route, $args));
        return $this->update($url);
    }

    /**
     * Queue an update request for an API URI
     */
    public function update($url) {
        $this->updates[$url] = true;
        return true;
    }

    /**
     * Clear a queued an update for an API URI
     */
    public function clearUpdate($url) {
        $this->updates[$url] = false;
        return true;
    }

    /**
     * Send all queued updates to the socket API
     */
    public function sendUpdates() {
        foreach($this->updates as $url => $bool) {
            if($bool) {
                try {
                    Guzzle::request('GET', env('SOCKET_BASE_URL', 'http://localhost:8085/').'update'.$url);
                } catch(Exception $e) {
                    Log::info("Exception while updating url: ".$url.", ".$e->getMessage());
                }
            }
        }
    }
}
