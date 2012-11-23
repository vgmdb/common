<?php

namespace VGMdb\Component\User\Provider;

use VGMdb\Component\User\Model\AbstractUser;
use VGMdb\Component\User\Model\UserInterface;
use VGMdb\Component\User\Model\UserManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;

/**
 * User entity provider, to be used with the Symfony Security Component.
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Gigablah <gigablah@vgmdb.net>
 * @copyright (c) 2010-2012 FriendsOfSymfony
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager
     */
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findUser($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(SecurityUserInterface $user)
    {
        if (!$user instanceof AbstractUser) {
            throw new UnsupportedUserException(sprintf('Expected an instance of AbstractUser, but got "%s".', get_class($user)));
        }

        //if (null === $reloadedUser = $this->userManager->findUserById($user->getId())) {
        if (null === $reloadedUser = $this->userManager->reloadUser($user)) {
            throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $user->getId()));
        }

        return $reloadedUser;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        $userClass = $this->userManager->getClass();

        return $userClass === $class || is_subclass_of($class, $userClass);
    }

    /**
     * Finds a user by username.
     *
     * This method is meant to be an extension point for child classes.
     *
     * @param string $username
     *
     * @return UserInterface|null
     */
    protected function findUser($username)
    {
        return $this->userManager->findUserByUsername($username);
    }

    /**
     * Finds a user by external provider.
     *
     * @param string  $provider
     * @param integer $providerId
     *
     * @return UserInterface|null
     */
    public function loadUserByAuthProvider($provider, $providerId)
    {
        return $this->userManager->findUserByAuthProvider($provider, $providerId);
    }
}
