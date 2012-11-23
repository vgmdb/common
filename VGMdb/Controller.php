<?php

namespace VGMdb;

use VGMdb\Component\View\ViewInterface;
use Silex\Controller as BaseController;

/**
 * Extends the Silex Controller with a convenience layout function.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Controller extends BaseController
{
    /**
     * Sets the controller's layout.
     *
     * @param string $layout Layout name.
     *
     * @return Controller $this The current Controller instance
     */
    public function layout($layout)
    {
        $this->getRoute()->setDefault('_layout', $layout);

        return $this;
    }
}
