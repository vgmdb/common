<?php

namespace VGMdb\Component\WebProfiler;

use VGMdb\Component\Silex\AbstractResourceProvider;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Provides Symfony's profiling tools.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class WebProfilerServiceProvider extends AbstractResourceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // defaults
        $app['profiler.class'] = 'Symfony\\Component\\HttpKernel\\Profiler\\Profiler';
        $app['profiler_listener.class'] = 'Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener';
        $app['profiler_listener.only_exceptions'] = false;
        $app['profiler_listener.only_master_requests'] = true;
        $app['profiler.request_matcher.class'] = 'Symfony\\Component\\HttpFoundation\\RequestMatcher';
        $app['profiler.request_matcher.path'] = null;
        $app['profiler.request_matcher.host'] = null;
        $app['profiler.request_matcher.methods'] = null;
        $app['profiler.request_matcher.ip'] = null;
        $app['profiler.controller.profiler.class'] = 'VGMdb\\Component\\WebProfiler\\Controller\\ProfilerController';

        $app['profiler.priorities'] = array(
            'config'      => -1,
            'request'     => -1,
            'exception'   => -1,
            'events'      => -1,
            'logger'      => -1,
            'time'        => -1,
            'memory'      => -1,
            'router'      => -1,
            'security'    => -1,
            'container'   => -1,
            'classloader' => -1,
            'view'        => -1,
            'doctrine'    => -1,
            'propel1'     => -1,
            'guzzle'      => -1,
            'swiftmailer' => -1,
            'elastica'    => -1,
        );

        // data collector classes
        $app['data_collector.config.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\ConfigDataCollector';
        $app['data_collector.request.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\RequestDataCollector';
        $app['data_collector.exception.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\ExceptionDataCollector';
        $app['data_collector.events.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\EventDataCollector';
        $app['data_collector.logger.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\LoggerDataCollector';
        $app['data_collector.time.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\TimeDataCollector';
        $app['data_collector.memory.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\MemoryDataCollector';
        $app['data_collector.router.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\RouterDataCollector';
        $app['data_collector.security.class'] = 'VGMdb\\Component\\Security\\DataCollector\\SecurityDataCollector';
        $app['data_collector.container.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\ContainerDataCollector';
        $app['data_collector.classloader.class'] = 'VGMdb\\Component\\Composer\\DataCollector\\ClassLoaderDataCollector';
        $app['data_collector.view.class'] = 'VGMdb\\Component\\View\\DataCollector\\ViewDataCollector';
        $app['data_collector.doctrine.class'] = 'VGMdb\\Component\\Doctrine\\DataCollector\\DoctrineDataCollector';
        $app['data_collector.propel1.class'] = 'VGMdb\\Component\\Propel1\\DataCollector\\PropelDataCollector';
        $app['data_collector.guzzle.class'] = 'VGMdb\\Component\\Guzzle\\DataCollector\\GuzzleDataCollector';
        $app['data_collector.swiftmailer.class'] = 'VGMdb\\Component\\Swiftmailer\\DataCollector\\EmailDataCollector';
        $app['data_collector.elastica.class'] = 'VGMdb\\Component\\Elastica\\DataCollector\\ElasticaDataCollector';

        // data collectors. remember to guard against nonexistent providers by returning -1
        $app['data_collector.config'] = $app->share(function ($app) {
            return new $app['data_collector.config.class']($app);
        });
        $app['data_collector.request'] = $app->share(function ($app) {
            return new $app['data_collector.request.class']($app['request_context']);
        });
        $app['data_collector.exception'] = $app->share(function ($app) {
            return new $app['data_collector.exception.class']();
        });
        $app['data_collector.events'] = $app->share(function ($app) {
            return new $app['data_collector.events.class']($app['dispatcher']);
        });
        $app['data_collector.logger'] = $app->share(function ($app) {
            if (!isset($app['logger'])) {
                return -1;
            }
            return new $app['data_collector.logger.class']($app['logger']);
        });
        $app['data_collector.time'] = $app->share(function ($app) {
            return new $app['data_collector.time.class']($app);
        });
        $app['data_collector.memory'] = $app->share(function ($app) {
            return new $app['data_collector.memory.class']();
        });
        $app['data_collector.router'] = $app->share(function ($app) {
            return new $app['data_collector.router.class']();
        });
        $app['data_collector.security'] = $app->share(function ($app) {
            if (!isset($app['security'])) {
                return -1;
            }
            return new $app['data_collector.security.class']($app['security']);
        });
        $app['data_collector.container'] = $app->share(function ($app) {
            return new $app['data_collector.container.class']($app);
        });
        $app['data_collector.classloader'] = $app->share(function ($app) {
            return new $app['data_collector.classloader.class']();
        });
        $app['data_collector.view'] = $app->share(function ($app) {
            return new $app['data_collector.view.class']($app['view.logger']);
        });
        $app['data_collector.doctrine'] = $app->share(function ($app) {
            if (!isset($app['doctrine'])) {
                return -1;
            }
            $collector = new $app['data_collector.doctrine.class']($app['doctrine']);
            $collector->addLogger($app['dbs.default'], $app['db.debug_logger']);

            return $collector;
        });
        $app['data_collector.propel1'] = $app->share(function ($app) {
            if (!isset($app['propel1.configuration'])) {
                return -1;
            }
            return new $app['data_collector.propel1.class']($app['propel1.logger'], $app['propel1.configuration']);
        });
        $app['data_collector.guzzle'] = $app->share(function ($app) {
            if (!isset($app['guzzle'])) {
                return -1;
            }
            return new $app['data_collector.guzzle.class']($app['guzzle.debug_logger.adapter']);
        });
        $app['data_collector.swiftmailer'] = $app->share(function ($app) {
            if (!isset($app['mailer'])) {
                return -1;
            }
            return new $app['data_collector.swiftmailer.class']($app, true);
        });
        $app['data_collector.elastica'] = $app->share(function ($app) {
            if (!isset($app['elastica'])) {
                return -1;
            }
            return new $app['data_collector.elastica.class']($app['elastica.debug_logger']);
        });

        $app['data_collector.registry'] = $app->share(function ($app) {
            $priorities = $app['profiler.priorities'];
            $registry = array(
                'config'      => array($priorities['config'],      '@WebProfiler/collector/config'),
                'request'     => array($priorities['request'],     '@WebProfiler/collector/request'),
                'exception'   => array($priorities['exception'],   '@WebProfiler/collector/exception'),
                'events'      => array($priorities['events'],      '@WebProfiler/collector/events'),
                'logger'      => array($priorities['logger'],      '@WebProfiler/collector/logger'),
                'time'        => array($priorities['time'],        '@WebProfiler/collector/time'),
                'memory'      => array($priorities['memory'],       null),
                'router'      => array($priorities['router'],      '@WebProfiler/collector/router'),
                'security'    => array($priorities['security'],    '@WebProfiler/collector/security'),
                'container'   => array($priorities['container'],   '@WebProfiler/collector/container'),
                'classloader' => array($priorities['classloader'], '@WebProfiler/collector/classloader'),
                'view'        => array($priorities['view'],        '@WebProfiler/collector/view'),
                'doctrine'    => array($priorities['doctrine'],    '@WebProfiler/collector/doctrine'),
                'propel1'     => array($priorities['propel1'],     '@WebProfiler/collector/propel'),
                'guzzle'      => array($priorities['guzzle'],      '@WebProfiler/collector/guzzle'),
                'swiftmailer' => array($priorities['swiftmailer'], '@WebProfiler/collector/swiftmailer'),
                'elastica'    => array($priorities['elastica'],    '@WebProfiler/collector/elastica'),
            );

            return $registry;
        });

        $app['profiler'] = $app->share(function ($app) {
            $profiler = new $app['profiler.class']($app['profiler.storage'], $app['logger']);

            $collectors = new \SplPriorityQueue();
            $order = PHP_INT_MAX;
            foreach ($app['data_collector.registry'] as $id => $attributes) {
                $priority = isset($attributes[0]) ? $attributes[0] : 0;
                if ($priority < 0) {
                    continue;
                }

                $template = null;
                if (isset($attributes[1])) {
                    $template = array($id, $attributes[1]);
                }

                $collectors->insert(array($id, $template), array($priority, --$order));
            }

            $templates = array();
            foreach ($collectors as $collector) {
                $data_collector = $app['data_collector.' . $collector[0]];
                if (-1 !== $data_collector) {
                    $profiler->add($data_collector);
                    $templates[$collector[0]] = $collector[1][1];
                }
            }

            $app['data_collector.templates'] = $templates;

            return $profiler;
        });

        $app['profiler.storage'] = $app->share(function ($app) {
            $supported = array(
                'sqlite'    => 'Symfony\\Component\\HttpKernel\\Profiler\\SqliteProfilerStorage',
                'mysql'     => 'Symfony\\Component\\HttpKernel\\Profiler\\MysqlProfilerStorage',
                'file'      => 'Symfony\\Component\\HttpKernel\\Profiler\\FileProfilerStorage',
                'mongodb'   => 'Symfony\\Component\\HttpKernel\\Profiler\\MongoDbProfilerStorage',
                'memcache'  => 'Symfony\\Component\\HttpKernel\\Profiler\\MemcacheProfilerStorage',
                'memcached' => 'Symfony\\Component\\HttpKernel\\Profiler\\MemcachedProfilerStorage',
                'redis'     => 'Symfony\\Component\\HttpKernel\\Profiler\\RedisProfilerStorage'
            );
            list($class, $dummy) = explode(':', $app['profiler.storage.dsn'], 2);
            if (!isset($supported[$class])) {
                throw new \LogicException(sprintf('Driver "%s" is not supported for the profiler.', $class));
            }

            return new $supported[$class](
                $app['profiler.storage.dsn'],
                $app['profiler.storage.username'],
                $app['profiler.storage.password'],
                $app['profiler.storage.lifetime']
            );
        });

        $app['profiler_listener'] = $app->share(function ($app) {
            return new $app['profiler_listener.class'](
                $app['profiler'],
                $app['profiler.request_matcher'],
                $app['profiler_listener.only_exceptions'],
                $app['profiler_listener.only_master_requests']
            );
        });

        $app['profiler.request_matcher'] = $app->share(function ($app) {
            if (!$app['profiler.request_matcher.path'] &&
                !$app['profiler.request_matcher.host'] &&
                !$app['profiler.request_matcher.methods'] &&
                !$app['profiler.request_matcher.ip']) {
                return null;
            }

            return new $app['profiler.request_matcher.class'](
                $app['profiler.request_matcher.path'],
                $app['profiler.request_matcher.host'],
                $app['profiler.request_matcher.methods'],
                $app['profiler.request_matcher.ip']
            );
        });

        $app['profiler.controller.profiler'] = $app->share(function ($app) {
            return new $app['profiler.controller.profiler.class']($app);
        });
    }

    public function boot(Application $app)
    {
        if (!method_exists($app['dispatcher'], 'setProfiler')) {
            throw new \RuntimeException('TraceableEventDispatcher not loaded. Please ensure that DebugServiceProvider is registered.');
        }

        $app['dispatcher']->setProfiler($app['profiler']);

        $app['dispatcher']->addSubscriber($app['profiler_listener']);

        if (isset($app['data_collector.request']) && isset($app['data_collector.registry']['request'])) {
            $app['dispatcher']->addSubscriber($app['data_collector.request']);
        }

        if (isset($app['data_collector.router']) && isset($app['data_collector.registry']['router'])) {
            $app['dispatcher']->addSubscriber($app['data_collector.router']);
        }
    }
}
