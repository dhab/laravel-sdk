<?php

namespace DreamHack\SDK\Http\Responses;

use Symfony\Component\Yaml\Yaml;

/**
 * RESTful API Modeling Language (RAML)
 *
 * @example #%RAML 1.0
 * @example title: 'Content API'
 * @example version: 1.6.2-17-gbe06fac
 * @example protocols:
 * @example   - HTTPS
 * @example baseUri: 'http://content.dev'
 * @example mediaType:
 * @example   - application/json
 * @example types:
 * @example   Error:
 * @example     type: object
 * @example     description: 'This general error structure is used throughout this API.'
 * @example     properties:
 * @example       status:
 * @example         type: integer
 * @example         minimum: 400
 * @example         maximum: 599
 * @example       error:
 * @example         type: string
 * @example         description: 'Error messsage'
 */
class Raml extends Response
{
    public $mime = 'application/x-yaml';

    public function __construct(array $source = [])
    {

        $content = "#%RAML 1.0\n";
        $content .= Yaml::dump($source, 200, 2); //, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        parent::__construct($content);

        //$this->header('Content-Type', 'application/x-yaml');
    }
}
