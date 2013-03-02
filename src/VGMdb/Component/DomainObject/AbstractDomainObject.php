<?php

namespace VGMdb\Component\DomainObject;

use VGMdb\Component\DomainObject\DomainObjectEvents;
use VGMdb\Component\DomainObject\Event\DomainObjectEvent;
use VGMdb\Component\DomainObject\ArrayAccessHandlerInterface;
use VGMdb\Component\DomainObject\Handler\ArrayHandler;
use VGMdb\Component\HttpFoundation\Util\XmlSerializable;
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

    public function setEntity($entity, ArrayAccessHandlerInterface $handler = null)
    {
        $this->entity = $entity;
        $this->handler = $handler;
    }

    public function getEntity()
    {
        return $this->entity;
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

    public function offsetUnset($offset)
    {
        parent::offsetUnset($offset);

        if (null !== $this->entity && null !== $this->handler) {
            $this->handler->offsetUnset($this->entity, $offset);
        }
    }

    public function offsetSet($offset, $value)
    {
        parent::offsetSet($offset, $value);

        if (null !== $this->entity && null !== $this->handler) {
            $this->handler->offsetSet($this->entity, $offset, $value);
        }
    }

    public function toArray()
    {
        return parent::getArrayCopy();
    }
}
