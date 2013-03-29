<?php

/*
 * This code was originally part of the Symfony2 FrameworkBundle.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */

namespace VGMdb\Component\Translation;

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
     * @param Boolean          $intersect whether to discard messages not already in catalogue
     */
    public function loadMessages($directory, MessageCatalogue $catalogue, $intersect = false)
    {
        foreach ($this->loaders as $format => $loader) {
            // load any existing translation files
            $extension = $catalogue->getLocale().'.';
            if (isset($this->extensions[$format])) {
                $extension .= $this->extensions[$format];
            } else {
                $extension .= strtolower($format);
            }
            $files = glob($directory.'/*.'.$extension);
            foreach ($files as $file) {
                $domain = substr(basename($file), 0, -1 * strlen($extension) - 1);
                $loaded = $loader->load($file, $catalogue->getLocale(), $domain);

                if ($intersect) {
                    $catalogue->intersectCatalogue($loaded);
                } else {
                    $catalogue->addCatalogue($loaded);
                }
            }
        }
    }
}
