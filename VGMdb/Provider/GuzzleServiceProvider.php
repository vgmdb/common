<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Guzzle\Service\Builder\ServiceBuilder;
use Guzzle\Service\Client;

/**
 * Guzzle service provider for Silex
 *
 * = Parameters:
 *  guzzle.class_path: (optional) Path to where the Guzzle library is located.
 *  guzzle.services: (optional) array|string|SimpleXMLElement Data describing
 *      your web service clients.  You can pass the path to a file
 *      (.xml|.js|.json), an array of data, or an instantiated SimpleXMLElement
 *      containing configuration data.  See the Guzzle docs for more info.
 *  guzzle.builder_format: (optional) Pass the file extension (xml, js) when
 *      using a file that does not use the standard file extension
 *
 * = Services:
 *   guzzle: An instantiated Guzzle ServiceBuilder.
 *   guzzle.client: A default Guzzle web service client using a dumb base URL.
 *
 * @author Michael Dowling <michael@guzzlephp.org>
 */
class GuzzleServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['guzzle.base_url'] = '/';
        $app['guzzle.builder_format'] = null;

        // Register a Guzzle ServiceBuilder
        $app['guzzle'] = $app->share(function () use ($app) {
            if (!isset($app['guzzle.services'])) {
                $builder = new ServiceBuilder(array());
            } else {
                $builder = ServiceBuilder::factory($app['guzzle.services'], $app['guzzle.builder_format']);
            }

            return $builder;
        });

        // Register a simple Guzzle Client object
        $app['guzzle.client'] = $app->share(function() use ($app) {
            return new Client($app['guzzle.base_url']);
        });
    }

    public function boot(Application $app)
    {
    }
}
