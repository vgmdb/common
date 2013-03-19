<?php

namespace VGMdb\Component\Propel\Connection;

use Propel\Runtime\Connection\ProfilerConnectionWrapper as BaseProfilerConnectionWrapper;

/**
 * Adds the prepare function to the list of logged methods.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ProfilerConnectionWrapper extends BaseProfilerConnectionWrapper
{
    protected $logMethods = array(
        'exec',
        'query',
        'prepare',
        'execute',
    );
}
