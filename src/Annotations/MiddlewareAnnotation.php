<?php
namespace DreamHack\SDK\Annotations;

use Collective\Annotations\Routing\Annotations\Annotations\Annotation;
use Collective\Annotations\Routing\Annotations\MethodEndpoint;
use ReflectionMethod;

class MiddlewareAnnotation extends Annotation {

  protected $middleware = [];

  /**
   * {@inheritdoc}
   */
  public function modify(MethodEndpoint $endpoint, ReflectionMethod $method)
  {
    if ($endpoint->hasPaths())
    {
      foreach ($endpoint->getPaths() as $path)
      {
        $path->middleware = array_merge($path->middleware, $this->middleware);
      }
    }
    else
    {
      $endpoint->middleware = array_merge($endpoint->middleware, $this->middleware);
    }
  }

}