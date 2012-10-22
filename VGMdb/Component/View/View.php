<?php

namespace VGMdb\Component\View;

/**
 * @brief       Nestable view container with rendering callback.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class View extends AbstractView
{
    public $template;
    public $render_callback;

    /**
     * Create a new view instance, with optional callback for render().
     *
     * @param string   $template
     * @param array    $data
     * @param \Closure $callback
     * @return void
     */
    public function __construct($template, array $data = array(), \Closure $callback = null)
    {
        $this->template = $template;
        if (!$callback) {
            $callback = function ($view) {
                return vsprintf($view->template, $view);
            };
        }
        $this->render_callback = $callback;
        parent::__construct($data);
    }

    /**
     * Convenience static method for creating a new view instance.
     *
     * @param mixed    $template
     * @param array    $data
     * @param \Closure $callback
     * @return View
     */
    static public function create($template, array $data = array(), \Closure $callback = null)
    {
        if ($template instanceof ViewInterface) {
            return $template;
        }

        return new static($template, $data, $callback);
    }

    /**
     * Insert a view object as a data element.
     *
     * @param mixed  $view
     * @param string $key
     * @return View
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
     * Get the evaluated string content of the view.
     *
     * @param array $data
     * @return string
     */
    public function render($data = array())
    {
        $render = $this->with($data)->render_callback;

        return $render($this);
    }
}