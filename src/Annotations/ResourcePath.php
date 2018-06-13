<?php

namespace DreamHack\SDK\Annotations;

use Collective\Annotations\Routing\Annotations\ResourcePath as BasePath;

class ResourcePath extends BasePath
{
    /**
     * Get the verb for the given resource method.
     *
     * @param string $method
     *
     * @return string
     */
    protected function getVerb($method)
    {
        switch ($method) {
            case 'index':
            case 'create':
            case 'show':
            case 'edit':
                return 'get';

            case 'store':
            case 'partialUpdate':
                return 'post';

            case 'update':
            case 'batch':
                return 'put';

            case 'destroy':
            case 'batchDestroy':
                return 'delete';
        }
    }
}
