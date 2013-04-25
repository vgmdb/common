<?php

namespace VGMdb\Component\NewRelic\Monitor;

use VGMdb\Component\NewRelic\MonitorInterface;

class ExtensionMonitor implements MonitorInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * {@inheritdoc}
     */
    public function setApplicationName($name)
    {
        $this->name = $name;
        newrelic_set_appname($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionName($name)
    {
        newrelic_name_transaction($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setBackgroundJob($flag)
    {
        newrelic_background_job($flag);
    }

    /**
     * {@inheritdoc}
     */
    public function setCaptureParameters($flag)
    {
        newrelic_capture_params($flag);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomMetric($name, $value)
    {
        newrelic_custom_metric((string) $name, (double) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomParameter($name, $value)
    {
        newrelic_custom_parameter((string) $name, (string) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingHeader()
    {
        return newrelic_get_browser_timing_header();
    }

    /**
     * {@inheritdoc}
     */
    public function getBrowserTimingFooter()
    {
        return newrelic_get_browser_timing_footer();
    }

    /**
     * {@inheritdoc}
     */
    public function disableAutoRum()
    {
        newrelic_disable_autorum();
    }

    /**
     * {@inheritdoc}
     */
    public function logError($message)
    {
        newrelic_notice_error($message);
    }

    /**
     * {@inheritdoc}
     */
    public function logException(\Exception $exception)
    {
        newrelic_notice_error(null, $exception);
    }
}
