<?php
namespace DreamHack\SDK\Http;


trait Resource {
	/**
	 * Get base request for fetching resource.
	 */
	private static function query() {
		$class = static::getClass();
		return $class::with(static::getRelations());
	}

	/**
	 * Format response object
	 */
	private static function response($data) {
		$response = static::getResponseClass();
		return new $response($data);
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