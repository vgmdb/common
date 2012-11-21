<?php

namespace VGMdb\Component\View;

/**
 * @brief       Abstract view object.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractView extends \ArrayObject implements ViewInterface
{
    static protected $globals = array();

    /**
     * {@inheritDoc}
     */
    public function with($data, $value = null)
    {
        if (!is_array($data) && !($data instanceof \ArrayAccess)) {
            $data = array($data => $value);
        }

        foreach ($data as $key => $value) {
            $this[$key] = $value;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    static public function share($data, $value = null)
    {
        throw new \RuntimeException('Missing implementation for share()');
    }

    /**
     * {@inheritDoc}
     */
    static public function globals()
    {
        return static::$globals;
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
     * {@inheritDoc}
     */
    public function render($data = array())
    {
        throw new \RuntimeException('Missing implementation for render()');
    }

    /**
     * {@inheritDoc}
     */
    public function nest($view, $key = 'content')
    {
        throw new \RuntimeException('Missing implementation for nest()');
    }

    /**
     * {@inheritDoc}
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
        if (array_key_exists($id, self::$globals)) {
            $value = self::$globals[$id];
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
        if (array_key_exists($id, self::$globals)) {
            return true;
        }

        return parent::offsetExists($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getArrayCopy($globals = false)
    {
        $data = parent::getArrayCopy();

        if ($globals) {
            $data = array_merge($data, static::globals());
        }

        foreach ($data as $key => $value) {
            if ($value instanceof ViewInterface) {
                $data[$key] = $value->getArrayCopy();
            }
        }

        return $data;
    }
}
