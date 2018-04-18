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
class PermissionParameter extends Annotation
{

    public function modify(MethodEndpoint $endpoint, ReflectionMethod $method)
    {
        if ($endpoint->hasPaths()) {
            foreach ($endpoint->getPaths() as $path) {
                $path->permission_parameters = array_merge($path->permission_parameters ?? [], (array) $this->value);
            }
        } else {
            $endpoint->permission_parameters = array_merge($endpoint->permission_parameters ?? [], (array) $this->value);
        }
    }

    public function modifyCollection(EndpointCollection $endpoints, ReflectionClass $class)
    {
        foreach ($endpoints as $endpoint) {
            $endpoint->permission_parameters = array_merge($endpoint->permission_parameters ?? [], (array) $this->value);
        }
    }
}
