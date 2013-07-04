<?php

namespace VGMdb\Component\Cache;

use VGMdb\Application;
use Stash\Pool;
use Stash\Driver\Composite;
use Psr\Log\LoggerInterface;

/**
 * Factory that creates cache pools.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class CacheFactory
{
    protected $app;
    protected $configs;
    protected $pools;

    public function __construct(Application $app, array $configs = array())
    {
        $this->app = $app;
        $this->configs = $configs;
        $this->pools = array();
    }

    public function getCache($name)
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        if (!isset($this->configs[$name])) {
            throw new \InvalidArgumentException(sprintf('The cache pool "%s" is not configured.', $name));
        }

        $config = $this->configs[$name];
        $drivers = array('drivers' => array());

        foreach ($config['drivers'] as $driver) {
            if (!isset($this->app['cache.driver.'.$driver])) {
                continue;
            }
            try {
                $drivers['drivers'][] = $this->app['cache.driver.'.$driver];
            } catch (\Exception $e) {
                continue;
            }
        }

        if (!count($drivers['drivers'])) {
            $drivers['drivers'][] = $this->app['cache.driver.ephemeral'];
        }

        return $this->pools[$name] = new Pool(new Composite($drivers));
    }
}
