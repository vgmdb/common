<?php

namespace VGMdb\Component\Translation;

use Symfony\Component\Translation\Translator as BaseTranslator;
use Symfony\Component\Translation\Loader\LoaderInterface;

/**
 * Translator.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Translator extends BaseTranslator
{
    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        $message = parent::trans($id, $parameters, $domain, $locale);

        if (strlen($message) === 0) {
            return (string) $id;
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        $message = parent::transChoice($id, $number, $parameters, $domain, $locale);

        if (strlen($message) === 0) {
            return (string) $id;
        }

        return $message;
    }
}
