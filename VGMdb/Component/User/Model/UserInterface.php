<?php

namespace VGMdb\Component\User\Model;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * User interface definition.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Gigablah <gigablah@vgmdb.net>
 * @copyright (c) 2010-2012 FriendsOfSymfony
 */
interface UserInterface extends AdvancedUserInterface, \Serializable
{
    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * Sets the username.
     *
     * @param string $username
     */
    public function setUsername($username);

    /**
     * Gets the canonical username in search and sort queries.
     *
     * @return string
     */
    public function getUsernameCanonical();

    /**
     * Sets the canonical username.
     *
     * @param string $usernameCanonical
     */
    public function setUsernameCanonical($usernameCanonical);

    /**
     * Gets email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Sets the email.
     *
     * @param string $email
     */
    public function setEmail($email);

    /**
     * Gets the canonical email in search and sort queries.
     *
     * @return string
     */
    public function getEmailCanonical();

    /**
     * Set the canonical email.
     *
     * @param string $emailCanonical
     */
    public function setEmailCanonical($emailCanonical);

    /**
     * Gets the plain password.
     *
     * @return string
     */
    public function getPlainPassword();

    /**
     * Sets the plain password.
     *
     * @param string $password
     */
    public function setPlainPassword($password);

    /**
     * Sets the hashed password.
     *
     * @param string $password
     */
    public function setPassword($password);

    /**
     * Tells if the the given user has the super admin role.
     *
     * @return Boolean
     */
    public function isSuperAdmin();

    /**
     * Tells if the the given user is this user.
     *
     * Useful when not hydrating all fields.
     *
     * @param UserInterface $user
     *
     * @return Boolean
     */
    public function isUser(UserInterface $user = null);

    /**
     * @param Boolean $boolean
     */
    public function setEnabled($boolean);

    /**
     * Sets the locking status of the user.
     *
     * @param Boolean $boolean
     */
    public function setLocked($boolean);

    /**
     * Sets the super admin status
     *
     * @param Boolean $boolean
     */
    public function setSuperAdmin($boolean);

    /**
     * Gets the confirmation token.
     *
     * @return string
     */
    public function getConfirmationToken();

    /**
     * Sets the confirmation token
     *
     * @param string $confirmationToken
     */
    public function setConfirmationToken($confirmationToken);

    /**
     * Sets the timestamp that the user requested a password reset.
     *
     * @param \DateTime $date
     */
    public function setPasswordRequestedAt(\DateTime $date);

    /**
     * Checks whether the password reset request has expired.
     *
     * @param integer $ttl Requests older than this many seconds will be considered expired
     *
     * @return Boolean true if the user's password request is non expired, false otherwise
     */
    public function isPasswordRequestNonExpired($ttl);

    /**
     * Sets the last login time
     *
     * @param \DateTime $time
     */
    public function setLastLogin(\DateTime $time);

    /**
     * Never use this to check if this user has access to anything!
     *
     * Use the SecurityContext, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $securityContext->isGranted('ROLE_USER');
     *
     * @param string $role
     *
     * @return Boolean
     */
    public function hasRole($role);

    /**
     * Sets the roles of the user.
     *
     * This overwrites any previous roles.
     *
     * @param array $roles
     */
    public function setRoles(array $roles);

    /**
     * Adds a role to the user.
     *
     * @param string $role
     */
    public function addRole($role);

    /**
     * Removes a role to the user.
     *
     * @param string $role
     */
    public function removeRole($role);
}
