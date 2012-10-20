<?php

namespace VGMdb\Provider;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @brief       User entity provider.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class DefaultUserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        if ($username !== 'gigablah') {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return new User(
            'gigablah',
            '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg==',
            array('ROLE_ADMIN', 'ROLE_USER'),
            true,
            true,
            true,
            true
        );
    }

    public function loadUserByUserId($uid)
    {
        if ($uid !== '1110663126') {
            throw new UsernameNotFoundException(sprintf('User ID "%s" does not exist.', $uid));
        }

        return new User(
            'gigablah',
            '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg==',
            array('ROLE_ADMIN', 'ROLE_USER'),
            true,
            true,
            true,
            true
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}