<?php

namespace VGMdb\Component\OAuthServer\Model\Entity;

use Doctrine\ORM\EntityManager;
use VGMdb\Component\OAuthServer\Model\ClientInterface;
use VGMdb\Component\OAuthServer\Model\ClientManager as BaseClientManager;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
class ClientManager extends BaseClientManager
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
    public function findClientBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function findClientsBy(array $criteria)
    {
        return $this->repository->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function updateClient(ClientInterface $client)
    {
        $this->em->persist($client);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteClient(ClientInterface $client)
    {
        $this->em->remove($client);
        $this->em->flush();
    }
}
