<?php

namespace VGMdb;

use VGMdb\Component\View\ViewInterface;
use Silex\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Sets the controller's layout.
     *
     * @param ViewInterface $view
     *
     * @return Controller $this The current Controller instance
     */
    public function layout(ViewInterface $view)
    {
        $this->getRoute()->setDefault('_layout', $view);

        return $this;
    }
}
