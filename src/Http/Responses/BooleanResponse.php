<?php

namespace DreamHack\SDK\Http\Responses;

/**
 * A flat boolean response indicating status of request.
 **/

class BooleanResponse extends Response
{
    public function __construct(bool $response = false, $status = 200, $headers = [])
    {
        return parent::__construct(json_encode($response), $status, $headers);
    }
}
