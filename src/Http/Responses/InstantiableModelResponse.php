<?php

namespace DreamHack\SDK\Http\Responses;
use Carbon;
use Exception;
use Illuminate\Support\Collection;

class InstantiableModelResponse extends Response {
    protected static function getKeyByField() {
        return "id";
    }
    protected static function getGroupByField() {
        return false;
    }
    protected function formatReturn($class, $items, $fields) {
        $single = false;
        if($items instanceof $class) {
            $single = true;
            $items = collect([$items]);
        } elseif(! $items instanceof Collection) {
            throw new Exception("Invalid input");
        }
        $return = $this->castCollectionSubset($items, $fields, static::getKeyByField(), $single?false:static::getGroupByField());
        if($single) {
            $return = $return->first();
        }
        return $return;
    }

    public function __construct($class, $items = [], $status = false, $headers = false) {
        if(method_exists($this, 'getFields')) {
            $fields = static::getFields();
        } else {
            $fields = $class::getFields();
        }
        parent::__construct($this->formatReturn($class, $items, $fields), $status, $headers);
    }
}
