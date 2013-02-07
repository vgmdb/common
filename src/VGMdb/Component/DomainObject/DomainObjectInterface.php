<?php

namespace VGMdb\Component\DomainObject;

/**
 * Interface definition for domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface DomainObjectInterface
{
    public function save($object, $async = false);

    public function delete($object, $async = false);
}
