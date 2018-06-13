<?php

namespace DreamHack\SDK\Annotations;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Collective\Annotations\Routing\Annotations\Path;
use Collective\Annotations\Routing\Annotations\ResourceEndpoint as BaseEndpoint;

class ResourceEndpoint extends BaseEndpoint
{

    /**
     * All of the resource controller methods.
     *
     * @var array
     */
    protected $customMethods = ['batch', 'batchDestroy', 'partialUpdate'];
    protected $customVerbs = [
        "batch" => "put",
        "batchDestroy" => "post",
        "partialUpdate" => "post",
    ];
    protected $customPaths = [
        "batch" => "batch",
        "batchDestroy" => "batchDestroy",
        "partialUpdate" => null,
    ];

    protected $methods = ['batch', 'batchDestroy', 'index', 'store', 'show', 'update', 'partialUpdate', 'destroy'];

    /**
     * Create a new route definition instance.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }

        $this->buildPaths();
    }

    /**
     * Get full URI for a path for documentation & service discovery
     * @return string
     */
    public function getURIForPath($path)
    {
        if (!in_array($path, $this->paths)) {
            throw new \Exception("Tried to get URI for non-member path");
        }
        $uri = $this->name."/";
        switch ($path->method) {
            case "create":
                $uri .= "create";
                break;
            case "update":
            case "destroy":
            case "show":
                $uri .= "{id}";
                break;
            case "edit":
                $uri .= "{id}/edit";
        }
        if ($this->isCustomPath($path)) {
            $uri .= $path->path;
        }
        return $uri;
    }

    /**
     * Build all of the paths for the resource endpoint.
     *
     * @return void
     */
    protected function buildPaths()
    {
        foreach ($this->getIncludedMethods() as $method) {
            if ($this->isCustomMethod($method)) {
                list($name, $prefix) = $this->getResourcePrefix($this->name);
                $singular_name = Str::singular($name);
                $as = $this->as ?? $name;
                $path = $this->customPaths[$method] ? $this->customPaths[$method] : "{{$singular_name}}";

                $path = new Path(
                    $this->customVerbs[$method],            // verb
                    "",                                     // domain
                    $path,                                  // path
                    $as.".".$this->getNameOf($method),      // as
                    [],                                     // middleware
                    []                                      // where
                );
                $path->method = $method;
                $this->paths[] = $path;
            } else {
                $this->paths[] = new ResourcePath($method);
            }
        }
    }

    protected function isCustomMethod($method)
    {
        return in_array($method, $this->customMethods);
    }
    protected function isCustomPath($path)
    {
        return !$path instanceof ResourcePath;
    }

    protected function getNameOf($method)
    {
        return $method;
    }
    protected function getVerbOf($method)
    {
        return $this->customVerbs[$method]??"GET";
    }

    /**
     * Transform the endpoint into a route definition.
     *
     * @return string
     */
    public function toRouteDefinition()
    {
        $routes = [];

        foreach ($this->paths as $path) {
            $routeDef = false;
            if ($this->isCustomPath($path)) {
                $routeDef = sprintf(
                    $this->getCustomTemplate(),
                    $path->verb,
                    $this->getURIForPath($path),
                    $this->reflection->name."@".$path->method, // $uses
                    var_export($path->as, true),
                    $this->implodeArray($this->getCustomMiddleware($path->method)),
                    $this->implodeArray($path->where),
                    var_export($path->domain, true)
                );
            } else {
                $routeDef = sprintf(
                    $this->getTemplate(),
                    'Resource: '.$this->name.'@'.$path->method,
                    $this->implodeArray($this->getMiddleware($path)),
                    var_export($path->path, true),
                    $this->implodeArray($path->where),
                    var_export($path->domain, true),
                    var_export($this->name, true),
                    var_export($this->reflection->name, true),
                    $this->implodeArray([$path->method]),
                    $this->implodeArray($this->getNames($path))
                );
            }
            $routes[] = $routeDef;
        }

        return implode(PHP_EOL.PHP_EOL, $routes);
    }

    protected function getCustomMiddleware($method)
    {
        $classMiddleware = Collection::make($this->classMiddleware)->filter(function ($m) use ($method) {
            return $this->middlewareAppliesToMethod($method, $m);
        })
        ->map(function ($m) {
            return $m['name'];
        })->all();
        $middleware = array_merge($classMiddleware, array_get($this->middleware, $method, []));
        return $middleware;
    }
    /**
     * Get the template for non-resource custom endpoints.
     *
     * @return string
     */
    protected function getCustomTemplate()
    {
        return '$router->%s(\'%s\', [
    \'uses\' => \'%s\',
    \'as\' => %s,
    \'middleware\' => [%s],
    \'where\' => [%s],
    \'domain\' => %s,
]);';
    }

    /**
     * Extract the resource and prefix from a resource name.
     *
     * @param  string  $name
     * @return array
     */
    protected function getResourcePrefix($name)
    {
        $segments = explode('/', $name);
        // To get the prefix, we will take all of the name segments and implode them on
        // a slash. This will generate a proper URI prefix for us. Then we take this
        // last segment, which will be considered the final resources name we use.
        $prefix = implode('/', array_slice($segments, 0, -1));
        return [end($segments), $prefix];
    }
}
