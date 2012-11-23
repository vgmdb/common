<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Assetic\AssetManager;
use Assetic\FilterManager;
use Assetic\AssetWriter;
use Assetic\Asset\AssetCache;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\LazyAssetManager;
use Assetic\Cache\FilesystemCache;
use Assetic\Filter\Yui\CssCompressorFilter;
use Assetic\Filter\Yui\JsCompressorFilter;

/**
 * Asset management.
 *
 * @author Michael Heap <m@michaelheap.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class AsseticServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['assetic.options'] = array();
        if (!isset($app['assetic.assets'])) {
            $app['assetic.assets'] = $app->protect(function() {});
        }

        /**
         * Asset Factory configuration happens here
         */
        $app['assetic'] = $app->share(function () use ($app) {
            $app['assetic.options'] = array_replace(
                array(
                    'debug' => false,
                    'formulae_cache_dir' => null,
                    'auto_dump_assets' => true,
                ), $app['assetic.options']
            );

            // initializing lazy asset manager
            if (isset($app['assetic.formulae']) &&
               !is_array($app['assetic.formulae']) &&
               !empty($app['assetic.formulae'])
            ) {
                $app['assetic.lazy_asset_manager'];
            }

            return $app['assetic.factory'];
        });

        /**
         * Factory
         * @return Assetic\Factory\AssetFactory
         */
        $app['assetic.factory'] = $app->share(function() use ($app) {
            $factory = new AssetFactory($app['assetic.web_path'], $app['assetic.options']['debug']);
            $factory->setAssetManager($app['assetic.asset_manager']);
            $factory->setFilterManager($app['assetic.filter_manager']);

            return $factory;
        });

        /**
         * Asset writer, writes to the 'assetic.web_path' folder
         */
        $app['assetic.asset_writer'] = $app->share(function () use ($app) {
            return new AssetWriter($app['assetic.web_path']);
        });

        /**
         * Asset manager, can be accessed via $app['assetic.asset_manager']
         * and can be configured via $app['assetic.assets'], just provide a
         * protected callback $app->protect(function($am) { }) and add
         * your assets inside the function to asset manager ($am->set())
         */
        $app['assetic.asset_manager'] = $app->share(function () use ($app) {
            $manager = new AssetManager();

            call_user_func_array($app['assetic.assets'], array($manager, $app['assetic.filter_manager']));

            return $manager;
        });

        /**
         * Filter manager, can be accessed via $app['assetic.filter_manager']
         * and can be configured via $app['assetic.filters']
         */
        $app['assetic.filter_manager'] = $app->share(function () use ($app) {
            $filters = $app['assetic.filters'];
            $filter_manager = new FilterManager();

            if (count($filters)) {
                foreach ($filters as $name => $parameters) {
                    list($class, $arguments) = $parameters;
                    if (!class_exists($class)) {
                        throw new \ErrorException(sprintf('No such filter: %s', $class));
                    }
                    if (!is_array($arguments)) {
                        $arguments = array($arguments);
                    }
                    $reflection = new \ReflectionClass($class);
                    $filter_manager->set($name, $reflection->newInstanceArgs($arguments));
                }
            }

            return $filter_manager;
        });

        $app['assetic.filters'] = array();

        /**
         * Lazy asset manager for loading assets from $app['assetic.formulae']
         */
        $app['assetic.lazy_asset_manager'] = $app->share(function () use ($app) {
            $formulae = isset($app['assetic.formulae']) ? $app['assetic.formulae'] : array();
            $options  = $app['assetic.options'];
            $lazy     = new LazyAssetmanager($app['assetic.factory']);

            if (empty($formulae)) {
                return $lazy;
            }

            foreach ($formulae as $name => $formula) {
                $lazy->setFormula($name, $formula);
            }

            if ($options['formulae_cache_dir'] !== null) {
                foreach ($lazy->getNames() as $name) {
                    $lazy->set($name, new AssetCache(
                        $lazy->get($name),
                        new FilesystemCache($options['formulae_cache_dir'])
                    ));
                }
            }

            return $lazy;
        });

        /**
         * Writes down all lazy asset manager and asset managers assets
         */
        $app['assetic.dumper'] = $app->protect(function () use ($app) {
            foreach ($app['assetic.asset_manager']->getNames() as $name) {
                $asset = $app['assetic.asset_manager']->get($name);
                $app['assetic.asset_writer']->writeAsset($asset);
            }

            foreach ($app['assetic.lazy_asset_manager']->getNames() as $name) {
                $asset = $app['assetic.lazy_asset_manager']->get($name);
                $formula = $app['assetic.lazy_asset_manager']->getFormula($name);
                $app['assetic.asset_writer']->writeAsset($asset);

                if (!isset($formula[2])) {
                    continue;
                }

                $debug   = isset($formula[2]['debug'])   ? $formula[2]['debug']   : $app['assetic.lazy_asset_manager']->isDebug();
                $combine = isset($formula[2]['combine']) ? $formula[2]['combine'] : null;

                if (null !== $combine ? !$combine : $debug) {
                    foreach ($asset as $leaf) {
                        $app['assetic.asset_writer']->writeAsset($leaf);
                    }
                }
            }
        });
    }

    public function boot(Application $app)
    {
        $app->after(function() use ($app) {
            // Boot assetic
            $assetic = $app['assetic'];

            if (!isset($app['assetic.options']) ||
                !isset($app['assetic.options']['auto_dump_assets']) ||
                !$app['assetic.options']['auto_dump_assets']
            ) {
                return;
            }

            $dumper = $app['assetic.dumper'];
            $dumper();
        });
    }
}
