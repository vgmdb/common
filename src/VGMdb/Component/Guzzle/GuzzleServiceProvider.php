<?php

namespace VGMdb\Component\Guzzle;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Guzzle\Log\MonologLogAdapter;
use Guzzle\Plugin\Log\LogPlugin;
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
        $app['guzzle'] = $app->share(function ($app) {
            if (!isset($app['guzzle.services'])) {
                $builder = new ServiceBuilder(array());
            } else {
                $builder = ServiceBuilder::factory($app['guzzle.services'], $app['guzzle.builder_format']);
            }

            return $builder;
        });

        // Register a simple Guzzle Client object
        $app['guzzle.client'] = $app->share(function() use ($app) {
            $guzzle = new Client($app['guzzle.base_url']);
            $guzzle->setEventDispatcher($app['dispatcher']);
            $guzzle->addSubscriber($app['guzzle.logger']);

            return $guzzle;
        });

        $app['guzzle.logger'] = $app->share(function() use ($app) {
            return new LogPlugin($app['guzzle.logger.adapter']);
        });

        $app['guzzle.logger.adapter'] = $app->share(function() use ($app) {
            return new MonologLogAdapter($app['monolog']);
        });
    }

    public function boot(Application $app)
    {
    }
}
