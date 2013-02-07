<?php

namespace VGMdb\Component\DomainObject;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

/**
 * Base class for domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractDomainObject implements DomainObjectInterface
{
    protected $logger;
    protected $dispatcher;

    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
    }
}
