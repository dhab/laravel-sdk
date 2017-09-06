<?php

namespace DreamHack\SDK\Http\Controllers;

use DreamHack\SDK\Facades\Manifest;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use DreamHack\SDK\Http\Responses;

class ManifestController extends BaseController
{

    /**
     * Display a listing of the resource.
     * @SkipAuth
     * @Get("manifest", as="manifest")
     * @return \DreamHack\SDK\Http\Responses\Manifest
     */
    public function manifest()
    {
        return new Responses\Manifest(Manifest::getManifest(static::class));
    }

    /**
     * Display generated API documentation in RAML 1.0 format.a
     * @SkipAuth
     * @Get("manifest.raml", as="raml")
     * @return \DreamHack\SDK\Http\Responses\Raml
     */
    public function raml()
    {
        $raml = Manifest::getRAMLManifest(static::class);

        if (isset($_GET['errors']) && $_GET['errors']) {
            return $raml->errors();
        }

        return new Responses\Raml($raml->toArray());
    }
}
