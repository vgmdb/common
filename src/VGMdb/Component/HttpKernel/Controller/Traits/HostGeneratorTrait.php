<?php

namespace VGMdb\Component\HttpKernel\Controller\Traits;

/**
 * HostGenerator trait.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
trait HostGeneratorTrait
{
    /**
     * Generates a hostname from configuration.
     *
     * @param string  $name       The name of the host
     * @param Boolean $withScheme Whether to include the scheme
     *
     * @return string The generated hostname
     */
    public function host($name, $withScheme = false)
    {
        $hostKey = sprintf('%s.host', $name);
        $schemeKey = sprintf('%s.scheme', $name);

        return ($withScheme ? sprintf('%s://', $this->app['app.hosts'][$schemeKey]) : '') . $this->app['app.hosts'][$hostKey];
    }
}
