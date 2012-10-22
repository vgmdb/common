<?php

namespace VGMdb\Component\View;

/**
 * @brief       An array-like collection of ViewInterface objects.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class ViewCollection extends AbstractView
{
    /**
     * Create a new view collection.
     *
     * @param string   $template
     * @param array    $data
     * @param \Closure $callback
     * @return void
     */
    public function __construct($template = null, array $data = array(), \Closure $callback = null)
    {
        if ($template) {
            if ($template instanceof View) {
                $this[] = $template->with($data);
            } else {
                $this[] = View::create($template, $data, $callback);
            }
        }
    }

    /**
     * Initialize template data across all views.
     *
     * @param array $data
     * @return ViewCollection
     */
    public function with($data = array())
    {
        foreach ($data as $key => $value) {
            foreach ($this as $view) {
                $view[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Insert a view object as a part of the collection.
     *
     * @param mixed  $view
     * @param string $key
     * @return ViewCollection
     */
    public function nest($view, $key = 'content')
    {
        if (!($view instanceof ViewInterface)) {
            $view = new View((string) $view);
        }

        $this[] = $view;

        return $this;
    }

    /**
     * Get the evaluated string content of all views.
     *
     * @param array $data
     * @return string
     */
    public function render($data = array())
    {
        $content = '';

        foreach ($this as $view) {
            $content .= $view->with($data)->render();
        }

        return $content;
    }

    /**
     * Exports data to an array.
     *
     * @return array Exported array.
     */
    public function getArrayCopy()
    {
        $array = array();

        foreach ($this as $view) {
            $array[] = $view->getArrayCopy();
        }

        return $array;
    }
}