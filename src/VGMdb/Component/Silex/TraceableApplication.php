<?php

namespace VGMdb\Component\Silex;

use VGMdb\Component\Silex\EventListener\ContainerTraceListener;

/**
 * The traceable application class. Used for profiling and debugging.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TraceableApplication extends Application
{
    private $bootlog;
    private $trace;
    private $stopwatch;
    private $startTime;

    /**
     * Constructor.
     */
    public function __construct(array $values = array())
    {
        $this->bootlog = array();
        $this->trace = array(
            'ids' => array(),
            'trace' => null
        );
        $this->startTime = microtime(true);

        parent::__construct($values);

        $app = $this;

        $this['dispatcher'] = $this->share($this->extend('dispatcher', function ($dispatcher) use ($app) {
            $dispatcher->addSubscriber(new ContainerTraceListener($app));

            return $dispatcher;
        }));
    }

    /**
     * Returns the request start time.
     *
     * @return integer
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Returns the service boot log.
     *
     * @return array
     */
    public function getBootlog()
    {
        return $this->bootlog;
    }

    /**
     * Stop profiling service access.
     */
    public function stopProfiling()
    {
        $this->stopwatch = null;
    }

    /**
     * Watch container IDs for read and write operations.
     */
    public function trace()
    {
        if (func_num_args()) {
            $this->trace['ids'] += func_get_args();
        }
    }

    /**
     * Get traces for watched container IDs.
     */
    public function getTrace()
    {
        return $this->trace['trace'];
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        self::$isBooting = true;

        if ($this['debug'] && isset($this['debug.stopwatch'])) {
            $start = microtime(true) * 1000;
            $this->stopwatch = $this['debug.stopwatch'];
            $end = microtime(true) * 1000;
            $this->bootlog['debug.stopwatch'] = sprintf('%.0f', $end - $start);
        }

        parent::boot();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($id, $value)
    {
        if (in_array($id, $this->trace['ids'])) {
            $this->trace['trace'] = new \Exception(
                sprintf('$app[\'%s\'] was written with value "%s".', $id, $this->varToString($value)),
                null,
                $this->trace['trace']
            );
        }

        parent::offsetSet($id, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($id)
    {
        if (in_array($id, $this->trace['ids'])) {
            $this->trace['trace'] = new \Exception(
                sprintf('$app[\'%s\'] was read.', $id),
                null,
                $this->trace['trace']
            );
        }

        $event = null;

        if (null !== $this->stopwatch) {
            $event = $this->stopwatch->start($id, 'boot');
        }

        $value = parent::offsetGet($id);

        if (null !== $event) {
            $event->stop($id);
            if (!isset($this->bootlog[$id])) {
                $this->bootlog[$id] = $event->getDuration();
            }
        }

        return $value;
    }

    /**
     * Converts a PHP variable to a string.
     *
     * @param mixed $var A PHP variable
     *
     * @return string The string representation of the variable
     */
    protected function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('Object(%s)', get_class($var));
        }

        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, $this->varToString($v));
            }

            return sprintf("Array(%s)", implode(', ', $a));
        }

        if (is_resource($var)) {
            return sprintf('Resource(%s)', get_resource_type($var));
        }

        if (null === $var) {
            return 'null';
        }

        if (false === $var) {
            return 'false';
        }

        if (true === $var) {
            return 'true';
        }

        return (string) $var;
    }
}
