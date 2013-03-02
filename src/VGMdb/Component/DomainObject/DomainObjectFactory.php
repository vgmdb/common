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
    protected $handlers;
    protected $dispatcher;
    protected $logger;

    public function __construct(array $config = array(), array $handlers = array(), EventDispatcherInterface $dispatcher, LoggerInterface $logger = null)
    {
        $this->config = array_replace(array(
            'classes' => array(),
            'default_class' => null
        ), $config);
        $this->handlers = $handlers;
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

        $object = new $class($data);
        $object->setDispatcher($this->dispatcher);
        $object->setLogger($this->logger);

        return $object;
    }

    public function createCollection($collection, $domain = null)
    {
        return $collection;
    }

    public function getHandler($provider)
    {
        if (isset($this->handlers[$provider])) {
            return $this->handlers[$provider];
        }

        return null;
    }
}
