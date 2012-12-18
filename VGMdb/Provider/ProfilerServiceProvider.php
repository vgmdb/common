<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpKernel\Debug\Stopwatch;

/**
 * Provides Symfony's profiling tools.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ProfilerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // defaults
        $app['profiler.class'] = 'Symfony\\Component\\HttpKernel\\Profiler\\Profiler';
        $app['profiler.enabled'] = $app['debug'];
        $app['profiler_listener.class'] = 'Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener';
        $app['profiler_listener.only_exceptions'] = false;
        $app['profiler_listener.only_master_requests'] = false;
        $app['profiler.request_matcher.class'] = 'Symfony\\Component\\HttpFoundation\\RequestMatcher';
        $app['profiler.request_matcher.path'] = null;
        $app['profiler.request_matcher.host'] = null;
        $app['profiler.request_matcher.methods'] = null;
        $app['profiler.request_matcher.ip'] = null;

        // data collectors
        $app['data_collector.time.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\TimeDataCollector';
        $app['data_collector.memory.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\MemoryDataCollector';
        $app['data_collector.events.class'] = 'Symfony\\Component\\HttpKernel\\DataCollector\\EventDataCollector';

        $app['data_collector.time'] = $app->share(function () use ($app) {
            return new $app['data_collector.time.class']();
        });
        $app['data_collector.memory'] = $app->share(function () use ($app) {
            return new $app['data_collector.memory.class']();
        });
        $app['data_collector.events'] = $app->share(function () use ($app) {
            return new $app['data_collector.events.class']($app['dispatcher']);
        });

        $app['data_collector.config'] = array(
            'time' => array(255, 'profiler/time'),
            'memory' => array(255, 'profiler/memory'),
            'events' => array(255, 'profiler/events'),
        );

        // replace dispatcher class with traceable implementation
        $app['dispatcher_class'] = 'VGMdb\\Component\\HttpKernel\\Debug\\TraceableEventDispatcher';
        $app['dispatcher.proto'] = $app->share(function () use ($app) {
            $dispatcher = new $app['dispatcher_class']($app['debug.stopwatch'], $app['logger']);
            $dispatcher->setProfiler($app['profiler']);

            return $dispatcher;
        });
        $app['debug.stopwatch'] = $app->share(function () use ($app) {
            return new Stopwatch();
        });

        $app['profiler'] = $app->share(function () use ($app) {
            $profiler = new $app['profiler.class']($app['profiler.storage'], $app['logger']);

            $collectors = new \SplPriorityQueue();
            $order = PHP_INT_MAX;
            foreach ($app['data_collector.config'] as $id => $attributes) {
                $priority = isset($attributes[0]) ? $attributes[0] : 0;
                $template = null;

                if (isset($attributes[1])) {
                    $template = array($id, $attributes[1]);
                }

                $collectors->insert(array($id, $template), array($priority, --$order));
            }

            $templates = array();
            foreach ($collectors as $collector) {
                $data_collector = $app['data_collector.' . $collector[0]];
                $profiler->add($data_collector);
                $templates[$collector[0]] = $collector[1];
            }

            $app['data_collector.templates'] = $templates;

            if (!$app['profiler.enabled']) {
                $profiler->disable();
            }

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
        $app['dispatcher']->addSubscriber($app['profiler_listener']);
    }
}
