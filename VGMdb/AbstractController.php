<?php

namespace VGMdb;

/**
 * Base class for application controllers.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractController
{
    protected $app;

    /**
     * Attaches the application context.
     *
     * @param Application $app
     */
    public function setContainer(Application $app)
    {
        $this->app = $app;
    }
}
