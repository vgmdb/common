<?php

namespace VGMdb\Component\DBAL\Logging;

use Doctrine\DBAL\Logging\SQLLogger;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class SQLDebugLogger implements SQLLogger
{
    public $queries = array();
    public $enabled = true;
    public $start = null;
    public $currentQuery = 0;
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        if ($this->enabled) {
            $this->start = microtime(true);
            $this->queries[++$this->currentQuery] = array('sql' => $sql, 'params' => $params, 'types' => $types, 'executionMS' => 0);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        if ($this->enabled) {
            $time = microtime(true) - $this->start;
            $this->queries[$this->currentQuery]['executionMS'] = $time;
            list($sql, $params, $types, $time) = array_values($this->queries[$this->currentQuery]);
            $time = number_format($time * 1000, 2);
            $this->logger->info('[' . $time . 'ms] ' . $sql . ' ' . json_encode($params) . ' ' . json_encode($types));
        }
    }
}