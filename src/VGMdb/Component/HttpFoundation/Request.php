<?php

namespace VGMdb\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Request as BaseRequest;

/**
 * Representation of a HTTP request.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Request extends BaseRequest
{
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
