<?php

namespace VGMdb\Component\User\Model\Doctrine\ORM;

use VGMdb\Component\User\Model\UserInterface;
use VGMdb\Component\User\Model\AbstractUserManager;
use VGMdb\Component\User\Util\CanonicalizerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Proxy\Proxy;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManager extends AbstractUserManager
{
    protected $objectManager;
    protected $userClass;
    protected $roleClass;
    protected $authClass;
    protected $userRepository;
    protected $roleRepository;
    protected $authRepository;

    /**
     * Constructor.
     *
     * @param EncoderFactoryInterface $encoderFactory
     * @param CanonicalizerInterface  $usernameCanonicalizer
     * @param CanonicalizerInterface  $emailCanonicalizer
     * @param ObjectManager           $om
     * @param string                  $userClass
     * @param string                  $authClass
     */
    public function __construct(EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $usernameCanonicalizer, CanonicalizerInterface $emailCanonicalizer, ObjectManager $om, $userClass, $roleClass, $authClass)
    {
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer);

        $this->objectManager = $om;
        $this->userRepository = $om->getRepository($userClass);
        $this->authRepository = $om->getRepository($authClass);

        //$metadata = $om->getClassMetadata($userClass);
        $this->userClass = $userClass; //$metadata->getName();
        $this->roleClass = $roleClass;
        $this->authClass = $authClass;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(UserInterface $user)
    {
        $this->objectManager->remove($user);
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->userClass;
    }

    /**
     * Creates a role.
     *
     * @param string $rolename
     *
     * @return RoleInterface
     */
    public function createRole($rolename)
    {
        $class = $this->roleClass;
        $role = new $class;
        $role->setRole($rolename);
        $this->objectManager->persist($role);

        return $role;
    }

    /**
     * Removes a role.
     *
     * @param RoleInterface $role
     */
    public function removeRole(RoleInterface $role)
    {
        $this->objectManager->remove($role);
    }

    /**
     * Creates an auth provider.
     *
     * @param string $provider
     * @param string $providerId
     *
     * @return \VGMdb\ORM\Entity\AuthProvider
     */
    public function createAuthProvider($provider, $providerId)
    {
        $class = $this->authClass;
        $auth = new $class;
        $auth->setProvider($this->authRepository->translateProvider($provider));
        $auth->setProviderId($providerId);
        $auth->setEnabled(true);
        $this->objectManager->persist($auth);

        return $auth;
    }

    /**
     * Removes an auth provider.
     *
     * @param \VGMdb\ORM\Entity\AuthProvider $auth
     */
    public function removeAuthProvider($auth)
    {
        $this->objectManager->remove($auth);
    }

    /**
     * {@inheritDoc}
     */
    public function findUserBy(array $criteria)
    {
        return $this->userRepository->findOneBy($criteria);
    }

    /**
     * Updates a user.
     *
     * @param string $provider
     * @param string $providerId
     *
     * @return UserInterface
     */
    public function findUserByAuthProvider($provider, $providerId)
    {
        $provider = $this->translateProvider($provider);
        $auth = $this->authRepository->findOneBy(array('provider' => $provider, 'provider_id' => $providerId));

        if ($auth) {
            return $auth->getUser();
        }

        return null;
    }

    /**
     * Converts a provider name to integer.
     *
     * @param string $provider
     *
     * @return integer
     */
    public function translateProvider($provider)
    {
        return $this->authRepository->translateProvider($provider);
    }

    /**
     * Refresh an unserialized user. Used by UserProvider.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    public function findUserMatch(UserInterface $user)
    {
        if ($user instanceof Proxy) {
            // Doctrine Proxy class must be initialized, otherwise getId() returns null
            $user = $this->objectManager->merge($user);
        }

        return $this->userRepository->findOneBy(array('id' => $user->getId()));
    }

    /**
     * {@inheritDoc}
     */
    public function findUsers()
    {
        return $this->userRepository->findAll();
    }

    /**
     * {@inheritDoc}
     */
    public function reloadUser(UserInterface $user)
    {
        if ($user instanceof Proxy) {
            // Doctrine Proxy class must be initialized, otherwise getId() returns null
            $user = $this->objectManager->merge($user);
        }

        //$this->objectManager->refresh($user);
        return $this->userRepository->findOneBy(array('id' => $user->getId()));
    }

    /**
     * Updates a user.
     *
     * @param UserInterface $user
     * @param Boolean       $andFlush Whether to flush the changes (default true)
     */
    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        $this->objectManager->persist($user);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }
}
