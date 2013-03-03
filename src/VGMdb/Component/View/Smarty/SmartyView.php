<?php

namespace VGMdb\Component\View\Smarty;

use VGMdb\Component\View\AbstractView;
use VGMdb\Component\View\Logger\ViewLoggerInterface;

/**
 * View with Smarty rendering engine.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class SmartyView extends AbstractView
{
    protected static $engine;

    /**
     * Create a new Smarty view instance.
     *
     * @param string              $template
     * @param array               $data
     * @param \Smarty             $smarty
     * @param ViewLoggerInterface $logger
     */
    public function __construct($template, array $data = array(), $smarty = null, ViewLoggerInterface $logger = null)
    {
        if (!is_string($template)) {
            throw new \InvalidArgumentException('Template name must be a string.');
        }

        $this->template = $template;

        if ($smarty && !($smarty instanceof \Smarty)) {
            throw new \InvalidArgumentException('Invalid Smarty object.');
        }

        parent::__construct($data, $smarty, $logger);
    }

    /**
     * {@inheritDoc}
     */
    protected function renderInternal($data = array())
    {
        $this->with($data);

        foreach ($this as $key => $value) {
            self::$engine->assign($key, $value);
        }

        $output = self::$engine->fetch($this->template . '.tpl');

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
        return 'Smarty';
    }
}
