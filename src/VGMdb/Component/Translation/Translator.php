<?php

namespace VGMdb\Component\Translation;

use VGMdb\Component\Translation\Extractor\Model\Message;
use Symfony\Component\Translation\Translator as BaseTranslator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\LoaderInterface;

/**
 * Translator.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Translator extends BaseTranslator
{
    protected $selector;

    public function __construct($locale, MessageSelector $selector = null)
    {
        $this->selector = null === $selector ? new MessageSelector() : $selector;

        parent::__construct($locale, $selector);
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        if (!isset($locale)) {
            $locale = $this->getLocale();
        }

        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        $id = (string) $id;
        $message = $this->catalogues[$locale]->get($id, $domain);

        if (!is_object($message)) {
            $message = strtr($message, $parameters);
            if (strlen($message) === 0) {
                return $id;
            }
        } elseif ($message instanceof Message) {
            $message->setParameters($parameters);
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        if (!isset($locale)) {
            $locale = $this->getLocale();
        }

        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        $id = (string) $id;
        $catalogue = $this->catalogues[$locale];
        while (!$catalogue->defines($id, $domain)) {
            if ($cat = $catalogue->getFallbackCatalogue()) {
                $catalogue = $cat;
                $locale = $catalogue->getLocale();
            } else {
                break;
            }
        }
        $message = $catalogue->get($id, $domain);
        $choice = $this->selector->choose((string) $message, (float) $number, $locale);

        if (!is_object($message)) {
            $message = strtr($choice, $parameters);
            if (strlen($message) === 0) {
                return $id;
            }
        } elseif ($message instanceof Message) {
            $message->setLocaleString($choice);
            $message->setParameters($parameters);
        }

        return $message;
    }
}
