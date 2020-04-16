<?php

namespace DreamHack\SDK\Annotations;

use App;
use Cache;
use URL;
use DreamHack\SDK\Documentation\Raml;
use Collective\Annotations\Routing\Annotations\Scanner as BaseRouteScanner;
use Collective\Annotations\Routing\Annotations\ResourceEndpoint;

function getLength($endpoint)
{
    $paths = [];
    foreach ($endpoint->getPaths() as $path) {
        if ($endpoint instanceof ResourceEndpoint) {
            $paths[] = $endpoint->getURIForPath($path);
        } else {
            $paths[] = $path->path;
        }
    }
    
    if ($endpoint instanceof ResourceEndpoint) {
        usort($paths, function ($a, $b) {
            return (strlen($a) < strlen($b)) ? -1 : 1;
        });
    } else {
        usort($paths, function ($a, $b) {
            return (strlen($a) > strlen($b)) ? -1 : 1;
        });
    }
    $path = $paths[0]??'';
    

    $min_wildcards = 0;
    $wildcards = array_reduce(array_map(function ($item) {
        return preg_match_all("/{\w+}/", $item);
    }, $paths), "min", PHP_INT_MAX);
    return [
        strlen($path),
        $wildcards==PHP_INT_MAX?0:$wildcards,
    ];
}

class Scanner extends BaseRouteScanner
{
    private $cache_key = "manifest";

    public function getManifest($skipClass = false)
    {
        $manifest = Cache::get($this->cache_key, false);
        if (!$manifest) {
            $manifest = [
                "uuid" => config('dhid.api_uuid'),
                "prefix" => config('dhid.api_prefix'),
                "permissions" => config('permissions'),
                "endpoints" => [],
            ];
            $endpoints = $this->getEndpointsInClasses($this->getReader());
            foreach ($endpoints as $endpoint) {
                if ($endpoint->reflection->name == $skipClass) {
                    continue;
                }
                foreach ($endpoint->getPaths() as $path) {
                    if (!isset($path->version)) {
                        continue;
                    }
                    $version = $path->version;
                    $method = strtoupper($path->verb);
                    if ($endpoint instanceof ResourceEndpoint) {
                        $uriParts = explode('/', $endpoint->getURIForPath($path));
                    } else {
                        if (empty($path->path)) {
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
                    if (isset($endpoint->skipAuth) && $endpoint->skipAuth) {
                        $route['skipAuth'] = true;
                    }
                    if ($method == 'GET' && isset($endpoint->cacheable) && $endpoint->cacheable) {
                        $route['cacheable'] = true;
                    }
                    if (isset($endpoint->permissions) && $endpoint->permissions) {
                        $route['permissions'] = $endpoint->permissions;
                    }
                    if (isset($endpoint->permission_parameters) && $endpoint->permission_parameters) {
                        $route['permission_parameters'] = $endpoint->permission_parameters;
                    }
                    $manifest['endpoints'][] = $route;
                    
                    if ($method == 'GET') {
                        $route['method'] = 'HEAD';
                        $manifest['endpoints'][] = $route;
                    }
                }
            }
            
            // Sort the endpoints by url-length, longest first.
            usort($manifest['endpoints'], function ($a, $b) {
                return (strlen($a['url']) > strlen($b['url'])) ? -1 : 1;
            });

            Cache::put($this->cache_key, $manifest, 300);
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
        foreach ($endpointsCollection as $endpoint) {
            $endpoints[] = $endpoint;
        }
        $unsortedEndpoints = $endpoints;
        usort($endpoints, function ($a, $b) {
            list($lengthA, $wildcardsA) = getLength($a);
            list($lengthB, $wildcardsB) = getLength($b);
            if ($wildcardsA < $wildcardsB) {
                return -1;
            } elseif ($wildcardsA > $wildcardsB) {
                return 1;
            } elseif ($lengthA > $lengthB) {
                return -1;
            } elseif ($lengthA < $lengthB) {
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

    public function getRAMLManifest($skipClass = false)
    {

        $version = config('dhid.version');
        if ($version == 'dev') {
            // Try to get the git tag/version
            $v = exec('git describe  --tags');
            if (strstr($v, 'fatal:') === false) {
                $version = $v;
            }
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
