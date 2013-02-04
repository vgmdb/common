<?php

namespace VGMdb\Component\Translation\Dumper;

use Symfony\Component\Translation\Dumper\FileDumper;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * JsonFileDumper generates a JSON dump of a message catalogue.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class JsonFileDumper extends FileDumper
{
    /**
     * {@inheritDoc}
     */
    public function format(MessageCatalogue $messages, $domain = 'messages')
    {
        $output = array();
        foreach ($messages->all($domain) as $source => $target) {
            $output[$domain][$source] = $target;
        }

        return json_encode($output);
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtension()
    {
        return 'js';
    }
}
