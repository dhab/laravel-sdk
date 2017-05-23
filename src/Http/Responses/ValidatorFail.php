<?php

namespace DreamHack\SDK\Http\Responses;

use Validator;

/**
 * Input validation failed, returns the first error 
 *
 * @property string $key Contains the row of the error
 * @property string $error The error message
 **/

class ValidatorFail extends Response {
    function __construct(Valdiator $validator = null, $key = null) {
        if ( !$validator ) 
            return parent::__construct();

        return parent::__construct(
            [
                'key' => $key,
                'error' => $validator->errors()->getMessages()
            ],
            422
        );
    }
}

