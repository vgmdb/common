<?php

namespace VGMdb\Component\Config;

use VGMdb\Component\Silex\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Provides YAML or JSON configuration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    protected $options;

    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    public function register(Application $app)
    {
        $options = array(
            'debug'       => isset($this->options['config.debug']) ? (Boolean) $this->options['config.debug'] : false,
            'cache_dir'   => $this->options['config.cache_dir'],
            'cache_class' => $this->options['config.cache_class'],
            'base_dirs'   => $this->options['config.base_dirs'],
            'files'       => $this->options['config.files'],
            'parameters'  => array_merge(
                array(
                    'app.base_dir'  => $app['base_dir'],
                    'app.env'       => $app['env'],
                    'app.debug'     => $app['debug'],
                    'app.name'      => $app['name'],
                    'app.cache_dir' => $app['cache_dir'],
                    'app.log_dir'   => $app['log_dir']
                ),
                isset($this->options['config.parameters']) ? $this->options['config.parameters'] : array()
            )
        );

        $app['config.locator'] = new FileLocator($this->options['config.base_dirs']);

        $app['config.loader'] = new DelegatingLoader(new LoaderResolver(array(
            new YamlFileLoader($app, $app['config.locator'], $options)
        )));

        foreach ($this->options['config.base_dirs'] as $dir) {
            foreach ($this->options['config.files'] as $file) {
                if (file_exists($dir . '/' . $file . '.dist')) {
                    foreach ($app['config.loader']->load($dir . '/' . $file . '.dist') as $key => $value) {
                        $app[$key] = $value;
                    }
                }
                if (file_exists($dir . '/' . $file)) {
                    foreach ($app['config.loader']->load($dir . '/' . $file) as $key => $value) {
                        $app[$key] = $value;
                    }
                }
            }
        }
    }

    public function boot(Application $app)
    {
    }
}
