<?php

namespace DreamHack\SDK\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{

    /**
     * Create a new exception instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function __construct($text = "Unauthorized")
    {
        parent::__construct($text);
    }

    public function getStatusCode()
    {
        return 401;
    }
}
