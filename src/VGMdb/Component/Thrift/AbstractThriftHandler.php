<?php

namespace VGMdb\Component\Thrift;

use VGMdb\Application;

/**
 * Base class for Thrift service handlers.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractThriftHandler implements ThriftHandlerInterface
{
    protected $app;

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
     * Get the associated processor for the handler.
     *
     * @return mixed
     */
    abstract public function getProcessor();
}
