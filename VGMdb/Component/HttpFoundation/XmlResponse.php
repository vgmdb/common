<?php

namespace VGMdb\Component\HttpFoundation;

use VGMdb\Component\View\View;

/**
 * @brief       Representation of a HTTP response in XML format.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class XmlResponse extends Response
{
    protected $data;

    /**
     * Constructor.
     *
     * @param mixed   $data    The response data
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     */
    public function __construct($data, $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        $this->setData($data);
    }

    /**
     * {@inheritDoc}
     */
    public static function create($data, $status = 200, $headers = array())
    {
        return new static($data, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(Request $request)
    {
        $this->headers->set('Content-Type', 'application/xml');
        $this->headers->set('Vary', 'Accept');

        // Ideally only the Vary header would be necessary
        // However Chrome has aggressive caching which ignores the Vary header
        $this->headers->set('Cache-Control', 'no-cache, no-store');

        return parent::prepare($request);
    }

    /**
     * Sets the data to be sent as xml.
     *
     * @param mixed $data
     *
     * @return XmlResponse
     */
    public function setData($data = array())
    {
        if (is_array($data)) {
            $data = View::create(null, $data);
        }

        $this->data = $data;
        $this->setContent($data);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function sendContent()
    {
        $data = array('document' => $this->content->getArrayCopy());

        echo self::xmlEncode($data);

        return $this;
    }

    /**
     * Sets the data to be sent as xml.
     *
     * @param mixed $data
     *
     * @return string
     */
    static public function xmlEncode($data, $domElement = null, $domDocument = null)
    {
        if (is_null($domDocument)) {
            $domDocument = new \DOMDocument();
            $domDocument->formatOutput = true;
            self::xmlEncode($data, $domDocument, $domDocument);

            return $domDocument->saveXML();

        } else {
            if (is_array($data) || $data instanceof \ArrayAccess) {
                foreach ($data as $index => $element){
                    if (is_int($index)) {
                        if ($index === 0) {
                            $node = $domElement;
                        } else {
                            $node = $domDocument->createElement($domElement->tagName);
                            $domElement->parentNode->appendChild($node);
                        }
                    } else {
                        $plural = $domDocument->createElement($index);
                        $domElement->appendChild($plural);
                        $node = $plural;
                        if (rtrim($index, 's') !== $index) {
                            $singular = $domDocument->createElement(rtrim($index, 's'));
                            $plural->appendChild($singular);
                            $node = $singular;
                        }
                    }
                    self::xmlEncode($element, $node, $domDocument);
                }
            } else {
                $domElement->appendChild($domDocument->createTextNode((string) $data));
            }
        }
    }
}
