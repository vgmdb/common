<?php

namespace VGMdb\Component\DomainObject\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Listener for domain object retrievals.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DomainObjectListener
{
    protected $serializer;
    protected $dispatcher;
    protected $logger;

    public function __construct(SerializerInterface $serializer, EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        $this->serializer = $serializer;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }
}
