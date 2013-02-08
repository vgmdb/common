<?php

namespace VGMdb\Provider;

use VGMdb\Component\DomainObject\DomainObjectFactory;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Domain object interoperability layer.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DomainObjectServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['domain.object_factory'] = $app->share(function ($app) {
            $serializer = isset($app['serializer']) ? $app['serializer'] : null;

            return new DomainObjectFactory($app['dispatcher'], $app['logger'], $serializer);
        });
    }

    public function boot(Application $app)
    {
    }
}
