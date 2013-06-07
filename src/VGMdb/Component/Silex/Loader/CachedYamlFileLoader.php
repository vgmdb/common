<?php

namespace VGMdb\Component\Silex\Loader;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * Cached version of YamlFileLoader.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class CachedYamlFileLoader extends YamlFileLoader implements WarmableInterface
{
    protected $resources = array();

    public function load($file, $type = null)
    {
        $cacheClass = implode('', array_map('ucfirst', explode('-', $this->options['cache_class'])));
        $cacheFile = $this->options['cache_dir'] . '/' . $cacheClass . '.php';
        $cache = new ConfigCache($cacheFile, $this->options['debug']);

        if (!$cache->isFresh()) {
            $configs = parent::load($file, $type);

            $cache->write(
                '<?php' . PHP_EOL . '$configs = ' . var_export($configs, true) . ';',
                array_unique($this->resources)
            );
        }

        require $cache;

        $this->replacements = array();

        return $configs;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $this->setOption('cache_dir', $cacheDir);
        $this->setOption('parse', false);

        $this->load($this->options['config_file']);
    }
}
