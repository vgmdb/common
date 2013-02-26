<?php

namespace VGMdb\Component\DomainObject;

/**
 * Interface definition for domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface DomainObjectInterface
{
    public function getObject();

    public function save();

    public function delete();

    public static function accepts($object);
}
