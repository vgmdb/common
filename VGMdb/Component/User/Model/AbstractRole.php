<?php

namespace VGMdb\Component\User\Model;

use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Storage agnostic role object
 */
abstract class AbstractRole implements RoleInterface, \Serializable
{
    protected $id;

    /**
     * @var string
     */
    protected $role;

    /**
     * Returns the role unique id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Serializes the role.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->role,
            $this->id
        ));
    }

    /**
     * Unserializes the role.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        list(
            $this->role,
            $this->id
        ) = $data;
    }

    public function __toString()
    {
        return (string) $this->getRole();
    }
}
