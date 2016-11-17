<?php

namespace DreamHack\SDK\Exceptions;

use Exception;

class BadGatewayException extends Exception
{

    /**
     * Create a new exception instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Bad Gateway.');
    }

    public function getStatusCode() {
        return 502;
    }
}
