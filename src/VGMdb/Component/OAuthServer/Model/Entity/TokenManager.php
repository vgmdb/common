<?php

namespace VGMdb\Component\OAuthServer\Model\Entity;

use Doctrine\ORM\EntityManager;
use VGMdb\Component\OAuthServer\Model\TokenInterface;
use VGMdb\Component\OAuthServer\Model\TokenManager as BaseTokenManager;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
class TokenManager extends BaseTokenManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($class);
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function findTokenBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function findTokensBy(array $criteria)
    {
        return $this->repository->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function updateToken(TokenInterface $token)
    {
        $this->em->persist($token);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteToken(TokenInterface $token)
    {
        $this->em->remove($token);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired()
    {
        $qb = $this->repository->createQueryBuilder('t');
        $qb->delete()
           ->where('t.expires_at < ?1')
           ->setParameters(array(1 => time()));

        return $qb->getQuery()->execute();
    }
}
