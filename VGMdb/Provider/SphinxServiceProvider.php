<?php

namespace VGMdb\Provider;

use FSphinx\FSphinxClient;
use FSphinx\Facet;
use FSphinx\MultiFieldQuery;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Provides Sphinx integration using FSphinx.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class SphinxServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['sphinx'] = $app->share(function () use ($app) {
            $sphinx = new FSphinxClient();
            $sphinx->setServer($app['sphinx.host'], $app['sphinx.port']);
            return $sphinx;
        });

        $app['sphinx.facet'] = $app->protect(function ($name, array $options = array(), $datasource = null) {
            return new Facet($name, $options, $datasource);
        });

        $app['sphinx.query'] = $app->protect(function (array $fields = array(), array $attributes = array()) {
            return new MultiFieldQuery($fields, $attributes);
        });

        $app['sphinx.host'] = '127.0.0.1';
        $app['sphinx.port'] = 9312;
    }

    public function boot(Application $app)
    {
    }
}
