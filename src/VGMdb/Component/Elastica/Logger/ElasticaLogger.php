<?php

/*
 * This code was originally part of FOQElasticaBundle.
 *
 * (c) 2012 Exercise.com
 */

namespace VGMdb\Component\Elastica\Logger;

use Symfony\Component\Stopwatch\Stopwatch;
use Psr\Log\LoggerInterface;
use Psr\Log\AbstractLogger;

/**
 * Logger for Elastica.
 *
 * @author Gordon Franke <info@nevalon.de>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ElasticaLogger extends AbstractLogger
{
    protected $logger;
    protected $stopwatch;
    protected $queries;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger    The logger
     * @param Stopwatch       $stopwatch A Stopwatch instance
     */
    public function __construct(LoggerInterface $logger = null, Stopwatch $stopwatch = null)
    {
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;
        $this->queries = array();
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }

    /**
     * Starts logging a query.
     *
     * @param string $path   Path to call
     * @param string $method Rest method to use (GET, POST, DELETE, PUT)
     * @param array  $data   Arguments
     * @param array  $query  Query parameters
     */
    public function startQuery($path, $method, $data, $query)
    {
        if (null !== $this->stopwatch) {
            $this->stopwatch->start('elastica.request', 'elastica');
        }
    }

    /**
     * Logs a query.
     *
     * @param string   $path     Path to call
     * @param string   $method   Rest method to use (GET, POST, DELETE, PUT)
     * @param array    $data     Arguments
     * @param array    $query    Query parameters
     * @param Response $response Returned response
     */
    public function stopQuery($path, $method, $data, $query, $response)
    {
        if (null !== $this->stopwatch) {
            $this->stopwatch->stop('elastica.request', 'elastica');
        }

        $time = $response->getQueryTime();

        $this->queries[] = array(
            'method' => $method,
            'path' => $path,
            'data' => $data,
            'query' => $query,
            'response' => $response->getData(),
            'time' => $time
        );

        if (null !== $this->logger) {
            $message = sprintf("%s %s %0.2f ms", $method, $path, $time * 1000);
            $this->logger->info($message, (array) $data);
        }
    }

    /**
     * Returns the number of queries that have been logged.
     *
     * @return integer The number of queries logged
     */
    public function getQueryCount()
    {
        return count($this->queries);
    }

    /**
     * Returns a human-readable array of queries logged.
     *
     * @return array An array of queries
     */
    public function getQueries()
    {
        return $this->queries;
    }
}
