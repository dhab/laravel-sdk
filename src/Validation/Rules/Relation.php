<?php

namespace DreamHack\SDK\Validation\Rules;

use Illuminate\Validation\Rules\Exists;
use Illuminate\Database\Eloquent\SoftDeletes;

class Relation extends Exists
{
    /**
     * The name of the class.
     */
    protected $class;


    /**
     * Create a new in rule instance.
     *
     * @param  array  $values
     * @return void
     */
    public function __construct(string $class)
    {
        $this->class = $class;

        $model = new $class;

        $table = $model->getTable();
        $column = $model->getKeyName();
        parent::__construct($table, $column);
        if (method_exists($model, 'getDeletedAtColumn')) {
            $this->whereNull($model->getDeletedAtColumn());
        }
    }
}
