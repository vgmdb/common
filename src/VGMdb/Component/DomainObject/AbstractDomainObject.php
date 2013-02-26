<?php

namespace VGMdb\Component\DomainObject;

use VGMdb\Component\DomainObject\DomainObjectEvents;
use VGMdb\Component\DomainObject\Event\DomainObjectEvent;
use VGMdb\Component\DomainObject\ArrayAccessHandlerInterface;
use VGMdb\Component\DomainObject\Handler\ArrayHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

/**
 * Base class for domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractDomainObject implements DomainObjectInterface, \ArrayAccess
{
    protected $object;
    protected $logger;
    protected $dispatcher;
    protected $handler;

    public function __construct($object, ArrayAccessHandlerInterface $handler = null)
    {
        if (is_object($object) && !static::accepts($object)) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot accept object of type "%s".',
                get_class($object)
            ));
        }

        $this->object = $object;
        $this->handler = $handler ?: new ArrayHandler();
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
    }

    public function save()
    {
        if (null !== $this->logger) {
            $this->logger->debug('Saving object.');
        }

        $event = new DomainObjectEvent($this);
        $this->dispatcher->dispatch(DomainObjectEvents::SAVE, $event);
    }

    public function delete()
    {
        if (null !== $this->logger) {
            $this->logger->debug('Deleting object.');
        }

        $event = new DomainObjectEvent($this);
        $this->dispatcher->dispatch(DomainObjectEvents::DELETE, $event);
    }

    abstract public static function accepts($object);

    public function offsetExists($offset)
    {
        return $this->handler->offsetExists($this->getObject(), $offset);
    }

    public function offsetGet($offset)
    {
        return $this->handler->offsetGet($this->getObject(), $offset);
    }

    public function offsetUnset($offset)
    {
        $this->handler->offsetUnset($this->getObject(), $offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->handler->offsetSet($this->getObject(), $offset, $value);
    }
}
