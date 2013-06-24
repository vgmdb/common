<?php

namespace VGMdb\Component\Validator;

use VGMdb\Component\Validator\Mapping\Cache\FilesystemCache;
use Silex\Application;
use Silex\Provider\ValidatorServiceProvider as BaseValidatorServiceProvider;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\LoaderChain;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Mapping\Loader\YamlFilesLoader;
use Doctrine\Common\Cache\FilesystemCache as BaseFilesystemCache;

/**
 * Symfony Validator component Provider.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ValidatorServiceProvider extends BaseValidatorServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $app['validator.mapping.class_metadata_factory'] = $app->share(function ($app) {
            return new ClassMetadataFactory(
                $app['validator.mapping.loader.loader_chain'],
                $app['validator.mapping.cache.filesystem']
            );
        });

        $app['validator.mapping.loader.loader_chain'] = $app->share(function ($app) {
            return new LoaderChain(array(
                new StaticMethodLoader(),
                new YamlFilesLoader($app['validator.mapping.loader.files'])
            ));
        });

        $app['validator.mapping.loader.files'] = $app->share(function ($app) {
            $directories = array($app['validator.base_dir']);
            foreach ($app['resource_locator']->getProviders() as $provider) {
                $directories[] = $provider->getPath() . '/Resources/config/validation';
            }

            $files = array();
            foreach ($directories as $directory) {
                $files = array_merge($files, (array) glob($directory . '/*.yml'));
            }

            return $files;
        });

        $app['validator.mapping.cache.filesystem'] = $app->share(function ($app) {
            return new FilesystemCache(new BaseFilesystemCache($app['validator.cache_dir']));
        });
    }
}
