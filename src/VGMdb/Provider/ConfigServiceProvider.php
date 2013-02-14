<?php

namespace VGMdb\Provider;

use VGMdb\Component\Config\ConfigLoader;
use VGMdb\Component\Config\CachedConfigLoader;
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
        $app['config.options'] = array(
            'debug'       => isset($this->options['config.debug']) ? (Boolean) $this->options['config.debug'] : false,
            'cache_dir'   => $this->options['config.cache_dir'],
            'cache_class' => $this->options['config.cache_class'],
            'base_dirs'   => $this->options['config.base_dirs'],
            'files'       => $this->options['config.files'],
            'parameters'  => isset($this->options['config.parameters']) ? $this->options['config.parameters'] : array()
        );

        if ($app['cache']) {
            $app['config.loader'] = new CachedConfigLoader($app['config.options']);
        } else {
            $app['config.loader'] = new ConfigLoader($app['config.options']);
        }

        $app['config.loader']->load($app);
    }

    public function boot(Application $app)
    {
    }
}
