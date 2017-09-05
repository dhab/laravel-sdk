<?php

namespace DreamHack\SDK\Annotations;
use App;
use Cache;
use URL;
use DreamHack\SDK\Documentation\Raml;
use Collective\Annotations\Routing\Annotations\Scanner as BaseRouteScanner;
use Collective\Annotations\Routing\Annotations\ResourceEndpoint;


function getLength($endpoint) {
    $paths = [];
    foreach($endpoint->getPaths() as $path) {
        if($endpoint instanceof ResourceEndpoint) {
            $paths[] = $endpoint->getURIForPath($path);
        } else {
            $paths[] = $path->path;
        }
    }
    
    usort($paths, function($a, $b) {
        return (strlen($a) > strlen($b)) ? -1 : 1;
    });
    $path = $paths[0]??'';
    

    $min_wildcards = 0;
    $wildcards = array_reduce(array_map(function($item) {
        return preg_match_all("/{\w+}/", $item);
    }, $paths), "min", PHP_INT_MAX);
    return [
        strlen($path),
        $wildcards==PHP_INT_MAX?0:$wildcards,
    ];
}

class Scanner extends BaseRouteScanner {
    private $cache_key = "manifest";

    public function getManifest($skipClass = false) {
        $manifest = false;
        if(!App::environment('local', 'staging') && Cache::has($this->cache_key)) {
            $manifest = Cache::get($this->cache_key);
        }
        if(!$manifest) {
            $manifest = [
                "uuid" => env('API_UUID'),
                "prefix" => env('API_PREFIX'),
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

    /**
     * Convert the scanned annotations into route definitions.
     *
     * @return string
     */
    public function getRouteDefinitions()
    {
        $output = '';
        $endpointsCollection = $this->getEndpointsInClasses($this->getReader());
        $endpoints = [];
        foreach($endpointsCollection as $endpoint) {
            $endpoints[] = $endpoint;
        }
        $unsortedEndpoints = $endpoints;
        usort($endpoints, function($a, $b) {
            list($lengthA, $wildcardsA) = getLength($a);
            list($lengthB, $wildcardsB) = getLength($b);
            if($wildcardsA < $wildcardsB) {
                return -1;
            } else if($wildcardsA > $wildcardsB) {
                return 1;
            } else if($lengthA > $lengthB) {
                return -1;
            } else if($lengthA < $lengthB) {
                return 1;
            } else {
                return 0;
            }
        });
        foreach ($endpoints as $endpoint) {
            $output .= $endpoint->toRouteDefinition().PHP_EOL.PHP_EOL;
        }

        return trim($output);
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
