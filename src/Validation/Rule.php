<?php

namespace DreamHack\SDK\Validation;

use Illuminate\Validation\Rule as IlluminateRule;

class Rule extends IlluminateRule
{
    protected static $languages = [];

    public static function relation($class) {
        return new Rules\Relation($class);
    }

}
