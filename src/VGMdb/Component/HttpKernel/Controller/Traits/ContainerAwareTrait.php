<?php

namespace VGMdb\Component\HttpKernel\Controller\Traits;

use Silex\Application;

/**
 * ContainerAware trait.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
trait ContainerAwareTrait
{
    protected $app;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app = null)
    {
        $this->setContainer($app);
    }

    /**
     * Attaches the application context.
     *
     * @param Application $app
     */
    public function setContainer(Application $app = null)
    {
        $this->app = $app;
    }
}
