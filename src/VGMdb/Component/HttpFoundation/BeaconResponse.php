<?php

namespace VGMdb\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Request as BaseRequest;

/**
 * Representation of a beacon, usually a 1x1 transparent GIF.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class BeaconResponse extends Response
{
    /**
     * Constructor.
     *
     * @param string  $type    The beacon filetype
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     */
    public function __construct($type = 'gif', $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        $this->setType($type);
    }

    /**
     * {@inheritDoc}
     */
    public static function create($type = 'gif', $status = 200, $headers = array())
    {
        return new static($type, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(BaseRequest $request)
    {
        $this->headers->set('Cache-Control', 'no-cache, no-store');

        return parent::prepare($request);
    }

    /**
     * Updates the content and headers according to the beacon type.
     *
     * @param string $type The beacon filetype
     * @return BeaconResponse
     */
    protected function setType($type)
    {
        if ($type !== 'gif') {
            throw new \InvalidArgumentException('Only GIF is supported at present.');
        }
        $this->headers->set('Content-Type', 'image/gif');
        $data = base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');

        return $this->setContent($data);
    }
}
