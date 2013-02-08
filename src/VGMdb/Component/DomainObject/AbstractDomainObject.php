<?php

namespace VGMdb\Component\DomainObject;

use VGMdb\Component\DomainObject\DomainObjectEvents;
use VGMdb\Component\DomainObject\Event\DomainObjectEvent;
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

    public function __construct($object)
    {
        if (!static::accepts($object)) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot accept object of type "%s".',
                is_object($object) ? get_class($object) : gettype($object)
            ));
        }

        $this->object = $object;
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
            $this->logger->info('Saving object.');
        }

        $event = new DomainObjectEvent($this);
        $this->dispatcher->dispatch(DomainObjectEvents::PRESAVE, $event);
        $this->doSave();
        $this->dispatcher->dispatch(DomainObjectEvents::POSTSAVE, $event);
    }

    public function delete()
    {
        if (null !== $this->logger) {
            $this->logger->info('Deleting object.');
        }

        $event = new DomainObjectEvent($this);
        $this->dispatcher->dispatch(DomainObjectEvents::PREDELETE, $event);
        $this->doDelete();
        $this->dispatcher->dispatch(DomainObjectEvents::POSTDELETE, $event);
    }

    abstract protected function doSave();

    abstract protected function doDelete();

    abstract public static function accepts($object);
}
