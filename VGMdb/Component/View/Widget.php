<?php

namespace VGMdb\Component\View;

/**
 * Widgets are mini-controllers with templating capabilities.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Widget extends AbstractView
{
    protected $view;
    protected $callback;

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
     * {@inheritDoc}
     */
    public function nest($view, $key = 'content')
    {
        if (!($view instanceof ViewInterface)) {
            $view_class = get_class($this->view);
            $view = new $view_class((string) $view);
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
     * {@inheritDoc}
     */
    protected function renderInternal($data = array())
    {
        if ($callback = $this->callback) {
            $this->with($callback($this));
        }

        return $this->view->render($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getArrayCopy($globals = false)
    {
        $data = array_merge(parent::getArrayCopy($globals), array('_template' => $this->view->template));

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getEngineType()
    {
        return 'Widget';
    }
}
