<?php

namespace VGMdb\Component\DomainObject;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Factory for domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class DomainObjectFactory
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
