<?php

namespace VGMdb;

/**
 * @brief       Abstract view object.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractView extends \ArrayObject implements ViewInterface
{
    static protected $globals = array();

    /**
     * Initialize view data.
     *
     * @param array $data
     * @return View
     */
    public function with($data = array())
    {
        foreach ($data as $key => $value) {
            $this[$key] = $value;
        }

        return $this;
    }

    /**
     * Apply global value across all views.
     *
     * @param mixed $data
     * @param mixed $value
     */
    static public function share($data, $value = null)
    {
        if (!is_array($data)) {
            $data = array($data => $value);
        }

        foreach ($data as $key => $value) {
            if (strtoupper($key) !== $key) {
                throw new \InvalidArgumentException(sprintf('Global "%s" must be uppercased.', $key));
            }
            static::$globals[$key] = $value;
        }
    }

    /**
     * Wraps a view object with another view.
     *
     * @param mixed  $view
     * @param string $key
     * @return View
     */
    public function wrap($view, $key = 'content')
    {
        if (!($view instanceof ViewInterface)) {
            $view = new View((string) $view);
        }

        $view[$key] = $this;

        return $view;
    }

    /**
     * Get the evaluated string content of the view.
     *
     * @param array $data
     * @return string
     */
    public function render($data = array())
    {
        throw new \RuntimeException('Missing implementation for render()');
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
        throw new \RuntimeException('Missing implementation for nest()');
    }

    /**
     * Get the evaluated string content of the view, magically.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $content = $this->render();
        } catch (\Exception $e) {
            $content = (isset($this['DEBUG']) && $this['DEBUG'] === true) ? $e->__toString() : '';
        }

        return $content;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param  string $id The unique identifier for the parameter or object
     * @return mixed The value of the parameter or an object
     * @throws \InvalidArgumentException if the identifier is not defined
     */
    function offsetGet($id)
    {
        if (array_key_exists($id, static::$globals)) {
            $value = static::$globals[$id];
        } else {
            if (!$this->offsetExists($id)) {
                throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
            }
            $value = parent::offsetGet($id);
        }

        return $value instanceof \Closure ? $value($this) : $value;
    }

    /**
     * Returns whether the requested index exists.
     *
     * @param  string $id The unique identifier for the parameter or object
     * @return Boolean True if the requested index exists, false otherwise
     */
    public function offsetExists($id)
    {
        if (array_key_exists($id, static::$globals)) {
            return true;
        }

        return parent::offsetExists($id);
    }

    /**
     * Exports data to an array.
     *
     * @return array Exported array.
     */
    public function getArrayCopy()
    {
        $data = array_merge(parent::getArrayCopy(), static::$globals);

        foreach ($data as $key => $value) {
            if ($value instanceof ViewInterface) {
                $data[$key] = $value->getArrayCopy();
            }
        }

        return $data;
    }
}