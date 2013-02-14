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
        $required = array(
            'config.debug',
            'config.cache_dir',
            'config.cache_class',
            'config.base_dirs',
            'config.files'
        );

        foreach ($required as $key) {
            if (!array_key_exists($key, $this->options)) {
                throw new \RuntimeException(sprintf('Config service parameter missing: "%s"', $key));
            }
            $app[$key] = $this->options[$key];
        }

        if ($app['nocache']) {
            $loader = new ConfigLoader($this->options);
        } else {
            $loader = new CachedConfigLoader($this->options);
        }

        $loader->load($app);
    }

    public function boot(Application $app)
    {
    }
}
