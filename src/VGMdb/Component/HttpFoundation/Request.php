<?php

namespace VGMdb\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Request as BaseRequest;

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

    /**
     * Gets the subdomain from the host.
     */
    public function getSubdomain()
    {
        $host = $this->getHost();
        $parts = explode('.', $host);

        if (count($parts) <= 2) {
            return null;
        }

        array_pop($parts);
        array_pop($parts);

        if (in_array(end($parts), array('local', 'integration', 'staging'))) {
            array_pop($parts);
        }

        return implode('.', $parts);
    }
}
