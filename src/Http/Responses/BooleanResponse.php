<?php

namespace DreamHack\SDK\Http\Responses;

use Validator;

/**
 * Input validation failed, returns the first error 
 *
 * @property string $key Contains the row of the error
 * @property string $error The error message
 **/

class BooleanResponse extends Response {
    function __construct(bool $response = false, $status = 200, $headers = []) {
        return parent::__construct(json_encode($response), $status, $headers);
    }
}

