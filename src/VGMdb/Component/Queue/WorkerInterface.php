<?php

namespace VGMdb\Component\Queue;

/**
 * Queue worker interface.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface WorkerInterface
{
    /**
     * Fetch a job from the queue.
     *
     * @return mixed
     */
    public function getJob();

    /**
     * Run a job.
     *
     * @param mixed $job Job to run
     */
    public function runJob($job);
}
