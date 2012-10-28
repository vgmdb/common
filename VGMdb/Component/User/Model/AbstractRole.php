<?php

namespace VGMdb\Component\User\Model;

use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Storage agnostic role object
 */
abstract class AbstractRole implements RoleInterface, \Serializable
{
    protected $id;

    protected $user_id;

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
     * Returns the role user id.
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userid;
    }

    /**
     * Returns the role.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Sets the user id.
     *
     * @param mixed $user_id
     *
     * @return Role
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Sets the role.
     *
     * @param string $role
     *
     * @return Role
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Serializes the role.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->user_id,
            $this->role,
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
            $this->id,
            $this->user_id,
            $this->role,
        ) = $data;
    }

    public function __toString()
    {
        return (string) $this->getRole();
    }
}
