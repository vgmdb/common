<?php

namespace VGMdb\Component\Queue;

use Psr\Log\LoggerInterface;

/**
 * Factory that creates job queues.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class QueueFactory
{
    protected $configs;
    protected $logger;
    protected $queues;

    public function __construct(array $configs = array(), LoggerInterface $logger = null)
    {
        $this->configs = $configs;
        $this->logger = $logger;
        $this->queues = array();
    }

    public function getQueue($name)
    {
        if (isset($this->queues[$name])) {
            return $this->queues[$name];
        }

        if (!isset($this->configs[$name])) {
            throw new \InvalidArgumentException(sprintf('The queue "%s" is not configured.', $name));
        }

        $config = $this->configs[$name];

        $this->queues[$name] = new Queue(
            $name,
            $config['provider'],
            $config['options'],
            $this->logger
        );

        return $this->queues[$name];
    }
}
