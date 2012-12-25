<?php

namespace VGMdb\Component\View\Mustache\Loader;

/**
 * Prefix to directory mapping Loader implementation.
 * Essentially the FilesystemLoader with support for multiple base directories through the use of prefixes.
 *
 * @implements Loader
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class PrefixLoader extends \Mustache_Loader_FilesystemLoader
{
    static private $prefixDirs = array();

    /**
     * Add a directory prefix.
     *
     * @param string $prefix
     * @param string $prefixDir
     */
    static public function addPrefix($prefix, $prefixDir)
    {
        $prefixDir = rtrim(realpath($prefixDir), '/');

        if (!is_dir($prefixDir)) {
            throw new \RuntimeException('PrefixDir must be a directory: '.$prefixDir);
        }

        self::$prefixDirs[$prefix] = $prefixDir;
    }

    /**
     * Helper function for getting a Mustache template file name.
     *
     * @param string $name
     *
     * @return string Template file name
     */
    protected function getFileName($name)
    {
        if ($name[0] === '@') {
            list($prefix, $file) = explode('/', substr($name, 1), 2);
            if (isset(self::$prefixDirs[$prefix])) {
                $prefix = self::$prefixDirs[$prefix];
            }
            $fileName = $prefix . '/' . $file;

            if (substr($fileName, 0 - strlen($this->extension)) !== $this->extension) {
                $fileName .= $this->extension;
            }

            return $fileName;
        }

        return parent::getFileName($name);
    }
}
