<?php

namespace VGMdb\Component\Silex\Loader;

/**
 * Processes the configuration returned by the loader.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface ConfigPassInterface
{
    public function process(array $config);
}
