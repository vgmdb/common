<?php

namespace VGMdb\Component\HttpFoundation;

use VGMdb\ViewInterface;
use VGMdb\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * @brief       Representation of a HTTP response.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class Response extends BaseResponse
{
    /**
     * {@inheritDoc}
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->headers = new ResponseHeaderBag($headers);
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');
        if (!$this->headers->has('Date')) {
            $this->setDate(new \DateTime(null, new \DateTimeZone('UTC')));
        }
    }

    /**
     * Sets the response content. Does NOT convert ViewInterface objects.
     *
     * @param mixed $content
     * @return Response
     */
    public function setContent($content)
    {
        if ($content instanceof ViewInterface) {
            $this->content = $content;

            return $this;
        }

        return parent::setContent($content);
    }

    /**
     * Sends HTTP headers.
     *
     * @return Response
     */
    public function sendHeaders()
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return $this;
        }

        // status
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText));

        // headers
        foreach ($this->headers->all(true) as $name => $values) {
            foreach ($values as $value) {
                header($name.': '.$value, false);
            }
        }

        // cookies
        foreach ($this->headers->getCookies() as $cookie) {
            setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }

        return $this;
    }

    /**
     * Sends content for the current web response.
     *
     * @return Response
     */
    public function sendContent()
    {
        echo $this->content;

        return $this;
    }
}