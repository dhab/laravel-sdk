<?php

namespace DreamHack\SDK\Annotations;

use \Collective\Annotations\Routing\Annotations\Scanner as BaseRouteScanner;

class Scanner extends BaseRouteScanner {
	public function getManifest($skipClass = false) {
		$manifest = [
			"uuid" => env('API_UUID', '15372CD5-C9F7-4F5F-A3F2-9810AB55B9CF'),
			"prefix" => env('API_PREFIX', 'content'),
			"endpoints" => [],
		];
		$endpoints = $this->getEndpointsInClasses($this->getReader());
		foreach ($endpoints as $endpoint) {
			if($endpoint->reflection->name == $skipClass) {
				continue;
			}
			foreach($endpoint->getPaths() as $path) {
				$method = strtoupper($path->verb);
				$uriParts = explode('/', $path->path);
				
				$version = array_shift($uriParts);
				array_shift($uriParts);
				$url = str_replace("//", "/", "^/".implode($uriParts, '/')."/?$");
				$url = preg_replace("/{\w+}/", "*", $url);
				$route = [
					"method" => $method,
					"version" => $version,
					"url" => $url
				];
				if($method == 'GET' && isset($endpoint->skipAuth) && $endpoint->skipAuth) {
					$route['skipAuth'] = true;
				}
				if(isset($endpoint->cacheable) && $endpoint->cacheable) {
					$route['cacheable'] = true;
				}
				$manifest['endpoints'][] = $route;
				
				if($method == 'GET') {
					$route['method'] = 'HEAD';
					$manifest['endpoints'][] = $route;
				}
			}
		}
		
		return $manifest;
	}
}