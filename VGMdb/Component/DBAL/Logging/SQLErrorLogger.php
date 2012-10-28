<?php

namespace VGMdb\Component\DBAL\Logging;

use Doctrine\DBAL\Logging\SQLLogger;

class SQLErrorLogger implements SQLLogger
{
    public $queries = array();
    public $enabled = true;
    public $start = null;
    public $currentQuery = 0;
    public $logfile = '/tmp/query.log';

    public function __construct($logfile)
    {
        $this->logfile = $logfile;
        error_log('----------' . date('Y-m-d H:i:s') . '----------' . PHP_EOL, 3, $this->logfile);
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
            error_log($time . ' ' . $sql . ' ' . json_encode($params) . ' ' . json_encode($types) . PHP_EOL, 3, $this->logfile);
        }
    }
}