<?php
namespace DreamHack\SDK\Http;

use DreamHack\SDK\Http\Requests\ModelRequest;
use DreamHack\SDK\Http\Responses\ModelResponse;
use DreamHack\SDK\Http\Responses\InstantiableModelResponse;

trait Resource {
    public abstract static function getClass();

    public static function getDefaultRelations() {
        $class = static::getClass();
        return $class::getDefaultRelations();
    }

    // public abstract static function getResponseClass();
    public abstract static function getRequestClass();

    /**
     * Get base request for fetching resource.
     */
    private static function query() {
        $class = static::getClass();
        return $class::ordered()->with(static::getDefaultRelations());
    }

    /**
     * Format response object
     */
    private static function response($data) {
        if(method_exists(__CLASS__, "getResponseClass")) {
            $response = static::getResponseClass();
            return new $response($data);
        } else {
            return new InstantiableModelResponse(static::getClass(), $data);
        }
    }

    /**
     * Display a listing of the resource.
     * @return DreamHack\SDK\Http\Responses\ModelResponse[]
     */
    public function index()
    {
        $items = self::query()->get();
        return self::response($items);
    }
    /**
     * Display the specified resource.
     * @param  uuid  $id
     * @return DreamHack\SDK\Http\Responses\ModelResponse
     */
    public function show($id)
    {
        $item = self::query()->findOrFail($id);
        return self::response($item);
    }
}