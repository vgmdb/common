<?php

namespace VGMdb\Component\DomainObject\Event;

use VGMdb\Component\DomainObject\DomainObjectInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows pre and post CRUD operations on domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DomainObjectEvent extends Event
{
    private $object;

    public function __construct(DomainObjectInterface $object)
    {
        $this->object = $object;
    }

    /**
     * Returns the current domain object
     *
     * @return DomainObjectInterface
     */
    public function getDomainObject()
    {
        return $this->object;
    }
}
