<?php

namespace VGMdb\Component\Console\Command;

use Silex\Application;
use Symfony\Component\Console\Command\Command;

/**
 * Container aware command.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class ContainerAwareCommand extends Command
{
    /**
     * @var Application|null
     */
    private $container;

    /**
     * @return Application
     */
    protected function getContainer()
    {
        if (null === $this->container) {
            $this->container = $this->getApplication()->getContainer();
        }

        return $this->container;
    }

    /**
     * @param Application $container
     */
    public function setContainer(Application $container = null)
    {
        $this->container = $container;
    }
}
