<?php
namespace DreamHack\SDK\Providers;

use DreamHack\SDK\Annotations\ManifestScanner;
use Collective\Annotations\Routing\Annotations\Scanner as RouteScanner;
use Collective\Annotations\AnnotationsServiceProvider as ServiceProvider;

class AnnotationsServiceProvider extends ServiceProvider {

    /**
     * The classes to scan for route annotations.
     *
     * @var array
     */
    protected $scanRoutes = [
      DreamHack\SDK\Http\Controllers\ManifestController::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        parent::register();

        $this->registerManifestScanner();

        $this->app->register(Fideloper\Proxy\TrustedProxyServiceProvider::class),
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

            $scanner->addAnnotationNamespace( 'Collective\Annotations\Routing\Annotations\Annotations' );
            $scanner->addAnnotationNamespace( 'DreamHack\SDK\Annotations' );
            return $scanner;
        });
    }

    public function getManifest($skipClass = false) {

        $scanner = $this->app->make('annotations.manifest.scanner');

        $scanner->setClassesToScan($this->routeScans());
        return $scanner->getManifest($skipClass);
    }

}
