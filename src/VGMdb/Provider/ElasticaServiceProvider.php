<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Provides ElasticSearch integration using Elastica.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ElasticaServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['elastica.client.class'] = 'Elastica\\Client';

        $app['elastica'] = $app->share(function ($app) {
            $elastica = new $app['elastica.client.class']($app['elastica.options']);

            return $elastica;
        });
    }

    public function boot(Application $app)
    {
    }
}
