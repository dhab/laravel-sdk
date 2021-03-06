<?php
namespace DreamHack\SDK\Providers;

use Collective\Annotations\AnnotationsServiceProvider as ServiceProvider;
use Collective\Annotations\Routing\Annotations\Scanner as RouteScanner;
use DreamHack\SDK\Annotations\Scanner as ManifestScanner;
use DreamHack\SDK\Http\Controllers\ManifestController;
use DreamHack\SDK\Http\Controllers\UserdataController;

class AnnotationsServiceProvider extends ServiceProvider
{

    /**
     * Determines if we will auto-scan in the local environment.
     *
     * @var bool
     */
    protected $scanWhenLocal = true;

    /**
     * Determines whether or not to automatically scan the controllers
     * directory (App\Http\Controllers) for routes
     *
     * @var bool
     */
    protected $scanControllers = true;

    /**
     * Determines whether or not to automatically scan all namespaced
     * classes for event, route, and model annotations.
     *
     * @var bool
     */
    protected $scanEverything = false;

    /**
     * The classes to scan for route annotations.
     *
     * @var array
     */
    protected $scanRoutes = [
        ManifestController::class,
        UserdataController::class,
    ];

    /**
     * Additional namespaces to scan for routes.
     *
     * @var array
     */
    protected $additionalRouteNamespaces = [
    ];

    protected $servicesToLoad = [
        AuthServiceProvider::class,
        DHIDServiceProvider::class,
        FakerServiceProvider::class,
        GuzzleServiceProvider::class,
        ResponseServiceProvider::class,
        ValidationServiceProvider::class,
    ];
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->registerManifestScanner();

        foreach ($this->servicesToLoad as $service) {
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
        $scanner->addAnnotationNamespace('DreamHack\SDK\Annotations', __DIR__.'/../Annotations');
    }


    /**
     * Get the classes to be scanned by the provider.
     *
     * @return array
     */
    public function routeScans()
    {
        if ($this->scanEverything) {
            return $this->getAllClasses();
        }
        $routes = parent::routeScans();
        foreach ($this->additionalRouteNamespaces as $namespace) {
            $routes = array_merge($this->getClassesFromNamespace($namespace), $routes);
        }
        $routes = array_merge($this->scanRoutes, $routes);
        return $routes;
    }


    /**
     * Scan the routes and write the scanned routes file.
     *
     * @return void
     */
    protected function scanRoutes()
    {
        $scans = $this->routeScans();

        if (empty($scans)) {
            return;
        }

        $scanner = $this->app->make('annotations.route.scanner');

        $scanner->setClassesToScan($scans);

        file_put_contents(
            $this->finder->getScannedRoutesPath(),
            '<?php '.$scanner->getRouteDefinitions()
        );
    }

    /**
     * Register the scanner.
     *
     * @return void
     */
    protected function registerManifestScanner()
    {
        $scanner = new ManifestScanner([]);
        $this->addRoutingAnnotations($scanner);
        $scanner->addAnnotationNamespace('Collective\Annotations\Routing\Annotations\Annotations', base_path().'/vendor/laravelcollective/annotations/src/Routing/Annotations/Annotations');
        $routesToScan = $this->routeScans();
        $scanner->setClassesToScan($routesToScan);
        $this->app->instance('annotations.manifest.scanner', $scanner);
        $this->app->instance('annotations.route.scanner', $scanner);
    }

    protected function getScanner()
    {
        $this->app->make('annotations.route.scanner');
        $scanner = $this->app->make('annotations.manifest.scanner');
        return $scanner;
    }

    public function getManifest($skipClass = false)
    {
        return $this->getScanner()->getManifest($skipClass);
    }

    public function getRAMLManifest($skipClass = false)
    {
        return $this->getScanner()->getRAMLManifest($skipClass);
    }
}
