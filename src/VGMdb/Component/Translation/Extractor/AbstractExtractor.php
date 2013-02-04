<?php

namespace VGMdb\Component\Translation\Extractor;

use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Extractor\ExtractorInterface;

/**
 * AbstractExtractor provides shared utility functions for translation extractors.
 *
 * Try saying the name three times, fast.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractExtractor implements ExtractorInterface
{
    /**
     * Prefix for new found message (optional).
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * {@inheritDoc}
     */
    abstract public function extract($directory, MessageCatalogue $catalogue);

    /**
     * {@inheritDoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Generates an initial value for a newly extracted message ID.
     *
     * @param string $id
     *
     * @return string
     */
    protected function generateUntranslatedMessage($id)
    {
        return '';
    }

    /**
     * Recursively searches for files matching a pattern, including subdirectories.
     *
     * @param string  $pattern
     * @param integer $flags
     *
     * @return array
     */
    protected function findFiles($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern).'/*', \GLOB_ONLYDIR|\GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->findFiles($dir.'/'.basename($pattern), $flags));
        }

        return $files;
    }
}
