<?php

namespace VGMdb\Component\Domain;

/**
 * Collection of domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DomainObjectCollection extends \ArrayObject
{
    public function toArray()
    {
        return parent::getArrayCopy();
    }
}
