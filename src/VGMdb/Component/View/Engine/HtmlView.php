<?php

namespace VGMdb\Component\View\Engine;

use VGMdb\Component\View\AbstractView;
use VGMdb\Component\View\Logger\ViewLoggerInterface;

/**
 * Regular HTML files.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class HtmlView extends AbstractView
{
    protected static $engine;

    /**
     * Create a new view instance.
     *
     * @param string              $template
     * @param array               $data
     * @param ViewLoggerInterface $logger
     */
    public function __construct($template, array $data = array(), ViewLoggerInterface $logger = null)
    {
        if (!is_string($template)) {
            throw new \InvalidArgumentException('Template name must be a string.');
        }

        $this->template = $template;

        parent::__construct($data, null, $logger);
    }

    /**
     * {@inheritDoc}
     */
    protected function renderInternal($data = array())
    {
        $this->with($data);

        $template = file_get_contents($this->template . '.html');

        return strtr($template, parent::getArrayCopy(true));
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
        return 'Html';
    }
}
