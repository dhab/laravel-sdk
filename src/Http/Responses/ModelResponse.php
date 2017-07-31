<?php

namespace DreamHack\SDK\Http\Responses;
use Carbon;
use Exception;
use Illuminate\Support\Collection;

abstract class ModelResponse extends InstantiableModelResponse {
    protected static function getFields() {
        return self::getClass()::getFields();
    }
	abstract protected static function getClass();
    public function __construct($items = [], $status = false, $headers = false) {
        parent::__construct(static::getClass(), $items, $status, $headers);
    }
}
