<?php

namespace VGMdb\Component\User\Model\Doctrine;

use VGMdb\Component\User\Model\UserInterface;
use VGMdb\Component\User\Model\AbstractUserManager;
use VGMdb\Component\User\Util\CanonicalizerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Proxy\Proxy;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManager extends AbstractUserManager
{
    protected $objectManager;
    protected $class;
    protected $userRepository;
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
    public function __construct(EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $usernameCanonicalizer, CanonicalizerInterface $emailCanonicalizer, ObjectManager $om, $userClass, $authClass)
    {
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer);

        $this->objectManager = $om;
        $this->userRepository = $om->getRepository($userClass);
        $this->authRepository = $om->getRepository($authClass);

        $metadata = $om->getClassMetadata($userClass);
        $this->class = $metadata->getName();
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
        return $this->class;
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
        $provider = $this->authRepository->translateProvider($provider);
        $auth = $this->authRepository->findOneBy(array('provider' => $provider, 'provider_id' => $providerId));

        if ($auth) {
            return $auth->getUser();
        }

        return null;
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
        $this->objectManager->refresh($user);
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
