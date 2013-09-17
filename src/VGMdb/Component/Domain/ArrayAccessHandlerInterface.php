<?php

namespace VGMdb\Component\Domain;

/**
 * Interface definition for domain object array access handlers.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface ArrayAccessHandlerInterface
{
    public function save(DomainObjectInterface $object);

    public function delete(DomainObjectInterface $object);

    public function offsetExists(DomainObjectInterface $object, $offset);

    public function offsetGet(DomainObjectInterface $object, $offset);

    public function offsetUnset(DomainObjectInterface $object, $offset);

    public function offsetSet(DomainObjectInterface $object, $offset, $value);
}
