<?php

namespace DreamHack\SDK\Http\Responses;
use Illuminate\Http\Response as IlluminateResponse;

class Response extends IlluminateResponse {
    public function __construct($content, $status = 200, $headers = []) {
    	parent::__construct($content, $status, $headers);
    	$this->header('Content-Type', 'application/json');
    }


	protected function collectionSubset($collection, $fields) {
		return $collection->map(function($row) use ($fields) {
			$ret = [];
			foreach($fields as $field) {
				$ret[$field] = $row->$field;
			}
			return $ret;
		});
	}
}
