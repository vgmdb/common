<?php

namespace VGMdb\Component\Queue;

use PHPQueue\Base;
use PHPQueue\Job;
use PHPQueue\JobQueue;
use PHPQueue\Backend\Base as ProviderBase;
use Psr\Log\LoggerInterface;

/**
 * Generic asynchronous job queue.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Queue extends JobQueue
{
    protected $queueWorker;
    protected $queueProvider;
    protected $logger;

    public function __construct($worker, $provider, array $config = array(), LoggerInterface $logger = null)
    {
        if (is_string($provider)) {
            $provider = Base::backendFactory($provider, $config);
        }

        $this->queueWorker = $worker;
        $this->queueProvider = $provider;
        $this->logger = $logger;
    }

    public function addJob($newJob = null)
    {
        $data = array(
            'worker' => $this->queueWorker,
            'data' => $newJob
        );

        return $this->queueProvider->add($data);
    }

    public function getJob($jobId = null)
    {
        $data = $this->queueProvider->get();
        if (!is_array($data)) {
            return null;
        }

        $job = new Job($data, $this->queueProvider->last_job_id);

        return $job;
    }

    public function updateJob($jobId = null, $resultData = null)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Job %s finished with result: %s', $jobId, json_encode($resultData)));
        }
    }

    public function clearJob($jobId = null)
    {
        return $this->queueProvider->clear($jobId);
    }

    public function releaseJob($jobId = null)
    {
        return $this->queueProvider->release($jobId);
    }

    public function popJob()
    {
        $job = $this->getJob();
        if ($job instanceof Job) {
            $this->clearJob($job->job_id);
        }

        return $job;
    }
}
