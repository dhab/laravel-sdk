<?php
namespace DreamHack\SDK\Annotations;

use Collective\Annotations\Routing\Annotations\Annotations\Resource;
use Collective\Annotations\Routing\Annotations\EndpointCollection;
use ReflectionClass;

/**
 * @Annotation
 */
class DHResource extends Resource {
    /**
     * {@inheritdoc}
     */
    public function modifyCollection(EndpointCollection $endpoints, ReflectionClass $class)
    {
        $this->values['value'] = ($this->values['version']?:'0')."/".env('API_PREFIX', 'content').'/'.$this->values['value'];
        $endpoints->push(new ResourceEndpoint([
            'reflection' => $class, 'name' => $this->value, 'names' => (array) $this->names,
            'only'       => (array) $this->only, 'except' => (array) $this->except,
            'middleware' => $this->getMiddleware($endpoints), 'as' => $this->as,
        ]));
        $this->prefixApiVersions($endpoints);
    }

    /**
    * {@inheritdoc}
    */
    public function prefixApiVersions(EndpointCollection $endpoints)
    {
        foreach ($endpoints->getAllPaths() as $path) {
            $path->version = $this->values['version'];
        }
    }
}