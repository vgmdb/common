<?php

namespace VGMdb\Component\Propel\Logger;

use Symfony\Component\Stopwatch\Stopwatch;
use Psr\Log\LoggerInterface;

/**
 * PropelLogger.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author William Durand <william.durand1@gmail.com>
 */
class PropelLogger
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

    private $isPrepared;
    private $incrementQuery;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger    A LoggerInterface instance
     * @param Stopwatch       $stopwatch A Stopwatch instance
     */
    public function __construct(LoggerInterface $logger = null, Stopwatch $stopwatch = null)
    {
        $this->logger    = $logger;
        $this->queries   = array();
        $this->stopwatch = $stopwatch;
        $this->isPrepared = false;
        $this->incrementQuery = false;
    }

    /**
     * A convenience function for logging an alert event.
     *
     * @param mixed $message the message to log.
     */
    public function alert($message)
    {
        if (null !== $this->logger) {
            $this->logger->alert($message);
        }
    }

    /**
     * A convenience function for logging a critical event.
     *
     * @param mixed $message the message to log.
     */
    public function crit($message)
    {
        if (null !== $this->logger) {
            $this->logger->crit($message);
        }
    }

    /**
     * A convenience function for logging an error event.
     *
     * @param mixed $message the message to log.
     */
    public function err($message)
    {
        if (null !== $this->logger) {
            $this->logger->err($message);
        }
    }

    /**
     * A convenience function for logging a warning event.
     *
     * @param mixed $message the message to log.
     */
    public function warning($message)
    {
        if (null !== $this->logger) {
            $this->logger->warn($message);
        }
    }

    /**
     * A convenience function for logging an critical event.
     *
     * @param mixed $message the message to log.
     */
    public function notice($message)
    {
        if (null !== $this->logger) {
            $this->logger->notice($message);
        }
    }

    /**
     * A convenience function for logging an critical event.
     *
     * @param mixed $message the message to log.
     */
    public function info($message)
    {
        if (null !== $this->logger) {
            $this->logger->info($message);
        }
    }

    /**
     * A convenience function for logging a debug event.
     *
     * @param mixed $message the message to log.
     */
    public function debug($message)
    {
        $add = true;

        $trace = debug_backtrace();
        $method = $trace[2]['args'][2];
        if ('PropelPDO::prepare' === $method) {
            $add = false;
        }

        if (null !== $this->stopwatch) {
            if ($this->incrementQuery) {
                $watch = 'Propel Query '.(count($this->queries)+1);
            } else {
                $watch = 'propel.query';
            }
            if ('PropelPDO::prepare' === $method) {
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
                $this->logger->debug($message);
            }
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
