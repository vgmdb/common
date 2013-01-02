<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Provides Symfony's profiling tools.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ProfilerServiceProvider implements ServiceProviderInterface
{
    private $enabled;

    public function __construct($enabled = true)
    {
        $this->enabled = (Boolean) $enabled;
    }

    public function register(Application $app)
    {
        if (!$this->enabled) {
            return;
        }

        // defaults
        $app['profiler.class'] = 'Symfony\\Component\\HttpKernel\\Profiler\\Profiler';
        $app['profiler_listener.class'] = 'Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener';
        $app['profiler_listener.only_exceptions'] = false;
        $app['profiler_listener.only_master_requests'] = false;
        $app['profiler.request_matcher.class'] = 'Symfony\\Component\\HttpFoundation\\RequestMatcher';
        $app['profiler.request_matcher.path'] = null;
        $app['profiler.request_matcher.host'] = null;
        $app['profiler.request_matcher.methods'] = null;
        $app['profiler.request_matcher.ip'] = null;
        $app['profiler.controller.profiler'] = 'VGMdb\\Component\\WebProfiler\\Controllers\\ProfilerController';

        // data collector classes
        $app['data_collector.config.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\ConfigDataCollector';
        $app['data_collector.request.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\RequestDataCollector';
        $app['data_collector.exception.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\ExceptionDataCollector';
        $app['data_collector.events.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\EventDataCollector';
        $app['data_collector.logger.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\LoggerDataCollector';
        $app['data_collector.time.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\TimeDataCollector';
        $app['data_collector.memory.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\MemoryDataCollector';
        $app['data_collector.router.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\RouterDataCollector';
        $app['data_collector.security.class'] = 'VGMdb\\Component\\Security\\DataCollector\\SecurityDataCollector';
        $app['data_collector.container.class'] = 'VGMdb\\Component\\HttpKernel\\DataCollector\\ContainerDataCollector';
        $app['data_collector.view.class'] = 'VGMdb\\Component\\View\\DataCollector\\ViewDataCollector';
        $app['data_collector.db.class'] = 'VGMdb\\Component\\Doctrine\\DataCollector\\DoctrineDataCollector';
        $app['data_collector.propel.class'] = 'VGMdb\\Component\\Propel\\DataCollector\\PropelDataCollector';
        $app['data_collector.guzzle.class'] = 'VGMdb\\Component\\Guzzle\\DataCollector\\GuzzleDataCollector';

        // data collectors. remember to guard against nonexistent providers by returning -1
        $app['data_collector.config'] = $app->share(function () use ($app) {
            return new $app['data_collector.config.class']($app);
        });
        $app['data_collector.request'] = $app->share(function () use ($app) {
            return new $app['data_collector.request.class']();
        });
        $app['data_collector.exception'] = $app->share(function () use ($app) {
            return new $app['data_collector.exception.class']();
        });
        $app['data_collector.events'] = $app->share(function () use ($app) {
            return new $app['data_collector.events.class']($app['dispatcher']);
        });
        $app['data_collector.logger'] = $app->share(function () use ($app) {
            if (!isset($app['logger'])) {
                return -1;
            }
            return new $app['data_collector.logger.class']($app['logger']);
        });
        $app['data_collector.time'] = $app->share(function () use ($app) {
            return new $app['data_collector.time.class']($app);
        });
        $app['data_collector.memory'] = $app->share(function () use ($app) {
            return new $app['data_collector.memory.class']();
        });
        $app['data_collector.router'] = $app->share(function () use ($app) {
            return new $app['data_collector.router.class']();
        });
        $app['data_collector.security'] = $app->share(function () use ($app) {
            return new $app['data_collector.security.class']($app['security']);
        });
        $app['data_collector.container'] = $app->share(function () use ($app) {
            return new $app['data_collector.container.class']($app);
        });
        $app['data_collector.view'] = $app->share(function () use ($app) {
            return new $app['data_collector.view.class']($app['view.logger']);
        });
        $app['data_collector.db'] = $app->share(function () use ($app) {
            if (!isset($app['db'])) {
                return -1;
            }
            $collector = new $app['data_collector.db.class']($app);
            $collector->addLogger($app['dbs.default'], $app['db.debug_logger']);

            return $collector;
        });
        $app['data_collector.propel'] = $app->share(function () use ($app) {
            if (!isset($app['propel.configuration'])) {
                return -1;
            }
            return new $app['data_collector.propel.class']($app['propel.logger'], $app['propel.configuration']);
        });
        $app['data_collector.guzzle'] = $app->share(function () use ($app) {
            if (!isset($app['guzzle'])) {
                return -1;
            }
            return new $app['data_collector.guzzle.class']($app['guzzle.debug_logger.adapter']);
        });

        $priorities = $app['profiler.options']['priorities'];
        $app['data_collector.registry'] = array(
            'config'    => array($priorities['config'],    'collector/config'),
            'request'   => array($priorities['request'],   'collector/request'),
            'exception' => array($priorities['exception'], 'collector/exception'),
            'events'    => array($priorities['events'],    'collector/events'),
            'logger'    => array($priorities['logger'],    'collector/logger'),
            'time'      => array($priorities['time'],      'collector/time'),
            'memory'    => array($priorities['memory'],     null),
            'router'    => array($priorities['router'],    'collector/router'),
            'security'  => array($priorities['security'],  'collector/security'),
            'container' => array($priorities['container'], 'collector/container'),
            'view'      => array($priorities['view'],      'collector/view'),
            'db'        => array($priorities['db'],        'collector/db'),
            'propel'    => array($priorities['propel'],    'collector/propel'),
            'guzzle'    => array($priorities['guzzle'],    'collector/guzzle'),
        );

        $app['profiler'] = $app->share(function () use ($app) {
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

        $app['profiler.storage'] = $app->share(function () use ($app) {
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

        $app['profiler_listener'] = $app->share(function () use ($app) {
            return new $app['profiler_listener.class'](
                $app['profiler'],
                $app['profiler.request_matcher'],
                $app['profiler_listener.only_exceptions'],
                $app['profiler_listener.only_master_requests']
            );
        });

        $app['profiler.request_matcher'] = $app->share(function () use ($app) {
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
    }

    public function boot(Application $app)
    {
        if (!$this->enabled) {
            return;
        }

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
