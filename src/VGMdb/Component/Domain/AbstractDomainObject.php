<?php

namespace VGMdb\Component\Domain;

use VGMdb\Component\Domain\DomainObjectEvents;
use VGMdb\Component\Domain\Event\DomainObjectEvent;
use VGMdb\Component\Domain\ArrayAccessHandlerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

/**
 * Base class for domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractDomainObject extends \ArrayObject implements DomainObjectInterface
{
    protected $entity;
    protected $handler;
    protected $logger;
    protected $dispatcher;

    public function __construct(array $data = array())
    {
        parent::__construct($data);
    }

    public function setEntity($entity = null, ArrayAccessHandlerInterface $handler = null)
    {
        $this->entity = $entity;
        $this->handler = $handler;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function setHandler(ArrayAccessHandlerInterface $handler = null)
    {
        $this->handler = $handler;
    }

    public function getHandler()
    {
        return $this->handler;
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

    public function offsetGet($offset)
    {
        if (parent::offsetExists($offset)) {
            return parent::offsetGet($offset);
        }

        if (null !== $this->entity && null !== $this->handler) {
            return $this->handler->offsetGet($this, $offset);
        }

        return null;
    }

    public function offsetUnset($offset)
    {
        if (null !== $this->entity && null !== $this->handler) {
            $this->handler->offsetUnset($this, $offset);
        }

        parent::offsetUnset($offset);
    }

    public function offsetSet($offset, $value)
    {
        if (null !== $this->entity && null !== $this->handler) {
            $this->handler->offsetSet($this, $offset, $value);
        }

        parent::offsetSet($offset, $value);
    }

    public function __call($method, $arguments)
    {
        return $this->handler->proxy($this, $method, $arguments);
    }

    public function toArray()
    {
        return parent::getArrayCopy();
    }
}
