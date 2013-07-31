<?php

namespace VGMdb\Component\HttpFoundation;

use VGMdb\Component\HttpFoundation\Util\XmlSerializable;
use VGMdb\Component\View\ViewInterface;
use VGMdb\Component\View\View;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request as BaseRequest;

/**
 * Representation of a HTTP response in XML format.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class XmlResponse extends Response
{
    protected $data;
    protected $serializer;

    /**
     * Constructor.
     *
     * @param mixed   $data    The response data
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     */
    public function __construct($data = '', $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        $this->setData($data);
    }

    /**
     * {@inheritDoc}
     */
    public static function create($data = '', $status = 200, $headers = array())
    {
        return new static($data, $status, $headers);
    }

    /**
     * Sets the object serializer.
     *
     * @param Serializer $serializer
     *
     * @return XmlResponse
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(BaseRequest $request)
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
        if (!$data instanceof ViewInterface) {
            $data = new View(null, $data);
        }

        $this->data = $data;
        $this->setContent('');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function sendContent()
    {
        $data = $this->data;

        if ($data instanceof \ArrayObject) {
            $data = $data->getArrayCopy(true);
        }

        if (!is_array($data)) {
            $data = array('content' => $data);
        }

        if ($this->serializer) {
            $data = $this->serializer->serialize($data, 'xml');
        } else {
            $data = self::xmlEncode($data);
        }

        echo $data;

        return $this;
    }

    /**
     * Sets the data to be sent as xml.
     *
     * @param mixed $data
     *
     * @return string
     */
    public static function xmlEncode($data, $domElement = null, $domDocument = null, $indexOverride = null)
    {
        if (is_null($domDocument)) {
            $domDocument = new \DOMDocument();
            $domDocument->formatOutput = true;
            self::xmlEncode($data, $domDocument, $domDocument);

            return $domDocument->saveXML();
        }

        if ($data instanceof XmlSerializable) {
            $data = $data->xmlSerialize();
        }

        if (is_array($data) || $data instanceof \ArrayAccess) {
            foreach ($data as $index => $element){
                if (is_int($index)) {
                    if ($index === 0) {
                        $node = $domElement;
                    } else {
                        $node = $domDocument->createElement($domElement->tagName);
                        $domElement->parentNode->appendChild($node);
                    }
                    $singular = null;
                } else {
                    if ($indexOverride) {
                        $plural = $domDocument->createElement($indexOverride);
                        $plural->setAttribute('name', $index);
                    } else {
                        $plural = $domDocument->createElement($index);
                    }
                    $domElement->appendChild($plural);
                    $node = $plural;
                    $singular = null;
                    if (rtrim($index, 's') !== $index) {
                        $singular = rtrim($index, 's');
                    }
                }
                self::xmlEncode($element, $node, $domDocument, $singular);
            }
        } else {
            $data = (string) $data;
            if (strpos($data, '[') !== false ||
                strpos($data, ']') !== false ||
                strpos($data, '<') !== false ||
                strpos($data, '>') !== false ||
                strpos($data, '&') !== false
            ) {
                $domElement->appendChild($domDocument->createCDATASection($data));
            } else {
                $domElement->appendChild($domDocument->createTextNode($data));
            }
        }
    }
}
