<?php

namespace VGMdb\Component\DomainObject\EventListener;

use VGMdb\Component\DomainObject\DomainObjectFactory;

/**
 * Listener for domain object retrievals.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DomainObjectListener
{
    protected $factory

    public function __construct(DomainObjectFactory $factory)
    {
        $this->factory = $factory;
    }
}
