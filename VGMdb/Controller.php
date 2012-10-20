<?php

namespace VGMdb;

class Controller extends \Silex\Controller
{
    protected $route;

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
