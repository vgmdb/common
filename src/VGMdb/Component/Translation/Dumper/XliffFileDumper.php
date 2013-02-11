<?php

/*
 * This code was originally part of JMSTranslationBundle.
 *
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 */

namespace VGMdb\Component\Translation\Dumper;

use VGMdb\Component\Translation\Extractor\Model\FileSource;
use VGMdb\Component\Translation\Extractor\Model\Message;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Dumper\XliffFileDumper as BaseXliffFileDumper;

/**
 * XLIFF dumper.
 *
 * This dumper uses version 1.2 of the specification.
 *
 * @see http://docs.oasis-open.org/xliff/v1.2/os/xliff-core.html
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class XliffFileDumper extends BaseXliffFileDumper
{
    const VERSION = '1.1.0-DEV';

    private $sourceLanguage = 'en';
    private $addDate = true;

    /**
     * @param $bool
     */
    public function setAddDate($bool)
    {
        $this->addDate = (Boolean) $bool;
    }

    /**
     * @param $lang
     */
    public function setSourceLanguage($lang)
    {
        $this->sourceLanguage = $lang;
    }

    /**
     * {@inheritDoc}
     */
    protected function format(MessageCatalogue $catalogue, $domain)
    {
        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->formatOutput = true;

        $doc->appendChild($root = $doc->createElement('xliff'));
        $root->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:1.2');
        $root->setAttribute('xmlns:jms', 'urn:jms:translation');
        $root->setAttribute('version', '1.2');

        $root->appendChild($file = $doc->createElement('file'));

        if ($this->addDate) {
            $date = new \DateTime();
            $file->setAttribute('date', $date->format('Y-m-d\TH:i:s\Z'));
        }

        $file->setAttribute('source-language', $this->sourceLanguage);
        $file->setAttribute('target-language', $catalogue->getLocale());
        $file->setAttribute('datatype', 'plaintext');
        $file->setAttribute('original', 'file.ext');

        $file->appendChild($header = $doc->createElement('header'));

        $header->appendChild($tool = $doc->createElement('tool'));
        $tool->setAttribute('tool-id', 'JMSTranslationBundle');
        $tool->setAttribute('tool-name', 'JMSTranslationBundle');
        $tool->setAttribute('tool-version', static::VERSION);


        $header->appendChild($note = $doc->createElement('note'));
        $note->appendChild($doc->createTextNode('The source node in most cases contains the sample message as written by the developer. If it looks like a dot-delimited string such as "form.label.firstname", then the developer has not provided a default message.'));

        $file->appendChild($body = $doc->createElement('body'));

        foreach ($catalogue->all($domain) as $id => $message) {
            $body->appendChild($unit = $doc->createElement('trans-unit'));

            if (is_object($message) && $message instanceof Message) {
                $unit->setAttribute('id', $message->getHash());
                $unit->setAttribute('resname', $message->getId());
                $unit->appendChild($source = $doc->createElement('source'));
                if (preg_match('/[<>&]/', $message->getSourceString())) {
                    $source->appendChild($doc->createCDATASection($message->getSourceString()));
                } else {
                    $source->appendChild($doc->createTextNode($message->getSourceString()));
                }

                $unit->appendChild($target = $doc->createElement('target'));
                if (preg_match('/[<>&]/', $message->getLocaleString())) {
                    $target->appendChild($doc->createCDATASection($message->getLocaleString()));
                } else {
                    $target->appendChild($doc->createTextNode($message->getLocaleString()));
                }

                if ($message->isNew()) {
                    $target->setAttribute('state', 'new');
                }

                if ($sources = $message->getSources()) {
                    foreach ($sources as $source) {
                        if ($source instanceof FileSource) {
                            $unit->appendChild($refFile = $doc->createElement('jms:reference-file', $source->getPath()));

                            if ($source->getLine()) {
                                $refFile->setAttribute('line', $source->getLine());
                            }

                            if ($source->getColumn()) {
                                $refFile->setAttribute('column', $source->getColumn());
                            }

                            continue;
                        }

                        $unit->appendChild($doc->createElementNS('jms:reference', (string) $source));
                    }
                }

                if ($meaning = $message->getMeaning()) {
                    $unit->setAttribute('extradata', 'Meaning: '.$meaning);
                }
            } else {
                $unit->setAttribute('id', hash('sha1', $id));
                $unit->setAttribute('resname', $id);
                $unit->appendChild($source = $doc->createElement('source'));
                if (preg_match('/[<>&]/', $id)) {
                    $source->appendChild($doc->createCDATASection($id));
                } else {
                    $source->appendChild($doc->createTextNode($id));
                }

                $unit->appendChild($target = $doc->createElement('target'));
                if (preg_match('/[<>&]/', $message)) {
                    $target->appendChild($doc->createCDATASection($message));
                } else {
                    $target->appendChild($doc->createTextNode($message));
                }
            }
        }

        return $doc->saveXML();
    }
}
