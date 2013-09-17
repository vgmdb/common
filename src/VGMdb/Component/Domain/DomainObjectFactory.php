<?php

namespace VGMdb\Component\Domain;

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

    public function __construct(array $config = array(), $handlers = array(), EventDispatcherInterface $dispatcher, LoggerInterface $logger = null)
    {
        $this->config = array_replace(array(
            'classes' => array(),
            'collection_classes' => array(),
            'default_class' => 'VGMdb\\Component\\Domain\\DomainObject',
            'default_collection_class' => 'VGMdb\\Component\\Domain\\DomainObjectCollection',
            'base_dirs' => array()
        ), $config);
        $this->handlers = $handlers;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    public function create($domain = null, array $data = array(), $provider = null, $entity = null)
    {
        $class = $this->resolveClass($domain);
        $object = new $class($data);

        $object->setEntity($entity, $this->getHandler($provider));
        $object->setDispatcher($this->dispatcher);
        $object->setLogger($this->logger);

        return $object;
    }

    public function createCollection($domain = null, array $data = array())
    {
        $class = $this->resolveCollectionClass($domain);
        $collection = new $class($data);

        return $collection;
    }

    public function getHandler($provider)
    {
        if (isset($this->handlers[$provider])) {
            return $this->handlers[$provider];
        }

        return null;
    }

    protected function resolveClass($domain = null)
    {
        if (!$domain) {
            return $this->config['default_class'];
        }

        if ($classes = $this->config['classes'] && isset($classes[$domain])) {
            return $classes[$domain];
        }

        $segments = array_map('ucfirst', explode('_', $domain));
        foreach ($this->config['namespaces'] as $namespace) {
            if (class_exists($class = $namespace . '\\' . $segments[0] . '\\' . implode('', $segments))) {
                return $class;
            }
        }

        return $this->config['default_class'];
    }

    protected function resolveCollectionClass($domain = null)
    {
        if (!$domain) {
            return $this->config['default_collection_class'];
        }

        if ($classes = $this->config['collection_classes'] && isset($classes[$domain])) {
            return $classes[$domain];
        }

        $segments = array_map('ucfirst', explode('_', $domain));
        foreach ($this->config['namespaces'] as $namespace) {
            if (class_exists($class = $namespace . '\\' . $segments[0] . '\\' . implode('', $segments) . 'Collection')) {
                return $class;
            }
        }

        return $this->config['default_collection_class'];
    }
}
