<?php
namespace DreamHack\SDK\Annotations;

use Collective\Annotations\Routing\Annotations\EndpointCollection;
use Collective\Annotations\Routing\Annotations\Annotations\Annotation;
use Collective\Annotations\Routing\Annotations\MethodEndpoint;
use ReflectionMethod;
use ReflectionClass;

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

    /**
     * {@inheritdoc}
     */
    public function modifyCollection(EndpointCollection $endpoints, ReflectionClass $class)
    {
        foreach ($endpoints as $endpoint) {
        	$endpoint->skipAuth = true;
        }
    }
}