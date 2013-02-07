<?php

/*
 * This code was originally part of JMSTranslationBundle.
 *
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 */

namespace VGMdb\Component\Translation\Extractor\Model;

/**
 * Represents an _extracted_ message.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Message
{
    /** Unique ID of this message (same across the same domain) */
    private $id;

    private $new = true;

    private $domain;

    private $localeString;

    /** Additional information about the intended meaning */
    private $meaning;

    /** The description/sample for translators */
    private $desc;

    /** The sources where this message occurs */
    private $sources = array();

    /**
     * @static
     * @param $id
     * @param string $domain
     * @return Message
     */
    public static function forThisFile($id, $domain = 'messages')
    {
        $message = new static($id, $domain);

        $trace = debug_backtrace(false);
        if (isset($trace[0]['file'])) {
            $message->addSource(new FileSource($trace[0]['file']));
        }

        return $message;
    }

    /**
     * @static
     * @param $id
     * @param string $domain
     * @return Message
     */
    public static function create($id, $domain = 'messages')
    {
        return new static($id, $domain);
    }

    /**
     * @param $id
     * @param string $domain
     */
    public function __construct($id, $domain = 'messages')
    {
        $this->id = $id;
        $this->domain = $domain;
    }

    /**
     * @param SourceInterface $source
     * @return Message
     */
    public function addSource(SourceInterface $source)
    {
        if ($this->hasSource($source)) {
            return $this;
        }

        $this->sources[] = $source;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function isNew()
    {
        return $this->new;
    }

    public function getLocaleString()
    {
        return strlen($this->localeString) ? $this->localeString : (strlen($this->desc) ? $this->desc : $this->id);
    }

    /**
     * Returns the string from which to translate.
     *
     * This typically is the description, but we will fallback to the id
     * if that has not been given.
     *
     * @return string
     */
    public function getSourceString()
    {
        return $this->desc ?: $this->id;
    }

    public function getMeaning()
    {
        return $this->meaning;
    }

    public function getDesc()
    {
        return $this->desc;
    }

    public function getSources()
    {
        return $this->sources;
    }

    public function setMeaning($meaning)
    {
        $this->meaning = $meaning;

        return $this;
    }

    public function setNew($bool)
    {
        $this->new = (Boolean) $bool;

        return $this;
    }

    public function setDesc($desc)
    {
        $this->desc = $desc;

        return $this;
    }

    public function setLocaleString($str)
    {
        $this->localeString = $str;

        return $this;
    }

    /**
     * Merges an extracted message.
     *
     * Do not use this if you want to merge a message from an existing catalogue.
     * In these cases, use mergeExisting() instead.
     *
     * @param Message $message
     * @throws \RuntimeException
     */
    public function merge(Message $message)
    {
        if ($this->id !== $message->getId()) {
            throw new \RuntimeException(sprintf('You can only merge messages with the same id. Expected id "%s", but got "%s".', $this->id, $message->getId()));
        }

        if (null !== $meaning = $message->getMeaning()) {
            $this->meaning = $meaning;
        }

        if (null !== $desc = $message->getDesc()) {
            $this->desc = $desc;
        }

        foreach ($message->getSources() as $source) {
            $this->addSource($source);
        }

        $this->new = $message->isNew();
        if ($localeString = $message->getLocaleString()) {
            $this->localeString = $localeString;
        }
    }

    /**
     * Merges a message from an existing translation catalogue.
     *
     * Do not use this if you want to merge a message from an extracted catalogue.
     * In these cases, use merge() instead.
     *
     * @param Message $message
     * @throws \RuntimeException
     */
    public function mergeExisting(Message $message)
    {
        if ($this->id !== $message->getId()) {
            throw new \RuntimeException(sprintf('You can only merge messages with the same id. Expected id "%s", but got "%s".', $this->id, $message->getId()));
        }

        if (null !== $meaning = $message->getMeaning()) {
            $this->meaning = $meaning;
        }

        if (null !== $desc = $message->getDesc()) {
            $this->desc = $desc;
        }

        $this->new = $message->isNew();
        if ($localeString = $message->getLocaleString()) {
            $this->localeString = $localeString;
        }
    }

    public function hasSource(SourceInterface $source)
    {
        foreach ($this->sources as $cSource) {
            if ($cSource->equals($source)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Allows us to use this with existing message catalogues.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLocaleString();
    }
}
