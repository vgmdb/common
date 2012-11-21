<?php

namespace VGMdb\Component\View;

/**
 * @brief       View with Mustache rendering engine.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class MustacheView extends AbstractView
{
    public $template;
    static private $mustache;

    /**
     * Create a new Mustache view instance.
     *
     * @param string           $template
     * @param array            $data
     * @param \Mustache_Engine $mustache
     * @return void
     */
    public function __construct($template, array $data = array(), $mustache = null)
    {
        if (!is_string($template)) {
            throw new \InvalidArgumentException('Template name must be a string.');
        }

        $this->template = $template;

        if ($mustache) {
            if (!($mustache instanceof \Mustache_Engine)) {
                throw new \InvalidArgumentException('Invalid Mustache object.');
            }
            self::$mustache = $mustache;
        }

        parent::__construct($data);
    }

    /**
     * Convenience static method for creating a new view instance.
     *
     * @param string           $template
     * @param array            $data
     * @param \Mustache_Engine $mustache
     * @return MustacheView
     */
    static public function create($template, array $data = array(), $mustache = null)
    {
        if ($template instanceof ViewInterface) {
            return $template;
        }

        return new static($template, $data, $mustache);
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
            self::$mustache->addHelper($key, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    static public function globals()
    {
        $data = array();

        foreach (self::$mustache->getHelpers() as $name => $helper) {
            if (is_scalar($helper) || is_null($helper) || is_array($helper)) {
                $data[$name] = $helper;
            }
        }

        return $data;
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
        return self::$mustache->loadTemplate($this->template)->render($this->with($data));
    }

    /**
     * {@inheritDoc}
     */
    function offsetGet($id)
    {
        if (self::$mustache->hasHelper($id)) {
            $value = self::$mustache->getHelper($id);
        } else {
            if (!$this->offsetExists($id)) {
                throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
            }
            $value = parent::offsetGet($id);
        }

        return $value instanceof \Closure ? $value($this) : $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($id)
    {
        if (self::$mustache->hasHelper($id)) {
            $value = self::$mustache->getHelper($id);
            if (is_scalar($value) || is_null($value) || is_array($value)) {
                return true;
            }
        }

        return parent::offsetExists($id);
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
