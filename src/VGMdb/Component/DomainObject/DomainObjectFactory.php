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
class DomainObjectFactory
{
    protected $config;
    protected $dispatcher;
    protected $logger;

    public function __construct(array $config, EventDispatcherInterface $dispatcher, LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    public function create($data, $domain = null)
    {
        $classes = $this->config['classes'];

        if ($domain && isset($classes[$domain])) {
            $class = $classes[$domain];
        } else {
            $class = $this->config['default_class'];
        }

        return new $class($data);
    }
}
