<?php

namespace VGMdb\Component\User\Model\Doctrine;

use VGMdb\Component\User\Model\UserInterface;
use VGMdb\Component\User\Model\AbstractUserManager;
use VGMdb\Component\User\Util\CanonicalizerInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Test implementation of UserManager without Doctrine ORM. DO NOT USE!
 */
class UserManager extends AbstractUserManager
{
    protected $conn;
    protected $userClass;
    protected $roleClass;
    protected $authClass;

    /**
     * Constructor.
     *
     * @param EncoderFactoryInterface $encoderFactory
     * @param CanonicalizerInterface  $usernameCanonicalizer
     * @param CanonicalizerInterface  $emailCanonicalizer
     * @param QueryBuilder            $queryBuilder
     * @param string                  $userClass
     * @param string                  $roleClass
     * @param string                  $authClass
     */
    public function __construct(EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $usernameCanonicalizer, CanonicalizerInterface $emailCanonicalizer, Connection $conn, $userClass, $roleClass, $authClass)
    {
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer);

        $this->conn = $conn;
        $this->userClass = $userClass;
        $this->roleClass = $roleClass;
        $this->authClass = $authClass;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(UserInterface $user)
    {
        throw new \Exception('Not implemented yet.');
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->userClass;
    }

    /**
     * {@inheritDoc}
     */
    public function findUserBy(array $criteria)
    {
        $query = $this->conn->createQueryBuilder();
        $query->select('*')->from('user', 'u');
        $counter = 0;
        foreach ($criteria as $column => $value) {
            $counter++;
            if ($counter === 1) {
                $query->where($query->expr()->eq('u.' . $column, ':' . $column . $counter));
            } else {
                $query->andWhere($query->expr()->eq('u.' . $column, ':' . $column . $counter));
            }
            $query->setParameter(':' . $column . $counter, $value);
        }
        $stmt = $query->execute();
        $data = $stmt->fetch(\PDO::FETCH_NUM);

        if ($data) {
            $class = $this->getClass();
            $user = new $class;
            $user->hydrate($data);
            return $user;
        }

        return null;
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
        throw new \Exception('Not implemented yet.');
    }

    /**
     * {@inheritDoc}
     */
    public function findUsers()
    {
        throw new \Exception('Not implemented yet.');
    }

    /**
     * {@inheritDoc}
     */
    public function reloadUser(UserInterface $user)
    {
        return $this->findUserBy(array('id' => $user->getId()));
    }

    /**
     * Updates a user.
     *
     * @param UserInterface $user
     */
    public function updateUser(UserInterface $user)
    {
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        $query = $this->conn->createQueryBuilder();
        $query->update('user', 'u')
              ->set('u.last_login', ':last_login')
              ->where($query->expr()->eq('u.id', ':id1'))
              ->setParameter(':last_login', $user->getLastLogin()->format('Y-m-d H:i:s'))
              ->setParameter(':id1', $user->getId());

        $query->execute();
    }

    /**
     * Adds a role to the user
     *
     * @param string        $role
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    public function addRole($role, UserInterface $user)
    {
        $class = $this->roleClass;
        $role = new $class;
        $role->setRole($role);
        $role->setUserId($user->getId());

        throw new \Exception('Not implemented yet.');

        $user->addRole($role);
        return $user;
    }

    /**
     * Adds an auth provider to the user
     *
     * @param string        $provider
     * @param integer       $provider_id
     * @param UserInterface $user
     * @return UserInterface
     */
    public function addAuthProvider($provider, $provider_id, UserInterface $user)
    {
        $class = $this->authClass;
        $auth = new $class;

        $auth->setProvider($provider);
        $auth->setProviderId($provider_id);
        $auth->setUserId($user->getId());

        throw new \Exception('Not implemented yet.');

        $user->addAuthProvider($auth);
        return $user;
    }
}
