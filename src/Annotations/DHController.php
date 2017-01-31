<?php
namespace DreamHack\SDK\Annotations;

use Collective\Annotations\Routing\Annotations\Annotations\Controller as BaseController;
use Collective\Annotations\Routing\Annotations\EndpointCollection;
use ReflectionClass;

/**
 * @Annotation
 */
class DHController extends BaseController {
    /**
     * {@inheritdoc}
     */
    public function modifyCollection(EndpointCollection $endpoints, ReflectionClass $class)
    {
    	parent::modifyCollection($endpoints, $class);

    	$this->prefixApiVersions($endpoints);
    }

  /**
   * {@inheritdoc}
   */
  public function prefixApiVersions(EndpointCollection $endpoints)
  {
        foreach ($endpoints->getAllPaths() as $path) {
            $path->path = $this->trimPath((isset($path->version)?$path->version:'0')."/".env('API_PREFIX', 'content'), $path->path);
        }
  }
}