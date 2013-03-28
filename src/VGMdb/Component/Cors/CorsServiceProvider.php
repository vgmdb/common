<?php

namespace VGMdb\Component\Cors;

use VGMdb\Component\Cors\EventListener\CorsListener;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Handles Cross-Origin Resource Sharing header configuration by subdomain and path.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class CorsServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['cors.defaults'] = array();
        $app['cors.config'] = array();

        $app['cors.listener'] = $app->share(function ($app) {
            if (!$app['cors.config']) {
                throw new \RuntimeException('CORS configuration is missing.');
            }

            $defaults = array_merge(
                array(
                    'allow_origin' => array(),
                    'allow_credentials' => false,
                    'allow_headers' => array(),
                    'expose_headers' => array(),
                    'allow_methods' => array(),
                    'max_age' => 0,
                ),
                $app['cors.defaults']
            );

            // normalize array('*') to true
            if (in_array('*', $defaults['allow_origin'])) {
                $defaults['allow_origin'] = true;
            }

            $config = $app['cors.config'];
            foreach ($config as $subdomain => $paths) {
                foreach ($paths as $path => $options) {
                    $options = array_filter($options);
                    if (isset($options['allow_origin']) && in_array('*', $options['allow_origin'])) {
                        $options['allow_origin'] = true;
                    }

                    $config[$subdomain][$path] = $options;
                }
            }

            return new CorsListener($app['dispatcher'], $config, $defaults);
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['cors.listener']);
    }
}
