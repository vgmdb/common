<?php

namespace VGMdb\Provider;

use VGMdb\Component\HttpFoundation\Request;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\THttpClient;
use Thrift\Transport\TBufferedTransport;

/**
 * Provides Apache Thrift transport and protocol.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ThriftServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['thrift.host'] = '127.0.0.1';
        $app['thrift.port'] = 80;
        $app['thrift.uri']  = null;
        $app['thrift.gen_dirs'] = array();

        $app['thrift.transport'] = $app->share(function ($app) {
            if (!isset($app['thrift.uri']) || !strlen($app['thrift.uri'])) {
                $socket = new TSocket($app['thrift.host'], $app['thrift.port']);
            } else {
                $socket = new THttpClient($app['thrift.host'], $app['thrift.port'], $app['thrift.uri']);
            }
            $transport = new TBufferedTransport($socket, 1024, 1024);

            return $transport;
        });

        $app['thrift.protocol'] = $app->share(function ($app) {
            return new TBinaryProtocol($app['thrift.transport']);
        });

        if (class_exists($reader = 'Doctrine\\Common\\Annotations\\AnnotationReader')) {
            $reader::addGlobalIgnoredName('generated'); // for autogenerated Thrift classes
        }

        Request::addFormat('thrift', array('application/x-thrift'));
    }

    public function boot(Application $app)
    {
        // Thrift has its own autoloader :(
        $loader = new ThriftClassLoader();
        foreach ($app['thrift.gen_dirs'] as $namespace => $gen_dirs) {
            $loader->registerDefinition($namespace, $gen_dirs);
        }
        $loader->register();
    }
}