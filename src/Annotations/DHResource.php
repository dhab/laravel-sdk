<?php
namespace DreamHack\SDK\Annotations;

use Collective\Annotations\Routing\Annotations\Annotations\Resource;
use Collective\Annotations\Routing\Annotations\EndpointCollection;
use ReflectionClass;

/**
 * @Annotation
 */
class DHResource extends Resource
{
    /**
     * {@inheritdoc}
     */
    public function modifyCollection(EndpointCollection $endpoints, ReflectionClass $class)
    {
        $initial_value = $this->value;
        $this->values['prefix'] = env('API_PREFIX').(!empty($initial_value)?'/'.$initial_value:'');
        $this->values['value'] = ($this->values['version']?:'0')."/".$this->values['prefix'];
        $endpoints->push(new ResourceEndpoint([
            'reflection' => $class, 'name' => $this->value, 'names' => (array) $this->names,
            'only'       => (array) $this->only, 'except' => (array) $this->except,
            'middleware' => $this->getMiddleware($endpoints), 'as' => $this->as,
        ]));
        $this->prefixApiVersions($endpoints);
    }

    /**
     * Trim the path slashes for a given prefix and path.
     *
     * @param string $prefix
     * @param string $path
     *
     * @return string
     */
    protected function trimPath($prefix, $path)
    {
        return trim(trim($prefix, '/').'/'.trim($path, '/'), '/');
    }

    /**
    * {@inheritdoc}
    */
    public function prefixApiVersions(EndpointCollection $endpoints)
    {
        foreach ($endpoints as $endpoint) {
            foreach ($endpoint->getPaths() as $path) {
                if (!$endpoint instanceof ResourceEndpoint) {
                    $path->path = $this->trimPath((isset($path->version)?$path->version:$this->values['version'])."/".$this->values['prefix'], $path->path);
                } else {
                    $path->version = $this->values['version'];
                }
            }
        }
    }
}
