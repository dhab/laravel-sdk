<?php

namespace DreamHack\SDK\Annotations;
use App;
use Cache;
use URL;
use DreamHack\SDK\Documentation\Raml;
use Collective\Annotations\Routing\Annotations\Scanner as BaseRouteScanner;
use Collective\Annotations\Routing\Annotations\ResourceEndpoint;

class Scanner extends BaseRouteScanner {
    private $cache_key = "manifest";

    public function getManifest($skipClass = false) {
        $manifest = false;
        if(!App::environment('local', 'staging') && Cache::has($this->cache_key)) {
            $manifest = Cache::get($this->cache_key);
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
                    if(!isset($path->version)) {
                        continue;
                    }
                    $version = $path->version;
                    $method = strtoupper($path->verb);
                    if($endpoint instanceof ResourceEndpoint) {
                        $uriParts = explode('/', $endpoint->getURIForPath($path));
                    } else {
                        if(empty($path->path)) {
                            dd($path, get_class_methods($path), $endpoint, get_class_methods($endpoint));
                            continue;
                        }
                        $uriParts = explode('/', $path->path);
                    }
                    array_shift($uriParts);
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

    public function getRAMLManifest($skipClass = false) {

        $version = env('VERSION', 'dev');
        if ( $version == 'dev' ) {
            // Try to get the git tag/version
            $v = exec('git describe  --tags');
            if ( strstr($v, 'fatal:') === false )
                $version = $v;
        }

        $raml = new Raml([
            'title' => config('app.name'),
            //'description' => 'desc...',
            'version' => $version,
            'protocols' => [ 'HTTPS' ],
            'baseUri' => URL::to('/'),
            'mediaType' => [ 'application/json' ],
            //'securedBy' => [ 'oauth_2_0' ],
            'documenation' => [
                'home' => [
                    'title' => 'test home title',
                    'content' => "### markdown header\ntesting"
                ]
            ],
        ], $skipClass);

        $raml->addEndpoints($this->getEndpointsInClasses($this->getReader()));

        return $raml;
    }

}
