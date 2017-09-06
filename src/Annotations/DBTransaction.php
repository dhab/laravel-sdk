<?php
namespace DreamHack\SDK\Annotations;

use DreamHack\SDK\Http\Middleware\DBTransaction as Middleware;

/**
 * @Annotation
 */
class DBTransaction extends MiddlewareAnnotation
{
    protected $middleware = [Middleware::class];
}
