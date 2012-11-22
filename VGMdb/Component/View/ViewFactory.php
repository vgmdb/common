<?php

namespace VGMdb\Component\View;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * @brief       View factory for creating views based on renderer type.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class ViewFactory
{
    /**
     * View factory for creating new view instances based on renderer type.
     *
     * @param mixed           $template
     * @param array           $data
     * @param mixed           $engine
     * @param LoggerInterface $logger
     * @return ViewInterface
     */
    static public function create($template, array $data = array(), $engine = null, LoggerInterface $logger = null)
    {
        if ($template instanceof ViewInterface) {
            return $template;
        }

        if ($engine instanceof \Mustache_Engine) {
            return new MustacheView($template, $data, $engine, $logger);
        }

        if ($engine instanceof \Smarty) {
            return new SmartyView($template, $data, $engine, $logger);
        }

        return new View($template, $data, $engine, $logger);
    }
}
