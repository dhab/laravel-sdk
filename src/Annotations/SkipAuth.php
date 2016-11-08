<?php
namespace DreamHack\SDK\Annotations;

use Collective\Annotations\Routing\Annotations\Annotations\Annotation;
use Collective\Annotations\Routing\Annotations\MethodEndpoint;
use ReflectionMethod;

/**
 * @Annotation
 */
class SkipAuth extends Annotation {

  /**
   * {@inheritdoc}
   */
  public function modify(MethodEndpoint $endpoint, ReflectionMethod $method)
  {
    $endpoint->skipAuth = true;
  }
}