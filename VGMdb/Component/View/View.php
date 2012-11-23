<?php

namespace VGMdb\Component\View;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * Nestable view container with rendering callback.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class View extends AbstractView
{
    public $template;
    static protected $engine;

    /**
     * Create a new view instance.
     *
     * @param string          $template
     * @param array           $data
     * @param mixed           $callback
     * @param LoggerInterface $logger
     */
    public function __construct($template, array $data = array(), $callback = null, LoggerInterface $logger = null)
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

        $output = $render($this->with($data));

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
}
