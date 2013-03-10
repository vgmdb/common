<?php

namespace VGMdb\Component\Domain;

/**
 * Interface definition for domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface DomainObjectInterface
{
    public function setEntity($entity, ArrayAccessHandlerInterface $handler = null);

    public function getEntity();

    public function getHandler();

    public function save();

    public function delete();

    public function toArray();
}
