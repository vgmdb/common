<?php

namespace VGMdb\Component\User\Util;

use VGMdb\Component\User\Model\UserManagerInterface;
use VGMdb\Component\User\Model\UserInterface;

/**
 * Executes some manipulations on the users
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @author Luis Cordova <cordoval@gmail.com>
 */
class UserManipulator
{
    /**
     * User manager
     *
     * @var UserManagerInterface
     */
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Returns a user if either user object or username supplied.
     *
     * @param mixed $user
     *
     * @return UserInterface
     */
    protected function checkUser($user)
    {
        if (!($user instanceof UserInterface)) {
            $username = $user;
            $user = $this->userManager->findUserByUsername($username);
            if (!$user) {
                throw new \InvalidArgumentException(sprintf('User identified by "%s" username does not exist.', $username));
            }
        }

        return $user;
    }

    /**
     * Creates a user and returns it.
     *
     * @param string  $username
     * @param string  $email
     * @param string  $password
     * @param Boolean $active
     * @param Boolean $superadmin
     *
     * @return UserInterface
     */
    public function create($username, $email, $password = null, $active = true, $superadmin = false)
    {
        $user = $this->userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled((Boolean) $active);
        $user->setSuperAdmin((Boolean) $superadmin);
        $user->setCreatedAt(new \DateTime());
        $this->userManager->updateUser($user);

        return $user;
    }

    /**
     * Activates the given user.
     *
     * @param mixed $user
     */
    public function activate($user)
    {
        $user = $this->checkUser($user);
        $user->setEnabled(true);
        $this->userManager->updateUser($user);
    }

    /**
     * Deactivates the given user.
     *
     * @param mixed $user
     */
    public function deactivate($user)
    {
        $user = $this->checkUser($user);
        $user->setEnabled(false);
        $this->userManager->updateUser($user);
    }

    /**
     * Changes the password for the given user.
     *
     * @param mixed  $user
     * @param string $password
     */
    public function changePassword($user, $password)
    {
        $user = $this->checkUser($user);
        $user->setPlainPassword($password);
        $this->userManager->updateUser($user);
    }

    /**
     * Promotes the given user.
     *
     * @param mixed $user
     */
    public function promote($user)
    {
        $user = $this->checkUser($user);
        $user->setSuperAdmin(true);
        $this->userManager->updateUser($user);
    }

    /**
     * Demotes the given user.
     *
     * @param mixed $use
     */
    public function demote($user)
    {
        $user = $this->checkUser($user);
        $user->setSuperAdmin(false);
        $this->userManager->updateUser($user);
    }

    /**
     * Adds a role to the given user.
     *
     * @param mixed  $user
     * @param string $rolename
     *
     * @return Boolean true if role was added, false if user already had the role
     */
    public function addRole($user, $rolename)
    {
        $user = $this->checkUser($user);

        if ($user->hasRole($rolename)) {
            return false;
        }

        $user->addRole($rolename);
        $this->userManager->updateUser($user);

        return true;
    }
    /**
     * Removes a role from the given user.
     *
     * @param mixed  $user
     * @param string $rolename
     *
     * @return Boolean true if role was removed, false if user didn't have the role
     */
    public function removeRole($user, $rolename)
    {
        $user = $this->checkUser($user);

        if (false === $role = $user->hasRole($rolename)) {
            return false;
        }

        $user->removeRole($rolename);
        $this->userManager->removeRole($role);
        $this->userManager->updateUser($user);

        return true;
    }

    /**
     * Adds an auth provider to the given user.
     *
     * @param mixed  $user
     * @param string $provider
     * @param string $providerId
     *
     * @return Boolean true if provider was added
     */
    public function addAuthProvider($user, $provider, $providerId)
    {
        $user = $this->checkUser($user);

        if ($user->hasAuthProvider($provider, $providerId)) {
            return false;
        }

        $user->addAuthProvider($provider, $providerId);
        $this->userManager->updateUser($user);

        return true;
    }
    /**
     * Removes an auth provider from the given user.
     *
     * @param mixed  $user
     * @param string $provider
     * @param string $providerId
     *
     * @return Boolean true if provider was removed
     */
    public function removeAuthProvider($user, $provider, $providerId = null)
    {
        $user = $this->checkUser($user);

        if (false === $authProvider = $user->hasAuthProvider($provider, $providerId)) {
            return false;
        }

        $user->removeAuthProvider($provider, $providerId);
        $this->userManager->removeAuthProvider($authProvider);
        $this->userManager->updateUser($user);

        return true;
    }
}
