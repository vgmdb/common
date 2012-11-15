<?php

namespace VGMdb;

use VGMdb\Component\View\ViewInterface;
use Silex\Controller as BaseController;

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
