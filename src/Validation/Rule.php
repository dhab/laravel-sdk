<?php

namespace DreamHack\SDK\Validation;

use Illuminate\Validation\Rule as IlluminateRule;

class Rule extends IlluminateRule
{
    protected static $languages = [];

    public static function relation($class, string $column = null)
    {
        return new Rules\Relation($class, $column);
    }

    public static function scope(\Illuminate\Database\Eloquent\Builder $scope, string $pk = '')
    {
        return new Rules\Scope($scope, $pk);
    }
}
