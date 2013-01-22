<?php

namespace VGMdb\Provider;

use VGMdb\Component\HttpFoundation\Request;
use VGMdb\Component\HttpKernel\EventListener\LocaleMappingListener;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Loads locale specific configuration upon boot.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class LocaleServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['locale.mapping'] = array();

        $app['locale.mapping_listener'] = $app->share(function ($app) {
            return new LocaleMappingListener($app['request_context'], $app['locale.mapping']);
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['locale.mapping_listener']);
    }
}
