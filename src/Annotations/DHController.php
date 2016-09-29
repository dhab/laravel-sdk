<?php
namespace DreamHack\SDK\Annotations;

use Collective\Annotations\Routing\Annotations\Annotations\Controller as BaseController;
use Collective\Annotations\Routing\Annotations\EndpointCollection;
use ReflectionMethod;

/**
 * @Annotation
 */
class DHController extends BaseController {

  /**
   * {@inheritdoc}
   */
  public function prefixEndpoints(EndpointCollection $endpoints)
  {
        foreach ($endpoints->getAllPaths() as $path) {
            $path->path = $this->trimPath((isset($path->version)?$path->version."/":'').env('API_PREFIX', 'content'), $this->trimPath($this->prefix, $path->path));
        }
  }
}