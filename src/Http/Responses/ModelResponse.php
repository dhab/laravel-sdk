<?php

namespace DreamHack\SDK\Http\Responses;
use Carbon;
use Exception;
use Illuminate\Support\Collection;

abstract class ModelResponse extends Response {
	protected static function getKeyByField() {
		return "id";
	}
	protected static function getGroupByField() {
		return false;
	}
	abstract protected static function getClass();
	abstract protected static function getFields();
    public function __construct($items = [], $status = false, $headers = false) {
        $single = false;
        $class = static::getClass();
        if($items instanceof $class) {
            $single = true;
            $items = collect([$items]);
        } elseif(! $items instanceof Collection) {
            throw new Exception("Invalid input");
        }
        $return = $this->castCollectionSubset($items, static::getFields(), static::getKeyByField(), $single?false:static::getGroupByField());
        if($single) {
            $return = $return->first();
        }

        parent::__construct($return, $status, $headers);
    }
}
