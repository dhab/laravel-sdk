<?php

namespace DreamHack\SDK\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    protected $orderBy;
    protected $orderDirection = 'ASC';

    public static function getTypeName()
    {
        return array_slice(explode('\\', static::class), -1)[0];
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy($this->orderBy?:"created_at", 'asc');
    }


    public static function getKeyByField()
    {
        return "id";
    }
    public static function getGroupByField()
    {
        return false;
    }
}
