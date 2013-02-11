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
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Message
{
    /** Unique ID of this message (same across the same domain) */
    private $id;

    /** Whether the message is new */
    private $new = true;

    /** Message domain **/
    private $domain;

    /** The localized string **/
    private $localeString;

    /** Additional information about the intended meaning */
    private $meaning;

    /** The description/sample for translators */
    private $desc;

    /** The sources where this message occurs */
    private $sources = array();

    /**
     * Create a new extraction message for the current file.
     *
     * @param $id
     * @param string $domain
     *
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
     * Create a new extraction message.
     *
     * @param $id
     * @param string $domain
     *
     * @return Message
     */
    public static function create($id, $domain = 'messages')
    {
        return new static($id, $domain);
    }

    /**
     * Constructor.
     *
     * @param $id
     * @param string $domain
     */
    public function __construct($id, $domain = 'messages')
    {
        $this->id = $id;
        $this->domain = $domain;
    }

    /**
     * Add a new extraction source.
     *
     * @param SourceInterface $source
     *
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

    /**
     * Get the hash of the message ID.
     *
     * @return string
     */
    public function getHash()
    {
        return hash('sha1', $this->id);
    }

    /**
     * Get the message ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the message domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Check whether the message is new.
     *
     * @return Boolean
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Get the localized string.
     *
     * @return string
     */
    public function getLocaleString()
    {
        return strlen($this->localeString) ? $this->localeString : (strlen($this->desc) ? $this->desc : $this->id);
    }

    /**
     * Returns the string from which to translate.
     *
     * This typically is the description, but we will fallback to the id if that has not been given.
     *
     * @return string
     */
    public function getSourceString()
    {
        return $this->desc ?: $this->id;
    }

    /**
     * Get the intended meaning of the string.
     *
     * @return string
     */
    public function getMeaning()
    {
        return $this->meaning;
    }

    /**
     * Get the description of the string.
     *
     * This is typically the untranslated string itself.
     *
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * Get the extraction sources for the string.
     *
     * @return array
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * Set the meaning of the string.
     *
     * @return Message
     */
    public function setMeaning($meaning)
    {
        $this->meaning = $meaning;

        return $this;
    }

    /**
     * Mark the message as new.
     *
     * @return Message
     */
    public function setNew($bool)
    {
        $this->new = (Boolean) $bool;

        return $this;
    }

    /**
     * Set the description of the string.
     *
     * @return Message
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;

        return $this;
    }

    /**
     * Set the localized translation of the string.
     *
     * @return Message
     */
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
     *
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
     *
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

    /**
     * Check whether the message has sources attached.
     *
     * @return Boolean
     */
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
