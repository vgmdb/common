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
    public function __construct($template, array $data = array(), $callback = null)
    {
        $this->template = $template;

        if (!$callback) {
            $callback = function ($view) {
                return vsprintf($view->template, $view);
            };
        }

        if (!($callback instanceof \Closure)) {
            throw new \InvalidArgumentException('Callback must be a Closure.');
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
    static public function create($template, array $data = array(), $callback = null)
    {
        if ($template instanceof ViewInterface) {
            return $template;
        }

        return new static($template, $data, $callback);
    }

    /**
     * {@inheritDoc}
     */
    static public function share($data, $value = null)
    {
        if (!(is_array($data) || $data instanceof \ArrayAccess)) {
            $data = array($data => $value);
        }

        foreach ($data as $key => $value) {
            if (strtoupper($key) !== $key) {
                throw new \InvalidArgumentException(sprintf('Global "%s" must be uppercased.', $key));
            }
            self::$globals[$key] = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function nest($view, $key = 'content')
    {
        if (!($view instanceof ViewInterface)) {
            $view = new static((string) $view);
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
    public function render($data = array())
    {
        $render = $this->with($data)->render_callback;

        return $render($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getArrayCopy($globals = false)
    {
        $data = array_merge(parent::getArrayCopy($globals), array('_template' => $this->template));

        return $data;
    }
}
