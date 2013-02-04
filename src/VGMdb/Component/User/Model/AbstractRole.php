<?php

namespace VGMdb\Component\User\Model;

use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Abstract role object. Simply provides a __toString method.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractRole implements RoleInterface
{
    public function __toString()
    {
        return (string) $this->getRole();
    }
}
