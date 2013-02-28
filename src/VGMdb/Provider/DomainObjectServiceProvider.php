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
        $app['domain.classes'] = array();
        $app['domain.default_class'] = 'VGMdb\\Component\\DomainObject\\DomainObject';

        $app['domain.object_factory'] = $app->share(function ($app) {
            $config = array(
                'classes' => $app['domain.classes'],
                'default_class' => $app['domain.default_class']
            );

            return new DomainObjectFactory($config, $app['dispatcher'], $app['logger']);
        });
    }

    public function boot(Application $app)
    {
    }
}
