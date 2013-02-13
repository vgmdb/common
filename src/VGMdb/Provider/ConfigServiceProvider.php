<?php

namespace VGMdb\Provider;

use VGMdb\Component\Config\ConfigLoader;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Provides YAML or JSON configuration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    protected $loader;

    public function __construct(array $options = array())
    {
        $this->loader = new ConfigLoader($options);
    }

    public function register(Application $app)
    {
        $this->loader->load($app);
    }

    public function boot(Application $app)
    {
    }
}
