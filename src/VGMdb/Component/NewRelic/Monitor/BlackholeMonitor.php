<?php

namespace VGMdb\Component\NewRelic\Monitor;

use VGMdb\Component\NewRelic\MonitorInterface;

class BlackholeMonitor implements MonitorInterface
{
    /**
     * {@inheritdoc}
     */
    public function setApplicationName($name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationName()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName($name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setBackgroundJob($flag)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setCaptureParameters($flag)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric($name, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter($name, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader()
    {
        return '<script>/* RUM HEADER */</script>';
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter()
    {
        return '<script>/* RUM FOOTER */</script>';
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRum()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function logError($message)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function logException(\Exception $exception)
    {
    }
}
