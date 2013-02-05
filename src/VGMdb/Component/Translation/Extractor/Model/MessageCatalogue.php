<?php

/*
 * This code was originally part of JMSTranslationBundle.
 *
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 */

namespace VGMdb\Component\Translation\Extractor\Model;

/**
 * Represents a collection of _extracted_ messages.
 *
 * A catalogue may consist of multiple domains. Each message belongs to
 * a specific domain, and the ID of the message is uniquely identifying the
 * message in its domain, but _not_ across domains.
 *
 * This catalogue is only used for extraction, for translation at run-time
 * we still use the optimized catalogue from the Translation component.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class MessageCatalogue
{
    private $locale;
    private $domains = array();

    /**
     * @param $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param Message $message
     */
    public function add(Message $message)
    {
        $this
            ->getOrCreateDomain($message->getDomain())
            ->add($message)
        ;
    }

    /**
     * @param Message $message
     */
    public function set(Message $message)
    {
        $this
            ->getOrCreateDomain($message->getDomain())
            ->set($message)
        ;
    }

    /**
     * @param $id
     * @param $domain
     * @throws \InvalidArgumentException
     * @return Message
     */
    public function get($id, $domain = 'messages')
    {
        return $this->getDomain($domain)->get($id);
    }

    /**
     * @param Message $message
     * @return Boolean
     */
    public function has(Message $message)
    {
        if (!$this->hasDomain($message->getDomain())) {
            return false;
        }

        return $this->getDomain($message->getDomain())->has($message->getId());
    }

    /**
     * @param MessageCatalogue $catalogue
     */
    public function merge(MessageCatalogue $catalogue)
    {
        foreach ($catalogue->getDomains() as $name => $domainCatalogue) {
            $this->getOrCreateDomain($name)->merge($domainCatalogue);
        }
    }

    /**
     * @param string $domain
     * @return Boolean
     */
    public function hasDomain($domain)
    {
        return isset($this->domains[$domain]);
    }

    /**
     * @param string $domain
     * @throws \InvalidArgumentException
     * @return MessageCollection
     */
    public function getDomain($domain)
    {
        if (!$this->hasDomain($domain)) {
            throw new \InvalidArgumentException(sprintf('There is no domain with name "%s".', $domain));
        }

        return $this->domains[$domain];
    }

    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param string $domain
     * @return MessageCollection
     */
    private function getOrCreateDomain($domain)
    {
        if (!$this->hasDomain($domain)) {
            $this->domains[$domain] = new MessageCollection($this);
        }

        return $this->domains[$domain];
    }
}
