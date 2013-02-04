<?php

namespace VGMdb\Component\Translation\Extractor;

use Symfony\Component\Translation\MessageCatalogue;

/**
 * MustacheExtractor extracts translation messages from a mustache template.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class MustacheExtractor extends AbstractExtractor
{
    const PATTERN = '%{{#\s*t\s*}}(.*){{/\s*t\s*}}%';

    /**
     * {@inheritDoc}
     */
    public function extract($directory, MessageCatalogue $catalogue)
    {
        $files = $this->findFiles($directory.'/*.ms');
        foreach ($files as $file) {
            if (preg_match_all(static::PATTERN, file_get_contents($file), $matches, \PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $message = $match[1];
                    $catalogue->set($message, $this->generateUntranslatedMessage($message));
                }
            }
        }
    }
}
