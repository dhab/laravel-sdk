<?php

namespace DreamHack\SDK\Http\Responses;

class EmptyResponse
{
    private $value = null;
    
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return json_encode($this->value);
    }
}
