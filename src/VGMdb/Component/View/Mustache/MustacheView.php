<?php

namespace VGMdb\Component\View\Mustache;

use VGMdb\Component\View\AbstractView;
use VGMdb\Component\View\Logger\ViewLoggerInterface;

/**
 * View with Mustache rendering engine.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class MustacheView extends AbstractView
{
    protected static $engine;

    /**
     * Create a new Mustache view instance.
     *
     * @param string              $template
     * @param array               $data
     * @param \Mustache_Engine    $mustache
     * @param ViewLoggerInterface $logger
     */
    public function __construct($template, array $data = array(), $mustache = null, ViewLoggerInterface $logger = null)
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
    public function render($data = array())
    {
        foreach (static::globals() as $key => $value) {
            self::$engine->addHelper($key, $value);
        }

        return parent::render($data);
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
    public function getArrayCopy($globals = false)
    {
        $data = array_merge(parent::getArrayCopy($globals), array('_template' => $this->template));

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getEngineType()
    {
        return 'Mustache';
    }
}
