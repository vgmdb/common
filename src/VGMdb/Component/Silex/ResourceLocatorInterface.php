<?php

namespace VGMdb\Component\Silex;

/**
 * Interface for resource locators.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface ResourceLocatorInterface
{
    /**
     * Gets the registered resource providers.
     *
     * @return ResourceProviderInterface[] An array of registered resource providers
     */
    public function getProviders();

    /**
     * Returns a resource provider and optionally its descendants by its name.
     *
     * @param string  $name  Provider name
     * @param Boolean $first Whether to return the first provider only or together with its descendants
     *
     * @return ResourceProviderInterface|ResourceProviderInterface[]
     *
     * @throws \InvalidArgumentException when the provider is not enabled
     */
    public function getProvider($name, $first = true);

    /**
     * Returns the file path for a given resource.
     *
     * A Resource can be a file or a directory.
     *
     * The resource name must follow the following pattern:
     *
     *     @ProviderName/path/to/a/file.something
     *
     * where ProviderName is the combined provider namespace
     * and the remaining part is the relative path in the namespace.
     *
     * If $dir is passed, and the first segment of the path is Resources,
     * this method will look for a file named:
     *
     *     $dir/ProviderName/path/without/Resources
     *
     * @param string  $name  A resource name to locate
     * @param string  $dir   A directory where to look for the resource first
     * @param Boolean $first Whether to return the first path or paths for all matching providers
     *
     * @return string|array The absolute path of the resource or an array if $first is false
     *
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     * @throws \RuntimeException         if the name contains invalid/unsafe characters
     */
    public function locateResource($name, $dir = null, $first = true);
}
