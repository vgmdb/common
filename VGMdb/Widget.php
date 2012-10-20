<?php

namespace VGMdb;

/**
 * @brief       Widgets are mini-controllers with templating capabilities.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class Widget extends AbstractView
{
    private $view;
    private $callback;

    /**
     * Create a new widget instance.
     *
     * @param ViewInterface $view
     * @param \Closure      $callback
     * @return void
     */
    public function __construct(ViewInterface $view, $callback = null)
    {
        $this->view = $view;
        $this->callback = $callback;
    }

    /**
     * Insert another view as a data element.
     *
     * @param mixed  $view
     * @param string $key
     * @return Widget
     */
    public function nest($view, $key = 'content')
    {
        if (!($view instanceof ViewInterface)) {
            $view = new View((string) $view);
        }

        if (isset($this[$key]) && $this[$key] instanceof ViewInterface) {
            if (!($this[$key] instanceof ViewCollection)) {
                $this[$key] = new ViewCollection($this[$key]);
            }
            $this[$key]->nest($view);
        } else {
            $this[$key] = $view;
        }

        return $this;
    }

    /**
     * Get the evaluated string content of the widget.
     *
     * @param array $data
     * @return string
     */
    public function render($data = array())
    {
        if ($callback = $this->callback) {
            $this->with($callback($this));
        }

        return $this->view->render($this);
    }
}