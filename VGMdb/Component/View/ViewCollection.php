<?php

namespace VGMdb\Component\View;

/**
 * An array-like collection of ViewInterface objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
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
            if ($template instanceof ViewInterface) {
                $this[] = $template->with($data);
            } else {
                $this[] = ViewFactory::create($template, $data, $callback);
            }
        }
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function renderInternal($data = array())
    {
        $content = '';

        foreach ($this as $view) {
            $content .= $view->with($data)->render();
        }

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function getArrayCopy($globals = false)
    {
        $array = array();

        foreach ($this as $view) {
            $array[] = $view->getArrayCopy($globals);
        }

        return $array;
    }
}
