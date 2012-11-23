<?php

namespace VGMdb\Provider;

use VGMdb\Component\DBAL\Logging\SQLDebugLogger;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider as BaseDoctrineServiceProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ApcCache;

/**
 * Doctrine DBAL and ORM Provider.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DoctrineServiceProvider extends BaseDoctrineServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $app['entity_manager'] = $app->share(function () use ($app) {
            if (!isset($app['orm.entity_dir'])) {
                throw new \RuntimeException('The orm.entity_dir path is not set.');
            }
            if (!isset($app['orm.proxy_dir'])) {
                throw new \RuntimeException('The orm.proxy_dir path is not set.');
            }
            if (!isset($app['orm.proxy_namespace'])) {
                throw new \RuntimeException('The orm.proxy_namespace path is not set.');
            }
            $cache = ($app['debug'] || !extension_loaded('apc')) ? new ArrayCache : new ApcCache;
            $config = new Configuration;
            $config->setMetadataCacheImpl($cache);
            $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver($app['orm.entity_dir'], false));
            $config->setQueryCacheImpl($cache);
            $config->setProxyDir($app['orm.proxy_dir']);
            $config->setProxyNamespace($app['orm.proxy_namespace']);
            $config->setAutoGenerateProxyClasses($app['debug']);
            $em = EntityManager::create($app['db'], $config);

            return $em;
        });
    }

    public function boot(Application $app)
    {
        // force Doctrine annotations to be loaded
        // should be removed when a better solution is found in Doctrine
        if (isset($app['orm.entity_dir'])) {
            class_exists('Doctrine\ORM\Mapping\Driver\AnnotationDriver');
        }

        if (isset($app['orm.proxy_dir'])) {
            $namespace = $app['orm.proxy_namespace'];
            $dir = $app['orm.proxy_dir'];
            spl_autoload_register(function ($class) use ($namespace, $dir) {
                if (0 === strpos($class, $namespace)) {
                    $className = str_replace('\\', '', substr($class, strlen($namespace) + 1));
                    $file = $dir.DIRECTORY_SEPARATOR.$className.'.php';
                    if (file_exists($file)) {
                        require $file;
                    }
                }
            });
        }

        if ($app['debug'] && isset($app['monolog'])) {
            //$logger = new SQLErrorLogger($app['db.logfile']);
            $logger = new SQLDebugLogger($app['monolog']);
            $app['db.config']->setSQLLogger($logger);
            /*$app->finish(function ($request, $response) use ($app, $logger) {
                if (isset($logger->queries) && count($logger->queries)) {
                    foreach ($logger->queries as $query) {
                        $app['monolog']->debug('[' . $query['executionMS'] . '] ' . $query['sql'], array(
                            'params' => $query['params'],
                            'types' => $query['types']
                        ));
                    }
                }
            });*/
        }
    }
}
