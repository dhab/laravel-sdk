<?php
namespace DreamHack\SDK\Providers;

use Collective\Annotations\AnnotationsServiceProvider as ServiceProvider;
use Collective\Annotations\Routing\Annotations\Scanner as RouteScanner;
use DreamHack\SDK\Annotations\Scanner as ManifestScanner;
use DreamHack\SDK\Http\Controllers\ManifestController;

class AnnotationsServiceProvider extends ServiceProvider {

    /**
     * The classes to scan for route annotations.
     *
     * @var array
     */
    protected $scanRoutes = [
      ManifestController::class,
    ];

    protected $servicesToLoad = [  
        AuthServiceProvider::class,
        DHIDServiceProvider::class,
        FakerServiceProvider::class,
        GuzzleServiceProvider::class,
        ResponseServiceProvider::class,
    ];
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        parent::register();

        $this->registerManifestScanner();

        foreach($this->servicesToLoad as $service) {
            $this->app->register($service);
        }
    }


    /**
     * Add annotation classes to the route scanner.
     *
     * @param RouteScanner $scanner
     */
    public function addRoutingAnnotations(RouteScanner $scanner)
    {
        parent::addRoutingAnnotations($scanner);
        $scanner->addAnnotationNamespace( 'DreamHack\SDK\Annotations', __DIR__.'/../Annotations' );
    }

    /**
     * Register the scanner.
     *
     * @return void
     */
    protected function registerManifestScanner()
    {
        $this->app->singleton('annotations.manifest.scanner', function ($app) {
            $scanner = new ManifestScanner([]);
            $this->addRoutingAnnotations($scanner);
            $scanner->addAnnotationNamespace( 'Collective\Annotations\Routing\Annotations\Annotations', base_path().'/vendor/laravelcollective/annotations/src/Routing/Annotations/Annotations' );
            $scanner->setClassesToScan($this->routeScans());
            return $scanner;
        });
    }

    protected function getScanner() {
        $this->app->make('annotations.route.scanner');
        $scanner = $this->app->make('annotations.manifest.scanner');
        return $scanner;
    }

    public function getManifest($skipClass = false) {
        dd($this->getScanner());
        return $this->getScanner()->getManifest($skipClass);
    }

    public function getRAMLManifest($skipClass = false) {
        return $this->getScanner()->getRAMLManifest($skipClass);
    }

}
