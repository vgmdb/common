<?php

namespace VGMdb;

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
     * Returns the resource name (the namespace segment).
     *
     * @return string The resource name
     */
    public function getName();

    /**
     * Gets the resource namespace.
     *
     * @return string The resource namespace
     */
    public function getNamespace();

    /**
     * Gets the resource directory path.
     *
     * The path should always be returned as a Unix path (with /).
     *
     * @return string The resource absolute path
     */
    public function getPath();
}
