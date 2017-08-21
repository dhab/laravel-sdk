<?php
namespace DreamHack\SDK\Http;

use DreamHack\SDK\Http\Requests\ModelRequest;
use DreamHack\SDK\Http\Responses\ModelResponse;
use DreamHack\SDK\Http\Responses\InstantiableModelResponse;
use Illuminate\Http\Request;

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
    protected static function query() {
        $class = static::getClass();
        return $class::ordered()->with(static::getDefaultRelations());
    }

    protected static function findOrFail($id) {
        return static::query()->findOrFail($id);
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

    private static function getRequiredFields() {
        $class = static::getClass();
        return $class::getRequiredFields();
    }
    private static function getFieldValidators() {
        $class = static::getClass();
        return $class::getFieldValidators();
    }

    protected static function getValidationRules($indicate_required = false) {
        $required = self::getRequiredFields();
        $rules = self::getFieldValidators();

        foreach ($rules as $key => $rule) {
            $val = "nullable";
            if(in_array($key, $required)) {
                if($indicate_required) {
                    $val = "required";
                } else {
                    continue;
                }
            }
            if(is_array($rule)) {
                array_unshift($rules[$key], $val);
            } else {
                $rules[$key] = $val."|".$rule;
            }
        }
        foreach($required as $field) {
            if(!isset($rules[$field])) {
                $rules[$field] = "required";
            }
        }
        return $rules;
    }

    /**
     * Display a listing of the resource.
     * @return DreamHack\SDK\Http\Responses\InstantiableModelResponse
     */
    public function index()
    {
        $items = static::query()->get();
        return self::response($items);
    }
    /**
     * Display the specified resource.
     * @param  uuid  $id
     * @return DreamHack\SDK\Http\Responses\InstantiableModelResponse
     */
    public function show($id)
    {
        $item = static::findOrFail($id);
        return self::response($item);
    }

    /**
     * Create a new record of the resource.
     * @param  Request $request
     * @return DreamHack\SDK\Http\Responses\InstantiableModelResponse
     */
    public function store(Request $request) {
        $rules = static::getValidationRules(true);
        $this->validate($request, $rules);
        $validated = collect($request->all())->only(array_keys($rules))->all();
        $class = static::getClass();
        $item = (new $class())->fill($validated);
        if(!$item->save()) {
            // handle db error
        }
        $item->load(static::getDefaultRelations());
        return self::response($item);
    }

    /**
     * Update an existing record of the resource
     * @param  Request $request
     * @param  string $id
     * @return DreamHack\SDK\Http\Responses\InstantiableModelResponse
     */
    public function update(Request $request, $id) {
        $class = static::getClass();
        
        $item = $class::findOrFail($id);

        $rules = static::getValidationRules();
        $this->validate($request, $rules);
        $validated = collect($request->all())->only(array_keys($rules))->all();
        $item->fill($validated);
        if(!$item->save()) {
            // handle db error
        }
        $item->load(static::getDefaultRelations());
        return self::response($item);
    }

    /**
     * Delete a record of the resource
     * @param  Request $request
     * @param  string $id
     * @return DreamHack\SDK\Http\Responses\BooleanResponse
     */
    public function destroy(Request $request, $id) {
        $class = static::getClass();

        $item = $class::findOrFail($id);

        if($item->delete()) {
            return response()->true();
        } else {
            return response()->false();
        }
    }
}