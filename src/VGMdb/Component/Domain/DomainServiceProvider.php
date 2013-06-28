<?php

namespace VGMdb\Component\Domain;

use VGMdb\Component\Domain\Handler\DoctrineHandler;
use VGMdb\Component\Domain\Handler\PropelHandler;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Domain object interoperability layer.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DomainServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['domain.classes'] = array();
        $app['domain.collection_classes'] = array();
        $app['domain.default_class'] = 'VGMdb\\Component\\Domain\\DomainObject';
        $app['domain.default_collection_class'] = 'VGMdb\\Component\\Domain\\DomainObjectCollection';
        $app['domain.namespaces'] = array();

        $app['domain.object_factory'] = $app->share(function ($app) {
            $config = array(
                'classes' => $app['domain.classes'],
                'collection_classes' => $app['domain.collection_classes'],
                'default_class' => $app['domain.default_class'],
                'default_collection_class' => $app['domain.default_collection_class'],
                'namespaces' => $app['domain.namespaces']
            );

            return new DomainObjectFactory($config, $app['domain.object_handlers'], $app['dispatcher'], $app['logger']);
        });

        $app['domain.object_handlers'] = $app->share(function ($app) {
            $handlers = new \Pimple();

            $handlers['propel'] = $handlers->share(function ($app) {
                return new PropelHandler(function () use ($app) {
                    return $app['propel.connection']();
                });
            });

            $handlers['propel1'] = $handlers->share(function ($app) {
                return new PropelHandler(function () use ($app) {
                    return $app['propel1.connection']();
                });
            });

            $handlers['doctrine'] = $handlers->share(function ($app) {
                return new DoctrineHandler(function () use ($app) {
                    return $app['doctrine'];
                });
            });

            return $handlers;
        });
    }

    public function boot(Application $app)
    {
    }
}
