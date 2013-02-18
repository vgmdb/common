<?php

namespace VGMdb\Component\Translation;

use VGMdb\Component\Translation\Extractor\Model\Message;
use Symfony\Component\Translation\MessageCatalogue as BaseMessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * MessageCatalogue.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class MessageCatalogue extends BaseMessageCatalogue
{
    public function intersectCatalogue(MessageCatalogueInterface $catalogue)
    {
        if ($catalogue->getLocale() !== $this->getLocale()) {
            throw new \LogicException(sprintf('Cannot intersect a catalogue for locale "%s" as the current locale for this catalogue is "%s"', $catalogue->getLocale(), $this->getLocale()));
        }

        foreach ($catalogue->all() as $domain => $messages) {
            $existing = $this->all($domain);
            $messages = array_intersect_key($messages, $existing);

            foreach ($messages as $id => $message) {
                if ($existing[$id] instanceof Message && $message instanceof Message) {
                    if ($existing[$id]->getSourceString() !== $message->getSourceString()) {
                        $localeString = $message->getLocaleString();
                        $message = $existing[$id];
                        $message->setLocaleString($localeString);
                    }
                }

                parent::add(array($id => $message), $domain);
            }
        }
    }
}
