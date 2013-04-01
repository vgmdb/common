<?php

namespace VGMdb\Component\Silex;

/**
 * Resource locator. Extracted from the Bundle handling functions of the Symfony2 Kernel.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ResourceLocator implements ResourceLocatorInterface
{
    protected $providers;
    protected $providerMap;

    /**
     * {@inheritdoc}
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider($name, $first = true)
    {
        if (!isset($this->providerMap[$name])) {
            throw new \InvalidArgumentException(sprintf('Provider "%s" does not exist or it is not enabled.', $name));
        }

        if (true === $first) {
            return $this->providerMap[$name][0];
        }

        return $this->providerMap[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function locateResource($name, $dir = null, $first = true)
    {
        if ('@' !== $name[0]) {
            throw new \InvalidArgumentException(sprintf('A resource name must start with @ ("%s" given).', $name));
        }

        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $name));
        }

        $providerName = substr($name, 1);
        $path = '';
        if (false !== strpos($providerName, '/')) {
            list($providerName, $path) = explode('/', $providerName, 2);
        }

        $isResource = 0 === strpos($path, 'Resources') && null !== $dir;
        $overridePath = substr($path, 9);
        $resourceProvider = null;
        $providers = $this->getProvider($providerName, false);
        $files = array();

        foreach ($providers as $provider) {
            if ($isResource && file_exists($file = $dir.'/'.$provider->getName().$overridePath)) {
                if (null !== $resourceProvider) {
                    throw new \RuntimeException(sprintf('"%s" resource is hidden by a resource from the "%s" derived provider. Create a "%s" file to override the provider resource.',
                        $file,
                        $resourceProvider,
                        $dir.'/'.$providers[0]->getName().$overridePath
                    ));
                }

                if ($first) {
                    return $file;
                }
                $files[] = $file;
            }

            if (file_exists($file = $provider->getPath().'/'.$path)) {
                if ($first && !$isResource) {
                    return $file;
                }
                $files[] = $file;
                $resourceProvider = $provider->getName();
            }
        }

        if (count($files) > 0) {
            return $first && $isResource ? $files[0] : $files;
        }

        throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $name));
    }

    /**
     * Initializes the data structures related to resource management.
     * - the providers property maps a name to the provider instance,
     * - the providerMap property maps a name to the provider inheritance hierarchy (most derived first).
     *
     * @param ResourceProviderInterface[] $providers Array of resource providers
     *
     * @throws \LogicException if two providers share a common name
     * @throws \LogicException if a provider tries to extend a non-registered provider
     * @throws \LogicException if a provider tries to extend itself
     * @throws \LogicException if two providers extend the same ancestor
     */
    public function initialize(array $providers)
    {
        // init providers
        $this->providers = array();
        $topMostProviders = array();
        $directChildren = array();

        foreach ($providers as $provider) {
            $name = $provider->getName();
            if (isset($this->providers[$name])) {
                throw new \LogicException(sprintf('Trying to register two providers with the same name "%s"', $name));
            }
            $this->providers[$name] = $provider;

            if ($parentName = $provider->getParent()) {
                if (isset($directChildren[$parentName])) {
                    throw new \LogicException(sprintf('Provider "%s" is directly extended by two providers "%s" and "%s".', $parentName, $name, $directChildren[$parentName]));
                }
                if ($parentName == $name) {
                    throw new \LogicException(sprintf('Provider "%s" can not extend itself.', $name));
                }
                $directChildren[$parentName] = $name;
            } else {
                $topMostProviders[$name] = $provider;
            }
        }

        // look for orphans
        if (count($diff = array_values(array_diff(array_keys($directChildren), array_keys($this->providers))))) {
            throw new \LogicException(sprintf('Provider "%s" extends provider "%s", which is not registered.', $directChildren[$diff[0]], $diff[0]));
        }

        // inheritance
        $this->providerMap = array();
        foreach ($topMostProviders as $name => $provider) {
            $providerMap = array($provider);
            $hierarchy = array($name);

            while (isset($directChildren[$name])) {
                $name = $directChildren[$name];
                array_unshift($providerMap, $this->providers[$name]);
                $hierarchy[] = $name;
            }

            foreach ($hierarchy as $providerName) {
                $this->providerMap[$providerName] = $providerMap;
                array_pop($providerMap);
            }
        }
    }
}
