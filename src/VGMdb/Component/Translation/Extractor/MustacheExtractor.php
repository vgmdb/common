<?php

namespace VGMdb\Component\Translation\Extractor;

use VGMdb\Component\Translation\Extractor\Model\FileSource;
use VGMdb\Component\Translation\Extractor\Model\Message;
use Silex\Application;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * MustacheExtractor extracts translation messages from a mustache template.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class MustacheExtractor extends AbstractExtractor
{
    const MSG_PATTERN = '%{{#\s*t\s*}}(.*){{/\s*t\s*}}%';
    const ID_PATTERN = '%\s*{{!\s*(.*)\s*}}\s*(.*)\s*%';

    protected $baseDir;

    public function __construct(Application $app)
    {
        $this->baseDir = $app['translator.extractor.base_dir'];
    }

    /**
     * {@inheritDoc}
     */
    public function extract($directory, MessageCatalogue $catalogue)
    {
        $files = $this->findFiles($directory.'/*.ms');
        foreach ($files as $file) {
            $fh = fopen($file, 'r');
            $lineCount = 0;
            while (!feof($fh)) {
                $line = fgets($fh);
                $lineCount++;
                if (preg_match_all(static::MSG_PATTERN, $line, $matches, \PREG_SET_ORDER|\PREG_OFFSET_CAPTURE)) {
                    foreach ($matches as $match) {
                        $domain = 'messages';
                        $id = $desc = $match[1][0];
                        $columnCount = $match[1][1] + 1;

                        if (preg_match(static::ID_PATTERN, $id, $tokens)) {
                            $id = $tokens[1];
                            $desc = $tokens[2];
                        }

                        $message = new Message($id, $domain);
                        $message->setDesc($desc);
                        $message->addSource(new FileSource((string) substr($file, strlen($this->baseDir) + 1), $lineCount, $columnCount));

                        $catalogue->set($id, $message, $domain);
                    }
                }
            }
            fclose($fh);
        }
    }
}
