<?php

namespace VGMdb\Component\DomainObject;

/**
 * Interface definition for domain object array access handlers.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface ArrayAccessHandlerInterface
{
    public function offsetExists($object, $offset);

    public function offsetGet($object, $offset);

    public function offsetUnset($object, $offset);

    public function offsetSet($object, $offset, $value);
}
