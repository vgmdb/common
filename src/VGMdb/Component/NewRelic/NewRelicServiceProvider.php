<?php

namespace VGMdb\Component\NewRelic;

use VGMdb\Component\NewRelic\EventListener\TransactionListener;
use VGMdb\Component\NewRelic\EventListener\RumInjectionListener;
use VGMdb\Component\NewRelic\EventListener\ExceptionListener;
use VGMdb\Component\Silex\AbstractResourceProvider;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * New Relic monitoring.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class NewRelicServiceProvider extends AbstractResourceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['newrelic.api_key'] = null;
        $app['newrelic.apps'] = array();
        $app['newrelic.monitor.class'] = extension_loaded('newrelic')
            ? 'VGMdb\\Component\\NewRelic\\Monitor\\ExtensionMonitor'
            : 'VGMdb\\Component\\NewRelic\\Monitor\\BlackholeMonitor';

        $app['newrelic.monitor'] = $app->share(function ($app) {
            return new $app['newrelic.monitor.class']();
        });

        $app['newrelic.transaction_listener'] = $app->share(function ($app) {
            return new TransactionListener($app['newrelic.monitor'], $app['request_context'], $app['newrelic.apps']);
        });

        $app['newrelic.rum_injection_listener'] = $app->share(function ($app) {
            return new RumInjectionListener($app['newrelic.monitor']);
        });

        $app['newrelic.exception_listener'] = $app->share(function ($app) {
            return new ExceptionListener($app['newrelic.monitor']);
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['newrelic.transaction_listener']);
        $app['dispatcher']->addSubscriber($app['newrelic.rum_injection_listener']);
        $app['dispatcher']->addSubscriber($app['newrelic.exception_listener']);
    }
}
