<?php
namespace DreamHack\SDK\Providers;

use Collective\Annotations\AnnotationsServiceProvider as ServiceProvider;
use Collective\Annotations\Routing\Annotations\Scanner as RouteScanner;
use DreamHack\SDK\Annotations\Scanner as ManifestScanner;
use DreamHack\SDK\Http\Controllers\ManifestController;
use Fideloper\Proxy\TrustedProxyServiceProvider;

class AnnotationsServiceProvider extends ServiceProvider {

    /**
     * The classes to scan for route annotations.
     *
     * @var array
     */
    protected $scanRoutes = [
      ManifestController::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        parent::register();

        $this->registerManifestScanner();

        $this->app->register(TrustedProxyServiceProvider::class);
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

            $scanner->addAnnotationNamespace( 'Collective\Annotations\Routing\Annotations\Annotations', base_path().'/vendor/laravelcollective/annotations/src/Routing/Annotations/Annotations' );
            $scanner->addAnnotationNamespace( 'DreamHack\SDK\Annotations', __DIR__.'/../Annotations' );
            return $scanner;
        });
    }

    public function getManifest($skipClass = false) {

        $this->app->make('annotations.route.scanner');
        $scanner = $this->app->make('annotations.manifest.scanner');
        $classes = array_merge(
          $this->scanRoutes,
          $this->getClassesFromNamespace($this->getAppNamespace().'Http\\Controllers')
        );
        $scanner->setClassesToScan($classes);
        return $scanner->getManifest($skipClass);
    }

}
