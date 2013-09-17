<?php

namespace VGMdb\Component\HttpKernel\DataCollector;

use VGMdb\Component\Silex\TraceableApplication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects container data.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ContainerDataCollector extends DataCollector
{
    private $app;

    /**
     * Constructor.
     *
     * @param TraceableApplication $app
     */
    public function __construct(TraceableApplication $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $services = array();
        $keys = $this->app->keys();
        sort($keys);
        $bootlog = $this->app->getBootlog();

        $this->app->stopProfiling();

        foreach ($keys as $key) {
            $booted = isset($bootlog[$key]);
            $services[] = array(
                'key' => $key,
                'value' => $booted ? $this->varToString($this->app[$key]) : null,
                'booted' => $booted,
                'duration' => $booted ? $bootlog[$key] : null,
            );
        }

        $this->data = array(
            'services' => $services,
            'keycount' => count($services),
            'bootcount' => count($bootlog),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'container';
    }
}
