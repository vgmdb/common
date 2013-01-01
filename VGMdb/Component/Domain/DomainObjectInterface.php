<?php

namespace VGMdb\Component\Domain;

/**
 * Interface definition for domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface DomainObjectInterface
{
    public function save($object);

    public function delete($object);

    public function find($criteria);

    public function findAggregate($criteria);

    public function findOne($criteria);

    public function findOneAggregate($criteria);

    public function count($criteria);

    public function getInterfaceDefinition();
}
