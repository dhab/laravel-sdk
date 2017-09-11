<?php

namespace DreamHack\SDK\Validation\Rules;

use Illuminate\Database\Eloquent\SoftDeletes;

class Scope
{
    protected $rule = 'in';
    protected $values;

    /**
     * Create a new in rule instance.
     *
     * @param  array  $values
     * @return void
     */
    public function __construct(\Illuminate\Database\Eloquent\Builder $scope, string $pk = '')
    {
        if ($pk === '') {
            $pk = $scope->getModel()->getQualifiedKeyName();
        }

        $this->values = $scope->pluck($pk)->toArray();
    }

    public function __toString()
    {
        return $this->rule.':'.implode(',', $this->values);
    }
}
