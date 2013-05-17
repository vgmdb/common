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
class QueueService extends JobQueue
{
    protected $queueWorker;
    protected $queueProvider;
    protected $logger;

    public function __construct($worker, $provider, array $config = array(), LoggerInterface $logger = null)
    {
        if (!$provider instanceof ProviderBase) {
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

    public function getJob()
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
        $this->queueProvider->clear($jobId);
    }

    public function releaseJob($jobId = null)
    {
        $this->queueProvider->release($jobId);
    }
}
