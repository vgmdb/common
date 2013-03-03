<?php

namespace VGMdb\Component\Guzzle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Adds request length details to the Symfony2 Profiler timeline.
 *
 * @author Chris Wilkinson <chris.wilkinson@admin.cam.ac.uk>
 */
class GuzzleRequestListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'request.before_send' => array('onRequestBeforeSend', 0),
            'request.complete' => array('onRequestComplete', 255),
        );
    }

    /**
     * @var Stopwatch|null
     */
    protected $stopwatch;

    /**
     * Constructor.
     *
     * @param Stopwatch|null $stopwatch
     */
    public function __construct(Stopwatch $stopwatch = null)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * Starts the stopwatch.
     */
    public function onRequestBeforeSend()
    {
        if (null !== $this->stopwatch) {
            $this->stopwatch->start('guzzle.request', 'guzzle');
        }
    }

    /**
     * Stops the stopwatch.
     */
    public function onRequestComplete()
    {
        if (null !== $this->stopwatch) {
            $this->stopwatch->stop('guzzle.request', 'guzzle');
        }
    }
}
