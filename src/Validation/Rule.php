<?php

namespace DreamHack\SDK\Validation;

use Illuminate\Validation\Rule as IlluminateRule;

class Rule extends IlluminateRule
{
    protected static $languages = [];

    public static function relation($class)
    {
        return new Rules\Relation($class);
    }

    public static function scope(\Illuminate\Database\Eloquent\Builder $scope, string $pk = '')
    {
        return new Rules\Scope($scope, $pk);
    }
}
