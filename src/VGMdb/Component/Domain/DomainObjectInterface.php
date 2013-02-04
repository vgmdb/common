<?php

namespace VGMdb\Component\Domain;

/**
 * Interface definition for domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface DomainObjectInterface
{
    public function find($criteria);

    public function save($object, $async = false);

    public function delete($object, $async = false);

    public function count($criteria);
}
