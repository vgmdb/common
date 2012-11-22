<?php

namespace VGMdb\Component\View;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * @brief       View with Mustache rendering engine.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class MustacheView extends AbstractView
{
    public $template;
    static protected $engine;

    /**
     * Create a new Mustache view instance.
     *
     * @param string           $template
     * @param array            $data
     * @param \Mustache_Engine $mustache
     * @param LoggerInterface  $logger
     */
    public function __construct($template, array $data = array(), $mustache = null, LoggerInterface $logger = null)
    {
        if (!is_string($template)) {
            throw new \InvalidArgumentException('Template name must be a string.');
        }

        $this->template = $template;

        if ($mustache && !($mustache instanceof \Mustache_Engine)) {
            throw new \InvalidArgumentException('Invalid Mustache object.');
        }

        parent::__construct($data, $mustache, $logger);
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
            self::$engine->addHelper($key, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    static public function globals()
    {
        $data = array();

        foreach (self::$engine->getHelpers() as $name => $helper) {
            if (is_scalar($helper) || is_null($helper) || is_array($helper)) {
                $data[$name] = $helper;
            }
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    protected function renderInternal($data = array())
    {
        $output = self::$engine->loadTemplate($this->template)->render($this->with($data));

        return $output;
    }

    /**
     * {@inheritDoc}
     */
    function offsetGet($id)
    {
        if (self::$engine->hasHelper($id)) {
            $value = self::$engine->getHelper($id);
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
        if (self::$engine->hasHelper($id)) {
            $value = self::$engine->getHelper($id);
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
