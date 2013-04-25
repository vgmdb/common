<?php

namespace VGMdb\Component\NewRelic\Monitor;

use VGMdb\Component\NewRelic\MonitorInterface;
use Psr\Log\LoggerInterface;

class LoggableMonitor implements MonitorInterface
{
    /**
     * @var MonitorInterface
     */
    protected $monitor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param MonitorInterface $monitor
     * @param LoggerInterface  $logger
     */
    public function __construct(MonitorInterface $monitor, LoggerInterface $logger = null)
    {
        $this->monitor = $monitor;
        $this->logger = $logger;
    }

    /**
     * Logs a handler action.
     *
     * @param string $message
     * @param array  $context
     */
    protected function log($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->debug($message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setApplicationName($name)
    {
        $this->log(sprintf('Setting New Relic Application name to %s', $name));
        $this->monitor->setApplicationName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationName()
    {
        $this->log('Getting New Relic Application name');

        return $this->monitor->getApplicationName();
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName($name)
    {
        $this->log(sprintf('Setting New Relic Transaction name to %s', $name));
        $this->monitor->setTransactionName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setBackgroundJob($flag)
    {
        $this->log(sprintf('Flagging New Relic Transaction as background job: %s', $flag ? 'true' : 'false'));
        $this->monitor->setBackgroundJob($flag);
    }

    /**
     * {@inheritdoc}
     */
    public function setCaptureParameters($flag)
    {
        $this->log(sprintf('Capturing New Relic request parameters: %s', $flag ? 'true' : 'false'));
        $this->monitor->setCaptureParameters($flag);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric($name, $value)
    {
        $this->log(sprintf('Adding custom New Relic metric %s: %s', $name, $value));
        $this->monitor->addCustomMetric((string) $name, (double) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter($name, $value)
    {
        $this->log(sprintf('Adding custom New Relic parameter %s: %s', $name, $value));
        $this->monitor->addCustomParameter((string) $name, (string) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader()
    {
        $this->log('Getting New Relic RUM timing header');

        return $this->monitor->getBrowserTimingHeader();
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter()
    {
        $this->log('Getting New Relic RUM timing footer');

        return $this->monitor->getBrowserTimingFooter();
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRum()
    {
        $this->log('Disabling New Relic Auto-RUM');
        $this->monitor->disableAutoRum();
    }

    /**
     * {@inheritdoc}
     */
    public function logError($message)
    {
        $this->log('Sending error message to New Relic');
        $this->monitor->logError($message);
    }

    /**
     * {@inheritdoc}
     */
    public function logException(\Exception $exception)
    {
        $this->log('Sending exception message to New Relic');
        $this->monitor->logException($exception);
    }
}
