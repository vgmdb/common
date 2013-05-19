<?php

namespace VGMdb\Component\Queue;

use Silex\Application;

/**
 * Container aware queue worker.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class ContainerAwareWorker extends AbstractWorker
{
    /**
     * @var Application|null
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function setContainer(Application $app = null)
    {
        $this->app = $app;
    }
}
