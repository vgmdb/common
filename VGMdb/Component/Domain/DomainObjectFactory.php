<?php

namespace Guru\Component\Domain;

use Aura\Marshal\Manager as Marshal;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * Factory for domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class DomainObjectFactory
{
    protected $dataProviders;
    protected $serializer;
    protected $marshal;
    protected $logger;
    protected $dispatcher;

    public function __construct(Marshal $marshal, array $configuration = array())
    {
        $this->marshal = $marshal;
    }

    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
    }
}
