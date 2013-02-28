<?php

namespace VGMdb\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Endroid\QrCode\QrCode;

/**
 * Returns a QR Code image.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class QrCodeResponse extends Response
{
    protected $qrCode;

    /**
     * Constructor.
     *
     * @param string  $url     The URL
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     */
    public function __construct($url = '', $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        $this->qrCode = new QrCode();
        $this->headers->set('Content-Type', 'image/png');
        $this->setUrl($url);
    }

    /**
     * Updates the content and headers with the image content.
     *
     * @param string $url The URL
     *
     * @return QrCodeResponse
     */
    public function setUrl($url)
    {
        $this->qrCode->setText($url);
        $this->qrCode->setSize(300);
        $data = $this->qrCode->get();

        return $this->setContent($data);
    }
}
