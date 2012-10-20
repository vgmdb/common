<?php

namespace VGMdb\ORM\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VGMdb\ORM\Entity\Role
 *
 * @ORM\Entity()
 * @ORM\Table(name="role", indexes={@ORM\Index(name="user_id_idx", columns={"user_id"})})
 */
class Role extends \VGMdb\Component\User\Model\AbstractRole
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $role;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="roles")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $user;

    public function __construct()
    {
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \VGMdb\ORM\Entity\Role
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of role.
     *
     * @param string $role
     * @return \VGMdb\ORM\Entity\Role
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the value of role.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set User entity (many to one).
     *
     * @param \VGMdb\ORM\Entity\User $user
     * @return \VGMdb\ORM\Entity\Role
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get User entity (many to one).
     *
     * @return \VGMdb\ORM\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function __sleep()
    {
        return array('id', 'user_id', 'role');
    }
}