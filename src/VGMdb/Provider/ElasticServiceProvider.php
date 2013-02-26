<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Elastica\Client;

/**
 * Provides ElasticSearch integration using Elastica.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ElasticServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['elastic'] = $app->share(function ($app) {
            $elastic = new Client($app['elastic.options']);

            return $elastic;
        });
    }

    public function boot(Application $app)
    {
    }
}
