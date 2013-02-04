<?php

namespace VGMdb\Component\Translation;

use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Loader\LoaderInterface;

/**
 * TranslationLoader loads translation messages from translation files.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TranslationLoader
{
    /**
     * Loaders used for import.
     *
     * @var array
     */
    private $loaders = array();

    /**
     * Format to extension mapping.
     *
     * @var array
     */
    private $extensions = array();

    /**
     * Sets the format to extension mapping.
     *
     * @param array $extensions
     */
    public function setExtensions(array $extensions = array())
    {
        $this->extensions = $extensions;
    }

    /**
     * Adds a loader to the translation extractor.
     *
     * @param string          $format The format of the loader
     * @param LoaderInterface $loader
     */
    public function addLoader($format, LoaderInterface $loader)
    {
        $this->loaders[$format] = $loader;
    }

    /**
     * Loads translation messages from a directory to the catalogue.
     *
     * @param string           $directory the directory to look into
     * @param MessageCatalogue $catalogue the catalogue
     */
    public function loadMessages($directory, MessageCatalogue $catalogue)
    {
        foreach ($this->loaders as $format => $loader) {
            // load any existing translation files
            $extension = $catalogue->getLocale().'.';
            if (array_key_exists($format, $this->extensions)) {
                $extension .= $this->extensions[$format];
            } else {
                $extension .= strtolower($format);
            }
            $files = glob($directory.'/*.'.$extension);
            foreach ($files as $file) {
                $domain = substr(basename($file), 0, -1 * strlen($extension) - 1);
                $catalogue->addCatalogue($loader->load($file, $catalogue->getLocale(), $domain));
            }
        }
    }
}
