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

        return implode('.', $parts);
    }

   /**
     * Splits an Accept-* HTTP header.
     * This function has been extended to optionally return custom param values.
     *
     * @param string $header  Header to split
     * @param string $param   Custom parameter value to extract
     * @param string $default Default value if parameter is not set
     * @return array Array indexed by the values of the Accept-* header in preferred order
     */
    public function splitHttpAcceptHeader($header, $param = 'q', $default = 1)
    {
        if (!$header) {
            return array();
        }

        $qvalues = $pvalues = array();
        $qgroups = $pgroups = array();
        foreach (array_filter(explode(',', $header)) as $value) {
            // Obtain custom parameter
            if (preg_match('/;\s*('.preg_quote($param).'=.*$)/', $value, $match)) {
                $p = substr(trim($match[1]), strlen($param) + 1);
            } else {
                $p = $default;
            }
            // Cut off any q-value that might come after a semi-colon
            if (preg_match('/;\s*(q=.*$)/', $value, $match)) {
                $q = substr(trim($match[1]), 2);
                $value = trim(substr($value, 0, -strlen($match[0])));
            } else {
                $q = 1;
            }

            $pgroups[trim($value)] = (float) $p;
            $qgroups[$q][] = $value;
        }

        krsort($qgroups);

        foreach ($qgroups as $q => $items) {
            $q = (float) $q;

            if (0 < $q) {
                foreach ($items as $value) {
                    $qvalues[trim($value)] = $q;
                    $pvalues[trim($value)] = $pgroups[trim($value)];
                }
            }
        }

        return $pvalues;
    }

    /**
     * Initializes HTTP request formats.
     */
    protected static function initializeFormats()
    {
        static::$formats = array(
            'html'   => array('text/html', 'application/xhtml+xml'),
            'txt'    => array('text/plain'),
            'js'     => array('application/javascript', 'application/x-javascript', 'text/javascript'),
            'css'    => array('text/css'),
            'json'   => array('application/json', 'application/x-json'),
            'xml'    => array('text/xml', 'application/xml', 'application/x-xml'),
            'rdf'    => array('application/rdf+xml'),
            'atom'   => array('application/atom+xml'),
            'rss'    => array('application/rss+xml'),
            'gif'    => array('image/gif'),
            'thrift' => array('application/x-thrift')
        );
    }
}
