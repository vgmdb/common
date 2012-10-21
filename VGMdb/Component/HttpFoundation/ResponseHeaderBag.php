<?php

namespace VGMdb\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\ResponseHeaderBag as BaseResponseHeaderBag;

/**
 * @brief       A container for Response HTTP headers, extended with the ability to preserve capitalization.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class ResponseHeaderBag extends BaseResponseHeaderBag
{
    protected $headerNames;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $headers = array())
    {
        $this->headerNames = array();

        parent::__construct($headers);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $cookies = '';
        foreach ($this->getCookies() as $cookie) {
            $cookies .= 'Set-Cookie: '.$cookie."\r\n";
        }

        if (!$this->headers) {
            return $cookies;
        }

        $max = max(array_map('strlen', array_keys($this->headers))) + 1;
        $content = '';
        ksort($this->headers);
        foreach ($this->headers as $name => $values) {
            if (array_key_exists($name, $this->headerNames)) {
                $name = $this->headerNames[$name];
            }
            foreach ($values as $value) {
                $content .= sprintf("%-{$max}s %s\r\n", $name.':', $value);
            }
        }

        return $content.$cookies;
    }

    /**
     * Returns the headers.
     *
     * @param Boolean $originalCase Whether to return the header names in their original case.
     *
     * @return array An array of headers
     *
     * @api
     */
    public function all($originalCase = false)
    {
        if ($originalCase) {
            $headers = array();
            foreach ($this->headers as $name => $values) {
                if (array_key_exists($name, $this->headerNames)) {
                    $name = $this->headerNames[$name];
                }
                $headers[$name] = $values;
            }

            return $headers;
        }

        return $this->headers;
    }

    /**
     * {@inheritDoc}
     */
    public function replace(array $headers = array())
    {
        $this->headerNames = array();

        parent::replace($headers);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $values, $replace = true)
    {
        if ($key === 'cache-control') {
            $key = 'Cache-Control';
        }

        parent::set($key, $values, $replace);

        $original = $key;
        $key = strtr(strtolower($key), '_', '-');

        $this->headerNames[$key] = $original;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($key)
    {
        parent::remove($key);

        $key = strtr(strtolower($key), '_', '-');

        unset($this->headerNames[$key]);
    }
}
