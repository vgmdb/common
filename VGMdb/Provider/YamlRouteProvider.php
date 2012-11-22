<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * @brief       Provides routes loaded from YAML configuration.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class YamlRouteProvider implements ControllerProviderInterface
{
    private $path;
    private $locale;

    public function __construct($path, $locale = null)
    {
        $this->path = $path;
        $this->locale = $locale;
    }

    public function connect(Application $app)
    {
        $locator = new FileLocator($this->path);
        $loader = new YamlFileLoader($locator);
        return $loader->load($this->path);
    }
}
