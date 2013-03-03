<?php

/*
 * This code was originally part of FOQElasticaBundle.
 *
 * (c) 2012 Exercise.com
 */

namespace VGMdb\Component\Elastica\Debug;

use VGMdb\Component\Elastica\Logger\ElasticaLogger;
use Elastica\Client;
use Elastica\Request;

/**
 * TraceableClient.
 *
 * @author Gordon Franke <info@nevalon.de>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TraceableClient extends Client
{
    protected $logger;

    public function setLogger(ElasticaLogger $logger)
    {
        $this->logger = $logger;
    }

    public function request($path, $method = Request::GET, $data = array(), array $query = array())
    {
        if (null !== $this->logger) {
            $this->logger->startQuery($path, $method, $data, $query);
        }

        $start = microtime(true);
        $response = parent::request($path, $method, $data, $query);

        $time = microtime(true) - $start;
        $response->setQueryTime($time);

        if (null !== $this->logger) {
            $this->logger->stopQuery($path, $method, $data, $query, $response);
        }

        return $response;
    }
}
