<?php

namespace VGMdb\Component\View;

use VGMdb\Component\View\Logger\ViewLoggerInterface;

/**
 * Nestable view container with rendering callback.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class View extends AbstractView
{
    public $template;
    protected static $engine;

    /**
     * Create a new view instance.
     *
     * @param string              $template
     * @param array               $data
     * @param mixed               $callback
     * @param ViewLoggerInterface $logger
     */
    public function __construct($template, array $data = array(), $callback = null, ViewLoggerInterface $logger = null)
    {
        $this->template = $template;

        if ($callback && !($callback instanceof \Closure)) {
            throw new \InvalidArgumentException('Callback must be a Closure.');
        }

        parent::__construct($data, $callback, $logger);
    }

    /**
     * {@inheritDoc}
     */
    protected function renderInternal($data = array())
    {
        $render = self::$engine;

        $output = $render($this);

        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function getArrayCopy($globals = false)
    {
        $data = parent::getArrayCopy($globals);

        if (null !== $this->template) {
            $data = array_merge($data, array('_template' => $this->template));
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getEngineType()
    {
        return 'Closure';
    }
}
