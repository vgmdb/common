<?php

/*
 * This code was originally part of FOQElasticaBundle.
 *
 * (c) 2012 Exercise.com
 */

namespace VGMdb\Component\Elastica\DataCollector;

use VGMdb\Component\Elastica\Logger\ElasticaLogger;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Data collector collecting elastica statistics.
 *
 * @author Gordon Franke <info@nevalon.de>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ElasticaDataCollector extends DataCollector
{
    protected $logger;

    public function __construct(ElasticaLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data['querycount'] = $this->logger->getQueryCount();
        $this->data['queries'] = $this->logger->getQueries();
    }

    public function getQueryCount()
    {
        return $this->data['querycount'];
    }

    public function getQueries()
    {
        return $this->data['queries'];
    }

    public function getTime()
    {
        $time = 0;
        foreach ($this->data['queries'] as $query) {
            $time += (float) $query['time'];
        }

        return $time;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'elastica';
    }
}
