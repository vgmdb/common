<?php

namespace VGMdb\Component\Thrift;

/**
 * Interface definition for Thrift service handlers.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface ThriftHandlerInterface
{
    /**
     * Get the associated processor for the handler.
     *
     * @return mixed
     */
    public function getProcessor();
}
