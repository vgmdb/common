<?php

namespace VGMdb\Component\Guzzle\Plugin\Signature;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Url;

/**
 * Abstract signature class that can be used when implementing new concrete strategies.
 *
 * Copyright 2010-2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 */
abstract class AbstractSignature implements SignatureInterface
{
    const DATE_ISO8601    = 'Ymd\THis\Z';
    const DATE_ISO8601_S3 = 'Y-m-d\TH:i:s\Z';
    const DATE_RFC1123    = 'D, d M Y H:i:s \G\M\T';
    const DATE_RFC2822    = \DateTime::RFC2822;
    const DATE_SHORT      = 'Ymd';

    /**
     * @var int Timestamp
     */
    private $timestamp;

    /**
     * @var string Prefix
     */
    protected $prefix = '';

    /**
     * @var int Version
     */
    protected $version = 0;

    /**
     * Prefixes header names
     *
     * @param string $header
     *
     * @return string
     */
    public function prefixHeader($header)
    {
        return 'x-' . $this->prefix . '-' . $header;
    }

    /**
     * Prefixes variables
     *
     * @return string
     */
    public function prefixNamespace()
    {
        return $this->prefix . $this->version;
    }

    /**
     * Get the canonicalized query string for a request
     *
     * @param RequestInterface $request
     * @return string
     */
    protected function getCanonicalizedQueryString(RequestInterface $request)
    {
        $queryParams = $request->getQuery()->getAll();
        unset($queryParams[$this->prefixHeader('Signature')]);
        if (empty($queryParams)) {
            return '';
        }

        $qs = '';
        ksort($queryParams);
        foreach ($queryParams as $key => $values) {
            if (is_array($values)) {
                sort($values);
            } elseif (!$values) {
                $values = array('');
            }

            foreach ((array) $values as $value) {
                $qs .= rawurlencode($key) . '=' . rawurlencode($value) . '&';
            }
        }

        return substr($qs, 0, -1);
    }

    /**
     * Provides the timestamp used for the class
     *
     * @param bool $refresh Set to TRUE to refresh the cached timestamp
     *
     * @return int
     */
    protected function getTimestamp($refresh = false)
    {
        if (!$this->timestamp || $refresh) {
            $this->timestamp = time();
        }

        return $this->timestamp;
    }

    /**
     * Get a date for one of the parts of the requests
     *
     * @param string $format Date format
     *
     * @return string
     */
    protected function getDateTime($format)
    {
        return gmdate($format, $this->getTimestamp());
    }

    /**
     * Parse the region name from a URL
     *
     * @param Url $url HTTP URL
     *
     * @return string
     */
    protected function parseRegionName(Url $url)
    {
        // Unimplemented, just return default for now
        return static::DEFAULT_REGION;
    }

    /**
     * Parse the service name from a URL
     *
     * @param Url $url HTTP URL
     *
     * @return string Returns a service name (or empty string)
     */
    protected function parseServiceName(Url $url)
    {
        // Unimplemented, just return default for now
        return static::DEFAULT_SERVICE;
    }
}
