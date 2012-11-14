<?php

namespace VGMdb\Component\HttpFoundation\Session\Storage\Handler;

/**
 * NativeRedisSessionStorage.
 *
 * Driver for the redis session save handler provided by the redis PHP extension.
 *
 * @see https://github.com/nicolasff/phpredis
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class NativeRedisSessionHandler extends NativeSessionHandler
{
    /**
     * Constructor.
     *
     * @param string $savePath Path of redis server.
     */
    public function __construct($savePath = 'tcp://127.0.0.1:6379?persistent=0')
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException('PHP does not have "redis" session module registered');
        }

        if (!strlen($savePath)) {
            $savePath = ini_get('session.save_path');
        }

        ini_set('session.save_path', $savePath);
        ini_set('session.save_handler', 'redis');
    }
}
