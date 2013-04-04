<?php

namespace VGMdb\Component\Silex;

use VGMdb\Component\Silex\AbstractResourceProvider;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Provides a resource locator for accessing resources in components and packages.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ResourceLocatorServiceProvider extends AbstractResourceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['resource_locator'] = $app->share(function ($app) {
            $providers = array();
            foreach ($app->getProviders() as $provider) {
                if ($provider instanceof ResourceProviderInterface && $provider->isActive()) {
                    $providers[] = $provider;
                }
            }

            $locator = new ResourceLocator();
            $locator->initialize($providers);

            return $locator;
        });
    }

    public function boot(Application $app)
    {
    }
}
