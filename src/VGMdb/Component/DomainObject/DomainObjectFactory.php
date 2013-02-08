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
    protected $classMap;
    protected $dispatcher;
    protected $logger;
    protected $serializer;

    public function __construct(array $classMap, EventDispatcherInterface $dispatcher, LoggerInterface $logger = null, SerializerInterface $serializer = null)
    {
        $this->classMap = $classMap;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    public function create($domain, $type = 'array')
    {
        if (!array_key_exists($type, $this->classMap)) {
            throw new \InvalidArgumentException(sprintf('No DomainObject wrapper found for type "%s".', $type));
        }
    }

    public function createFromObject($object, $type = null)
    {
    }
}
