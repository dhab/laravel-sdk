<?php
namespace DreamHack\SDK\Http;

use DreamHack\SDK\Http\Requests\ModelRequest;
use DreamHack\SDK\Http\Responses\ModelResponse;
use DreamHack\SDK\Http\Responses\InstantiableModelResponse;
use DreamHack\SDK\Validation\Rule;
use Illuminate\Http\Request;
use DB;
use Validator;

trait Resource
{
    public abstract static function getClass();

    public static function getDefaultRelations()
    {
        $class = static::getClass();
        return $class::getDefaultRelations();
    }

    /**
     * Get base request for fetching resource.
     */
    protected static function query()
    {
        $class = static::getClass();
        return $class::ordered()->with(static::getDefaultRelations());
    }

    protected static function findOrFail($id)
    {
        return static::query()->findOrFail($id);
    }

    protected static function getId()
    {
        $route = request()->route();
        return $route->parameter(end($route->parameterNames));
    }

    /**
     * Format response object
     */
    private static function response($data)
    {
        if (method_exists(__CLASS__, "getResponseClass")) {
            $response = static::getResponseClass();
            return new $response($data);
        } else {
            return new InstantiableModelResponse(static::getClass(), $data);
        }
    }

    private static function getRequiredFields()
    {
        $class = static::getClass();
        return $class::getRequiredFields();
    }
    private static function getFieldValidators()
    {
        $class = static::getClass();
        return $class::getFieldValidators();
    }

    protected static function getValidationRules($indicate_required = false)
    {
        $required = self::getRequiredFields();
        $rules = self::getFieldValidators();

        foreach ($rules as $key => $rule) {
            $val = "nullable";
            if (in_array($key, $required)) {
                if ($indicate_required) {
                    $val = "required";
                } else {
                    continue;
                }
            }
            if (is_array($rule)) {
                array_unshift($rules[$key], $val);
            } else {
                $rules[$key] = $val."|".$rule;
            }
        }
        foreach ($required as $field) {
            if (!isset($rules[$field])) {
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
    public function show()
    {
        $item = static::findOrFail(static::getId());
        return self::response($item);
    }

    /**
     * Create a new record of the resource.
     * @param  Request $request
     * @return DreamHack\SDK\Http\Responses\InstantiableModelResponse
     */
    public function store(Request $request)
    {
        $rules = static::getValidationRules(true);
        $this->validate($request, $rules);
        $validated = collect($request->all())->only(array_keys($rules))->all();
        $class = static::getClass();
        $item = (new $class())->fill($validated);
        if (!$item->save()) {
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
    public function update(Request $request)
    {
        $class = static::getClass();
        
        $item = $class::findOrFail(static::getId());

        $rules = static::getValidationRules();
        $this->validate($request, $rules);
        $validated = collect($request->all())->only(array_keys($rules))->all();
        $item->fill($validated);
        if (!$item->save()) {
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
    public function destroy(Request $request)
    {
        $class = static::getClass();

        $item = $class::findOrFail(static::getId());

        if ($item->delete()) {
            return response()->true();
        } else {
            return response()->false();
        }
    }
    
    /**
     * Delete multiple records of the resource
     * @param  Request $request
     * @return DreamHack\SDK\Http\Responses\BooleanResponse
     */
    public function batchDestroy(Request $request)
    {
        $class = static::getClass();
        $validator = Validator::make($request->all(), ["*" => [Rule::relation($class)]]);
        $validator->validate();
        DB::transaction(function () use ($class, $request) {
            $items = $request->all();
            foreach ($items as $id) {
                $item = $class::findOrFail($id);
                $item->delete();
            }
        });
        return response()->true();
    }

    /**
     * Batch update & create multiple records of the resource
     * @param  Request $request
     * @return DreamHack\SDK\Http\Responses\InstantiableModelResponse
     */
    public function batch(Request $request)
    {
        $class = static::getClass();
        $model = new $class;
        $createRules = static::getValidationRules(true);
        $updateRules = static::getValidationRules();
        if (!isset($updateRules[$model->getKeyName()])) {
            $updateRules[$model->getKeyName()] = [];
        }
        $updateRules[$model->getKeyName()][] = Rule::relation($class);

        $rules = [
            "create" => ["required", "array"],
            "update" => ["required", "array"],
        ];
        foreach ($createRules as $key => $rule) {
            $rules["create.*.".$key] = $rule;
        }
        foreach ($updateRules as $key => $rule) {
            $rules["update.*.".$key] = $rule;
        }
        $this->validate($request, $rules);

        $return = collect([]);
        DB::transaction(function () use ($class, $createRules, $updateRules, $request, $return, $model) {
            collect($request->get('create'))->each(function ($row) use ($createRules, $class, $return) {
                $validated = collect($row)->only(array_keys($createRules))->all();
                $item = (new $class())->fill($validated);
                if (!$item->save()) {
                    throw new Exception("Couldn't create model.");
                }
                $return->push($item);
            });
            collect($request->get('update'))->each(function ($row) use ($updateRules, $class, $return, $model) {
                $validated = collect($row)->only(array_keys($updateRules))->except($model->getKeyName())->all();
                $item = $class::findOrFail($row[$model->getKeyName()]);
                $item->fill($validated);
                if (!$item->save()) {
                    throw new Exception("Couldn't update model.");
                }
                $return->push($item);
            });
        });
        $return->each(function ($item) {
            $item->load(static::getDefaultRelations());
        });
        return self::response($return);
    }
}
