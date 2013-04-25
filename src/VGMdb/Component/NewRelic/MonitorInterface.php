<?php

namespace VGMdb\Component\NewRelic;

/**
 * Interface for the New Relic PHP API.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface MonitorInterface
{
    /**
     * @param string $name
     */
    public function setApplicationName($name);

    /**
     * @return string
     */
    public function getApplicationName();

    /**
     * @param string $name
     */
    public function setTransactionName($name);

    /**
     * @param Boolean $flag
     */
    public function setBackgroundJob($flag);

    /**
     * @param Boolean $flag
     */
    public function setCaptureParameters($flag);

    /**
     * @param string $name
     * @param string $value
     */
    public function addCustomMetric($name, $value);

    /**
     * @param string $name
     * @param string $value
     */
    public function addCustomParameter($name, $value);

    /**
     * @return string
     */
    public function getBrowserTimingHeader();

    /**
     * @return string
     */
    public function getBrowserTimingFooter();

    public function disableAutoRum();

    /**
     * @param string $message
     */
    public function logError($message);

    /**
     * @param \Exception $exception
     */
    public function logException(\Exception $exception);
}
