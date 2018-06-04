<?php
namespace DreamHack\SDK\Http;

use Carbon\Carbon;
use DreamHack\SDK\Http\Requests\ModelRequest;
use DreamHack\SDK\Http\Responses\ModelResponse;
use DreamHack\SDK\Http\Responses\InstantiableModelResponse;
use DreamHack\SDK\Validation\Rule;
use Illuminate\Contracts\Auth\Access\Gate;
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

        if (in_array('Spatie\EloquentSortable\Sortable', class_implements($class))) {
            return $class::ordered()->with(static::getDefaultRelations());
        }

        return $class::with(static::getDefaultRelations());
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
        if (method_exists(static::class, "getResponseClass")) {
            $response = static::getResponseClass();
            $response = new $response($data);
        } else {
            $response = new InstantiableModelResponse(static::getClass(), $data);
        }

        $class = static::getClass();
        $gate = app(Gate::class);
        if ($gate->getPolicyFor($class)) {
            $gateWithUser = $gate->forUser(request()->user());
            $headers = [
                'X-Permissions' => json_encode([
                    'create' => $gateWithUser->check('create', [$class]),
                    'update' => $gateWithUser->check('update', [$class]),
                    'delete' => $gateWithUser->check('delete', [$class]),
                ])
            ];
            return $response->withHeaders($headers);
        }
    }

    protected static function fillDefaultValues($values, $obj = false, $rules = [])
    {
        foreach ($rules as $key => $rule) {
            if (!isset($values[$key])) {
                continue;
            }
            $isDate = false;
            foreach (is_array($rule)?$rule:[$rule] as $r) {
                if (is_string($r) && strpos($r, 'date') !== false) {
                    $values[$key] = Carbon::parse($values[$key])->timezone('UTC');
                }
            }
        }
        return $values;
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

    protected static function formatValidationRules($rules = [], $required = [], $indicate_required = false)
    {
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

    protected static function getValidationRules($indicate_required = false)
    {
        $required = self::getRequiredFields();
        $rules = self::getFieldValidators();

        return self::formatValidationRules($rules, $required, $indicate_required);
    }

    /**
     * Display a listing of the resource.
     * @return DreamHack\SDK\Http\Responses\InstantiableModelResponse
     */
    public function index()
    {
        if (app(Gate::class)->getPolicyFor(static::getClass())) {
            $this->authorize('view', [static::getClass()]);
        }

        $q = static::query();
        if (method_exists(static::class, "shouldPaginate") && static::shouldPaginate()) {
            $items = $q->paginate(max(min((int)(request()->get('per_page') ?? 100), 1000), 1));
        } else {
            $items = $q->get();
        }
        
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

        if (app(Gate::class)->getPolicyFor(static::getClass())) {
            $this->authorize('view', $item);
        }

        return self::response($item);
    }

    /**
     * Create a new record of the resource.
     * @param  Request $request
     * @return DreamHack\SDK\Http\Responses\InstantiableModelResponse
     */
    public function store(Request $request)
    {
        $class = static::getClass();
        $rules = static::getValidationRules(true);
        $this->validate($request, $rules);
        $validated = collect($request->all())->only(array_keys($rules))->all();
        $validated = static::fillDefaultValues($validated, false, $rules);
        $item = (new $class())->fill($validated);
        if (app(Gate::class)->getPolicyFor($class)) {
            $this->authorize('create', $item);
        }
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
        $item = static::findOrFail(static::getId());

        $rules = static::getValidationRules();
        $this->validate($request, $rules);
        $validated = collect($request->all())->only(array_keys($rules))->all();
        $validated = static::fillDefaultValues($validated, $item, $rules);
        $item->fill($validated);
        if (app(Gate::class)->getPolicyFor(static::getClass())) {
            $this->authorize('update', $item);
        }
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
        $item = static::findOrFail(static::getId());
        if (app(Gate::class)->getPolicyFor(static::getClass())) {
            $this->authorize('delete', $item);
        }

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
        $items = $request->all();
        foreach ($items as $key => $id) {
            $items[$key] = static::findOrFail($id);
            if (app(Gate::class)->getPolicyFor($class)) {
                $this->authorize('delete', $items[$key]);
            }
        }
        DB::transaction(function () use ($class, $request) {
            foreach ($items as $item) {
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
        $keyName = $model->getKeyName();
        $createRules = static::getValidationRules(true);
        $updateRules = static::getValidationRules();
        if (!isset($updateRules[$keyName])) {
            $updateRules[$keyName] = [];
        }
        $updateRules[$keyName][] = Rule::relation($class);

        $rules = [
            "create" => ["array"],
            "update" => ["array"],
        ];
        foreach ($createRules as $key => $rule) {
            $rules["create.*.".$key] = $rule;
        }
        foreach ($updateRules as $key => $rule) {
            $rules["update.*.".$key] = $rule;
        }
        $this->validate($request, $rules);
        $creates = [];
        foreach ($request->get('create') as $row) {
            $validated = collect($row)->only(array_keys($createRules))->all();
            $validated = static::fillDefaultValues($validated, false, $createRules);
            $item = (new $class())->fill($validated);
            if (app(Gate::class)->getPolicyFor($class)) {
                $this->authorize('create', $item);
            }
            $creates[] = $item;
        }

        $updates = [];
        foreach ($request->get('update') as $row) {
            $item = static::findOrFail($row[$keyName]);
            $validated = collect($row)->only(array_keys($updateRules))->except($keyName)->all();
            $validated = static::fillDefaultValues($validated, $item, $updateRules);
            $item->fill($validated);
            if (app(Gate::class)->getPolicyFor($class)) {
                $this->authorize('update', $item);
            }
            $updates[$item->$keyName] = $item;
        }

        $return = collect([]);
        DB::transaction(function () use ($creates, $updates, $return, $keyName) {
            foreach ($creates as $item) {
                if (!$item->save()) {
                    throw new Exception("Couldn't create model.");
                }
                $return->push($item);
            }
            foreach ($updates as $item) {
                if (!$item->save()) {
                    throw new Exception("Couldn't update model #".($item->$keyName).".");
                }
                $return->push($item);
            }
        });
        $return->each(function ($item) {
            $item->load(static::getDefaultRelations());
        });
        return self::response($return);
    }
}
