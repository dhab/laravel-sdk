<?php

namespace DreamHack\SDK\Http\Responses;

class Response extends \Illuminate\Http\Response {


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
