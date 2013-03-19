<?php

namespace VGMdb\Component\Propel\Logger;

use Symfony\Component\Stopwatch\Stopwatch;
use Psr\Log\LoggerInterface;

/**
 * PropelLogger, now with PSR-3 interface.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class PropelLogger implements LoggerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $queries;

    /**
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * @var Boolean
     */
    private $isPrepared;

    /**
     * @var Boolean
     */
    private $incrementQuery;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger    A LoggerInterface instance
     * @param Stopwatch       $stopwatch A Stopwatch instance
     */
    public function __construct(LoggerInterface $logger = null, Stopwatch $stopwatch = null)
    {
        $this->logger = $logger;
        $this->queries = array();
        $this->stopwatch = $stopwatch;
        $this->isPrepared = false;
        $this->incrementQuery = false;
    }

    /**
     * {@inheritDoc}
     */
    public function emergency($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->emergency($message, $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function alert($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->alert($message, $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function critical($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->critical($message, $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function error($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->error($message, $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function warning($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->warning($message, $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function notice($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->notice($message, $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function info($message, array $context = array())
    {
        $add = true;

        $trace = debug_backtrace();
        $method = $trace[2]['args'][2];
        if ('ProfilerConnectionWrapper::prepare' === $method) {
            $add = false;
        }

        if (null !== $this->stopwatch) {
            if ($this->incrementQuery) {
                $watch = 'Propel Query '.(count($this->queries)+1);
            } else {
                $watch = 'propel.query';
            }
            if ('ProfilerConnectionWrapper::prepare' === $method) {
                $this->isPrepared = true;
                $this->stopwatch->start($watch, 'propel');
            } elseif ($this->isPrepared) {
                $this->isPrepared = false;
                $this->stopwatch->stop($watch);
            }
        }

        if ($add) {
            $this->queries[] = $message;
            if (null !== $this->logger) {
                $this->logger->info($message, $context);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function debug($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->debug($message, $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Returns queries.
     *
     * @return array Queries
     */
    public function getQueries()
    {
        return $this->queries;
    }
}
