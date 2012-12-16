<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Provides ElasticSearch integration using Elastica.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ElasticServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['elastic'] = $app->share(function () use ($app) {
            $elastic = new \Elastica_Client($app['elastic.options']);

            return $elastic;
        });
    }

    public function boot(Application $app)
    {
    }
}
