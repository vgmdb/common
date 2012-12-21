<?php

namespace VGMdb\Component\HttpKernel\Debug;

use Symfony\Component\HttpKernel\Debug\ErrorHandler as BaseErrorHandler;

/**
 * Handles general errors.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ErrorHandler extends BaseErrorHandler
{
    /**
     * Register the error handler. No levels here, it's all or nothing.
     *
     * @param Boolean $debug
     *
     * @return The registered error handler, or void if $debug is false.
     */
    public static function register($debug = true)
    {
        if (!$debug) {
            error_reporting(0);
        } else {
            error_reporting(-1);
            parent::register(null);
        }
    }
}
