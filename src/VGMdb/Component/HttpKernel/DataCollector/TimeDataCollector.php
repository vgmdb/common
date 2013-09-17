<?php

namespace VGMdb\Component\HttpKernel\DataCollector;

use VGMdb\Component\Silex\TraceableApplication;
use Silex\Application;
use Symfony\Component\HttpKernel\DataCollector\TimeDataCollector as BaseTimeDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Silex-compatible TimeDataCollector.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TimeDataCollector extends BaseTimeDataCollector
{
    protected $app;

    public function __construct(Application $app = null)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'start_time' => null !== $this->app && $this->app instanceof TraceableApplication
                ? $this->app->getStartTime() * 1000
                : $_SERVER['REQUEST_TIME'] * 1000,
            'events'     => array(),
        );
    }
}
