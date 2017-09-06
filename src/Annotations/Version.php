<?php
namespace DreamHack\SDK\Annotations;

use Collective\Annotations\Routing\Annotations\Annotations\Annotation;
use Collective\Annotations\Routing\Annotations\MethodEndpoint;
use ReflectionMethod;

/**
 * @Annotation
 */
class Version extends Annotation
{

  /**
   * {@inheritdoc}
   */
    public function modify(MethodEndpoint $endpoint, ReflectionMethod $method)
    {
        $endpoint->version = $this->value;

        if ($endpoint->hasPaths()) {
            foreach ($endpoint->getPaths() as $path) {
                $path->version = $this->value;
            }
        }
    }
}
