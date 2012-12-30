<?php

namespace VGMdb\Component\HttpKernel\DataCollector;

use VGMdb\Application;
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
     * @param Application $app
     */
    public function __construct(Application $app)
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
