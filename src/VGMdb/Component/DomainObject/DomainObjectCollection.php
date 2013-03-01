<?php

namespace VGMdb\Component\DomainObject;

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
