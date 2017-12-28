<?php

namespace DreamHack\SDK\Annotations;

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
    protected $customMethods = ['batch', 'batchDestroy'];
    protected $customVerbs = [
        "batch" => "put",
        "batchDestroy" => "post"
    ];
    protected $methods = ['batch', 'batchDestroy', 'index', 'store', 'show', 'update', 'destroy'];

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
                $path = new Path(
                    $this->customVerbs[$method],
                    "",
                    $method,
                    $this->as.".".$this->getNameOf($method),
                    [],
                    []
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
}
