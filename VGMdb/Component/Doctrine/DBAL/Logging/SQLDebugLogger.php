<?php

namespace VGMdb\Component\Doctrine\DBAL\Logging;

use Doctrine\DBAL\Logging\DebugStack;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * A SQL logger that dumps to a log manager.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class SQLDebugLogger extends DebugStack
{
    protected $logger;
    protected $stopwatch;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger    A LoggerInterface instance
     * @param Stopwatch       $stopwatch A Stopwatch instance
     */
    public function __construct(LoggerInterface $logger, Stopwatch $stopwatch = null)
    {
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        if ($this->enabled && null !== $this->stopwatch) {
            $this->stopwatch->start('doctrine', 'doctrine');
        }

        parent::startQuery($sql, $params, $types);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        if ($this->enabled && null !== $this->stopwatch) {
            $this->stopwatch->stop('doctrine');
        }

        parent::stopQuery();

        if ($this->enabled) {
            list($sql, $params, $types, $time) = array_values($this->queries[$this->currentQuery]);
            $time = number_format($time * 1000, 2);
            $this->logger->info('[' . $time . 'ms] ' . $sql . ' ' . json_encode($params) . ' ' . json_encode($types));
        }
    }
}
