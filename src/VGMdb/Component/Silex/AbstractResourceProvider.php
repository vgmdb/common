<?php

namespace VGMdb\Component\Silex;

use VGMdb\Component\Silex\Loader\YamlFileLoader;
use VGMdb\Component\Silex\Loader\CachedYamlFileLoader;
use Silex\Application as BaseApplication;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Finder\Finder;

/**
 * Base implementation for resource providers, based on the Symfony2 Bundle system.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractResourceProvider implements ResourceProviderInterface
{
    protected $name;
    protected $config = 'config.yml';
    protected $reflected;

    /**
     * Loads a specific configuration.
     *
     * @param BaseApplication $app An application instance
     *
     * @return array
     */
    public function load(BaseApplication $app)
    {
        $options = array(
            'parameters' => array(
                'app.base_dir'  => isset($app['base_dir']) ? $app['base_dir'] : null,
                'app.cache_dir' => isset($app['cache_dir']) ? $app['cache_dir'] : null,
                'app.log_dir'   => isset($app['log_dir']) ? $app['log_dir'] : null,
                'app.env'       => isset($app['env']) ? $app['env'] : null,
                'app.debug'     => isset($app['debug']) ? $app['debug'] : null,
                'app.name'      => isset($app['name']) ? $app['name'] : null
            )
        );

        $paths = array($this->getPath() . '/Resources/config');

        $loader = new YamlFileLoader($app, new FileLocator($paths), $options);

        return $loader->load($this->config);
    }

    /**
     * Checks if the provider is enabled.
     *
     * @return Boolean
     */
    public function isActive()
    {
        return true;
    }

    /**
     * Checks if the provider should be autoloaded.
     *
     * @return Boolean
     */
    public function isAutoload()
    {
        return false;
    }

    /**
     * Returns the parent provider name.
     *
     * @return string The parent name it overrides or null if no parent
     */
    public function getParent()
    {
        return null;
    }

    /**
     * Returns the resource name (the namespace segment).
     *
     * @return string The resource name
     */
    final public function getName()
    {
        if (null !== $this->name) {
            return $this->name;
        }

        $name = $this->getNamespace();
        $pos = strrpos($name, '\\');

        return $this->name = false === $pos ? $name :  substr($name, $pos + 1);
    }

    /**
     * Gets the resource namespace.
     *
     * @return string The resource namespace
     */
    public function getNamespace()
    {
        if (null === $this->reflected) {
            $this->reflected = new \ReflectionObject($this);
        }

        return $this->reflected->getNamespaceName();
    }

    /**
     * Gets the resource directory path.
     *
     * @return string The resource absolute path
     */
    public function getPath()
    {
        if (null === $this->reflected) {
            $this->reflected = new \ReflectionObject($this);
        }

        return dirname($this->reflected->getFileName());
    }

    /**
     * Finds and registers Commands.
     *
     * Override this method if your provider commands do not follow the conventions:
     *
     * * Commands are in the 'Command' sub-directory
     * * Commands extend Symfony\Component\Console\Command\Command
     *
     * @param ConsoleApplication $application A ConsoleApplication instance
     */
    public function registerCommands(ConsoleApplication $application)
    {
        if (!is_dir($dir = $this->getPath().'/Command')) {
            return;
        }

        $finder = new Finder();
        $finder->files()->name('*Command.php')->in($dir);

        $prefix = $this->getNamespace().'\\Command';
        foreach ($finder as $file) {
            $ns = $prefix;
            if ($relativePath = $file->getRelativePath()) {
                $ns .= '\\'.strtr($relativePath, '/', '\\');
            }
            $r = new \ReflectionClass($ns.'\\'.$file->getBasename('.php'));
            if ($r->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$r->isAbstract()) {
                $application->add($r->newInstance());
            }
        }
    }
}
