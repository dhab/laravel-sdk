<?php
namespace DreamHack\SDK\Annotations;

use Collective\Annotations\Routing\Annotations\EndpointCollection;
use Collective\Annotations\Routing\Annotations\Annotations\Annotation;
use Collective\Annotations\Routing\Annotations\MethodEndpoint;
use ReflectionMethod;
use ReflectionClass;

class MiddlewareAnnotation extends Annotation
{

    protected $middleware = [];

    /**
     * {@inheritdoc}
     */
    public function modify(MethodEndpoint $endpoint, ReflectionMethod $method)
    {
        if ($endpoint->hasPaths()) {
            foreach ($endpoint->getPaths() as $path) {
                $path->middleware = array_merge($path->middleware, (array) $this->middleware);
            }
        } else {
            $endpoint->middleware = array_merge($endpoint->middleware, (array) $this->middleware);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modifyCollection(EndpointCollection $endpoints, ReflectionClass $class)
    {
        foreach ($endpoints as $endpoint) {
            foreach ((array) $this->middleware as $middleware) {
                $endpoint->classMiddleware[] = [
                    'name' => $middleware, 'only' => (array) $this->only, 'except' => (array) $this->except,
                ];
            }
        }
    }
}
