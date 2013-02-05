<?php

/*
 * This code was originally part of JMSTranslationBundle.
 *
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 */

namespace VGMdb\Component\Translation\Loader;

use VGMdb\Component\Translation\Extractor\Model\Message;
use VGMdb\Component\Translation\Extractor\Model\FileSource;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * XliffFileLoader loads translations from XLIFF files.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class XliffFileLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        if (!stream_is_local($resource)) {
            throw new InvalidResourceException(sprintf('This is not a local file "%s".', $resource));
        }

        if (!file_exists($resource)) {
            throw new NotFoundResourceException(sprintf('File "%s" not found.', $resource));
        }

        $previous = libxml_use_internal_errors(true);
        if (false === $doc = simplexml_load_file($resource)) {
            libxml_use_internal_errors($previous);
            $libxmlError = libxml_get_last_error();

            throw new \RuntimeException(sprintf('Could not load XML file "%s": %s', $resource, $libxmlError->message));
        }
        libxml_use_internal_errors($previous);

        $doc->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:1.2');
        $doc->registerXPathNamespace('jms', 'urn:jms:translation');

        $hasReferenceFiles = in_array('urn:jms:translation', $doc->getNamespaces(true));

        $catalogue = new MessageCatalogue($locale);

        foreach ($doc->xpath('//xliff:trans-unit') as $trans) {
            $id = ($resName = (string) $trans->attributes()->resname)
                       ? $resName : (string) $trans->source;

            $message = Message::create($id, $domain)->setDesc((string) $trans->source)->setLocaleString((string) $trans->target);
            $catalogue->set($id, $message, $domain);

            if ($hasReferenceFiles) {
                foreach ($trans->xpath('./jms:reference-file') as $file) {
                    $line = (string) $file->attributes()->line;
                    $column = (string) $file->attributes()->column;
                    $message->addSource(new FileSource(
                        (string) $file,
                        $line ? (integer) $line : null,
                        $column ? (integer) $column : null
                    ));
                }
            }

            if ($meaning = (string) $trans->attributes()->extradata) {
                if (0 === strpos($meaning, 'Meaning: ')) {
                    $meaning = substr($meaning, 9);
                }

                $message->setMeaning($meaning);
            }

            if (!($state = (string) $trans->target->attributes()->state) || 'new' !== $state) {
                $message->setNew(false);
            }

        }

        return $catalogue;
    }
}
