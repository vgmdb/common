<?php

namespace VGMdb\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Request as BaseRequest;

/**
 * Representation of a plaintext response.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TextResponse extends Response
{
    /**
     * Constructor.
     *
     * @param mixed   $data    The response data
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     */
    public function __construct($data = '', $status = 200, $headers = array())
    {
        if ($data instanceof \ArrayObject) {
            $data = $data->getArrayCopy(true);
        }

        if (is_array($data)) {
            $data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        parent::__construct($data, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(BaseRequest $request)
    {
        $ret = parent::prepare($request);

        $this->headers->set('Content-Type', 'text/plain');
        $this->headers->set('Vary', 'Accept');

        // Ideally only the Vary header would be necessary
        // However Chrome has aggressive caching which ignores the Vary header
        $this->headers->set('Cache-Control', 'no-cache, no-store');

        return $ret;
    }
}
