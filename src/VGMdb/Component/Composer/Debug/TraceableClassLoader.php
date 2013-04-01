<?php

namespace VGMdb\Component\Composer\Debug;

/**
 * Keeps a list of loaded classnames.
 *
 * Note that classes loaded by include() in the Composer autoloader will not show up here.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TraceableClassLoader
{
    private static $classes = array();

    public static function loadClass($class)
    {
        static::$classes[] = $class;

        return false;
    }

    public static function register()
    {
        spl_autoload_register(get_called_class() . '::loadClass', false, true);
    }

    public static function getLoadedClasses()
    {
        return static::$classes;
    }
}
