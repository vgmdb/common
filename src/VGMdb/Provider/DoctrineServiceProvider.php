<?php

namespace VGMdb\Provider;

use VGMdb\Component\Doctrine\Registry;
use VGMdb\Component\Doctrine\DBAL\Logging\SQLDebugLogger;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider as BaseDoctrineServiceProvider;
use Doctrine\DBAL\Logging\LoggerChain;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;

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

        $app['entity_managers'] = $app->share(function ($app) {
            $entityManagers = array();

            if (!isset($app['orm.cache_dir'])) {
                throw new \RuntimeException('The orm.cache_dir path is not set.');
            }
            if (!isset($app['orm.entity_dir'])) {
                throw new \RuntimeException('The orm.entity_dir path is not set.');
            }
            if (!isset($app['orm.proxy_dir'])) {
                throw new \RuntimeException('The orm.proxy_dir path is not set.');
            }
            if (!isset($app['orm.proxy_namespace'])) {
                throw new \RuntimeException('The orm.proxy_namespace path is not set.');
            }

            $metadataCache = ($app['debug'] || !extension_loaded('apc'))
                ? new FilesystemCache($app['orm.cache_dir'])
                : new ApcCache();

            $queryCache = ($app['debug'] || !extension_loaded('apc'))
                ? new FilesystemCache($app['orm.cache_dir'])
                : new ApcCache();

            $driver = new AnnotationDriver(
                new FileCacheReader(new AnnotationReader(), $app['orm.cache_dir'], $app['debug']),
                (array) $app['orm.entity_dir']
            );

            $ref = new \ReflectionClass('Doctrine\\ORM\\Configuration');
            AnnotationRegistry::registerFile(dirname($ref->getFilename()) . '/Mapping/Driver/DoctrineAnnotations.php');

            foreach ($app['orm.entity_managers'] as $name => $options) {
                $app['entity_manager.' . $name] = $app->share(function ($app) use ($options, $metadataCache, $queryCache, $driver) {
                    $config = new Configuration();
                    $config->setMetadataCacheImpl($metadataCache);
                    $config->setMetadataDriverImpl($driver);
                    $config->setQueryCacheImpl($queryCache);
                    $config->setProxyDir($app['orm.proxy_dir']);
                    $config->setProxyNamespace($app['orm.proxy_namespace']);
                    $config->setAutoGenerateProxyClasses($app['debug']);

                    $connections = $app['dbs'];
                    $entityManager = EntityManager::create($connections[$options['connection']], $config);

                    return $entityManager;
                });

                $entityManagers[$name] = 'entity_manager.' . $name;
            }

            return $entityManagers;
        });

        $app['entity_manager'] = $app->share(function ($app) {
            $entityManagers = $app['entity_managers'];
            $defaultManager = reset($entityManagers);

            return $app[$defaultManager];
        });

        $app['doctrine'] = $app->share(function ($app) {
            $dbs = $app['dbs'];
            $connections = array();
            foreach (array_keys($app['dbs.options']) as $name) {
                $app['doctrine.dbal.' . $name] = $dbs[$name];
                $connections[$name] = 'doctrine.dbal.' . $name;
            }

            return new Registry(
                $app,
                $connections,
                $app['entity_managers'],
                $app['dbal.default_connection'],
                $app['orm.default_entity_manager']
            );
        });

        $app['db.logger'] = $app->share(function ($app) {
            return new LoggerChain();
        });
    }

    public function boot(Application $app)
    {
        // force Doctrine annotations to be loaded
        // should be removed when a better solution is found in Doctrine
        if (isset($app['orm.entity_dir'])) {
            class_exists('Doctrine\\ORM\\Mapping\\Driver\\AnnotationDriver');
        }

        // autoload Doctrine proxies. Not PSR-0 compliant!
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

        $app['db.config']->setSQLLogger($app['db.logger']);
    }
}
