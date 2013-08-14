<?php

namespace VGMdb\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Representation of a HTTP request, with additional logic for versioning.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Request extends BaseRequest
{
    /**
     * @var array
     */
    protected static $additionalFormats = array();

    /**
     * {@inheritDoc}
     */
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);

        foreach (static::$additionalFormats as $format => $mimeTypes) {
            $this->setFormat($format, $mimeTypes);
        }
    }

    /**
     * Copied from Symfony 2.3. REMOVE THIS WHEN UPGRADING
     */
    public function getClientIp()
    {
        $ip = $this->server->get('REMOTE_ADDR');

        if (!self::$trustProxy) {
            return $ip;
        }

        if (!self::$trustedHeaders[self::HEADER_CLIENT_IP] || !$this->headers->has(self::$trustedHeaders[self::HEADER_CLIENT_IP])) {
            return $ip;
        }

        $clientIps = array_map('trim', explode(',', $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_IP])));
        $clientIps[] = $ip;

        $trustedProxies = self::$trustProxy && !self::$trustedProxies ? array($ip) : self::$trustedProxies;
        $ip = $clientIps[0];

        foreach ($clientIps as $key => $clientIp) {
            foreach ($trustedProxies as $trustedProxy) {
                if (IpUtils::checkIp($clientIp, $trustedProxy)) {
                    unset($clientIps[$key]);

                    continue 2;
                }
            }
        }

        return $clientIps ? array_pop($clientIps) : $ip;
    }

    /**
     * Associates a format with mime types outside of the request scope. It will be added in the constructor.
     *
     * @param string       $format    The format
     * @param string|array $mimeTypes The associated mime types
     */
    public static function addFormat($format, $mimeTypes)
    {
        static::$additionalFormats[$format] = $mimeTypes;
    }

    /**
     * Gets the request version.
     *
     * @return string The request version
     */
    public function getRequestVersion()
    {
        return $this->attributes->get('_version');
    }

    /**
     * Sets the request version.
     *
     * @param string $version The request version.
     */
    public function setRequestVersion($version)
    {
        $this->attributes->set('_version', $version);
    }

    /**
     * Overrides the requested URI.
     *
     * @param string $uri The raw URI.
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
    }
}
