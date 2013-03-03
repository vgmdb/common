<?php

namespace VGMdb\Component\View\Logger;

use VGMdb\Component\View\ViewInterface;

/**
 * Function signatures for logging and profiling views.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface ViewLoggerInterface
{
    /**
     * Logs the start of view rendering.
     *
     * @param ViewInterface $view
     */
    public function startRender(ViewInterface $view);

    /**
     * Logs the completion of view rendering.
     *
     * @param ViewInterface $view
     */
    public function stopRender(ViewInterface $view);
}
