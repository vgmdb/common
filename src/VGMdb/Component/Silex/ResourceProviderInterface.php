<?php

namespace VGMdb\Component\Silex;

/**
 * Interface for resource providers.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface ResourceProviderInterface
{
    /**
     * Builds the resource.
     *
     * It is only ever called once when the cache is empty.
     */
    public function build();

    /**
     * Returns the provider name that this provider overrides.
     *
     * @return string The provider name it overrides or null if no parent
     */
    public function getParent();

    /**
     * Returns the provider name (the namespace segment).
     *
     * @return string The provider name
     */
    public function getName();

    /**
     * Gets the provider namespace.
     *
     * @return string The provider namespace
     */
    public function getNamespace();

    /**
     * Gets the provider directory path.
     *
     * The path should always be returned as a Unix path (with /).
     *
     * @return string The provider absolute path
     */
    public function getPath();
}
