<?php

namespace DreamHack\SDK\Annotations;
use App;
use Cache;
use \Collective\Annotations\Routing\Annotations\Scanner as BaseRouteScanner;

class Scanner extends BaseRouteScanner {
	private $cache_key = "manifest";
	public function getManifest($skipClass = false) {
		$manifest = false;
		if(!App::environment('local', 'staging') && Cache::has($this->cache_key)) {
			$manifest = Cache::get($this->cache_key);
			// $manifest = json_decode($manifest);
		}
		if(!$manifest) {
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
					if(!isset($path->version)) {
						continue;
					}
					$version = array_shift($uriParts);
					array_shift($uriParts);
					$url = str_replace("//", "/", "^/".implode($uriParts, '/')."/?$");
					$url = preg_replace("/{\w+}/", "*", $url);
					$route = [
						"method" => $method,
						"version" => $version,
						"url" => $url
					];
					if(isset($endpoint->skipAuth) && $endpoint->skipAuth) {
						$route['skipAuth'] = true;
					}
					if($method == 'GET' && isset($endpoint->cacheable) && $endpoint->cacheable) {
						$route['cacheable'] = true;
					}
					$manifest['endpoints'][] = $route;
					
					if($method == 'GET') {
						$route['method'] = 'HEAD';
						$manifest['endpoints'][] = $route;
					}
				}
			}
			
			// Sort the endpoints by url-length, longest first.
			usort($manifest['endpoints'], function($a, $b) {
	    		return (strlen($a['url']) > strlen($b['url'])) ? -1 : 1;
			});
			if(!App::environment('local', 'staging'))
				Cache::put($this->cache_key, $manifest, 5);
		}
		return $manifest;
	}
}