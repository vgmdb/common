<?php

namespace VGMdb\Component\Queue;

use PHPQueue\Worker as BaseWorker;
use PHPQueue\Job;

/**
 * Abstract queue worker.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractWorker extends BaseWorker implements WorkerInterface
{
    protected $queue;

    public function __construct(Queue $queue = null)
    {
        $this->queue = $queue;
    }

    public function getJob()
    {
        $job = $this->queue->popJob();

        if ($job instanceof Job) {
            return $job;
        }

        return null;
    }
}
