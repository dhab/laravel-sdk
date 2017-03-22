<?php

namespace DreamHack\SDK\Http\Controllers;

use Manifest;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class ManifestController extends BaseController
{

    /**
     * Display a listing of the resource.
     * @Get("manifest", as="manifest", middleware="web")
     * @return \Illuminate\Http\Response
     */
    public function manifest()
    {
        return response()->json(Manifest::getManifest(static::class));
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function raml()
    {
        // * @Get("manifest.raml", as="manifest.raml", middleware="web")
        return response()->json(Manifest::getRAMLManifest(static::class));
    }

}