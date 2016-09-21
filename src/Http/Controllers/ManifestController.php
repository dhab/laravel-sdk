<?php

namespace DreamHack\SDK\Http\Controllers;


use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use DreamHack\SDK\AnnotationsServiceProvider;

class ManifestController extends BaseController
{
	private $provider;


	public function __construct(AnnotationsServiceProvider $provider) {
		$this->provider = $provider;
	}

    /**
     * Display a listing of the resource.
     * @Get("manifest", as="manifest", middleware="web")
     * @return \Illuminate\Http\Response
     */
    public function manifest()
    {
        return response()->json($this->provider->getManifest(static::class));
    }

    /**
     * Display a listing of the resource.
     * @Get("manifest.raml", as="manifest.raml", middleware="web")
     * @return \Illuminate\Http\Response
     */
    public function raml()
    {
        return response()->json($this->provider->getRAMLManifest(static::class));
    }

}