<?php

namespace VGMdb\Component\HttpKernel\Debug;

/**
 * @brief       Handles general errors. Mostly copied from Symfony's HttpKernel.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class ErrorHandler
{
    private $levels = array(
        E_WARNING           => 'Warning',
        E_NOTICE            => 'Notice',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated',
    );

    public function __construct()
    {
        error_reporting(-1);
    }

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
            $handler = new static();
            set_error_handler(array($handler, 'handle'));
            return $handler;
        }
    }

    /**
     * @throws \ErrorException When error_reporting returns error
     */
    public function handle($level, $message, $file, $line, $context)
    {
        if (0 === error_reporting()) {
            return false;
        }

        throw new \ErrorException(sprintf('%s: %s in %s line %d', isset($this->levels[$level]) ? $this->levels[$level] : $level, $message, $file, $line), 0, $level, $file, $line);

        return false;
    }
}
