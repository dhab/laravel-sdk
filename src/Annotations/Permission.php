<?php
namespace DreamHack\SDK\Annotations;

use Collective\Annotations\Routing\Annotations\Annotations\Annotation;

use Collective\Annotations\Routing\Annotations\EndpointCollection;
use Collective\Annotations\Routing\Annotations\MethodEndpoint;
use ReflectionClass;
use ReflectionMethod;

/**
 * @Annotation
 */
class Permission extends Annotation
{

    public function modify(MethodEndpoint $endpoint, ReflectionMethod $method)
    {
        if ($endpoint->hasPaths()) {
            foreach ($endpoint->getPaths() as $path) {
                $path->permissions = array_merge($path->permissions ?? [], (array) $this->value);
            }
        } else {
            $endpoint->permissions = array_merge($endpoint->permissions ?? [], (array) $this->value);
        }
    }

    public function modifyCollection(EndpointCollection $endpoints, ReflectionClass $class)
    {
        foreach ($endpoints as $endpoint) {
            $endpoint->permissions = array_merge($endpoint->permissions ?? [], (array) $this->value);
        }
    }
}
