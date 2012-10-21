<?php

namespace VGMdb\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;

/**
 * @brief       Representation of a static file.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class FileResponse extends Response
{
    /**
     * X-Sendfile (Apache)
     *
     * @var int
     */
    const X_SENDFILE = 1;

    /**
     * X-Accel-Redirect (Nginx)
     *
     * @var int
     */
    const X_ACCEL_REDIRECT = 2;

    protected $file;
    protected $method;
    protected $save_as;

    /**
     * Constructor.
     *
     * @param string  $file    The full file path
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     */
    public function __construct($file, $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        $this->setFile($file);
    }

    /**
     * {@inheritDoc}
     */
    public static function create($file, $status = 200, $headers = array())
    {
        return new static($file, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(Request $request)
    {
        $guesser = MimeTypeGuesser::getInstance();
        $guesser->register(new FileInfoMimeTypeGuesser());
        $mimetype = $guesser->guess($this->file);
        if (!$mimetype) {
            $mimetype = 'text/plain';
        }
        $this->headers->set('Content-Type', $mimetype);

        if ($this->method ===  self::X_SENDFILE) {
            $this->headers->set('X-Sendfile', $this->file);
        } elseif ($this->method ===  self::X_ACCEL_REDIRECT) {
            $this->headers->set('X-Accel-Redirect', $this->file);
        } else {
            $this->setContent(file_get_contents($this->file));
        }

        if ($this->save_as) {
            $this->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $this->save_as));
        }

        return parent::prepare($request);
    }

    /**
     * Sets the file to be delivered.
     *
     * @param string $file The full file path
     * @return FileResponse
     */
    public function setFile($file)
    {
        if (empty($file)) {
            throw new \InvalidArgumentException('Filename not specified.');
        }
        $this->file = $file;

        return $this;
    }

    /**
     * Sets the method of delivery.
     *
     * @param integer $method The delivery method
     * @return FileResponse
     */
    public function setMethod($method = null)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Forces the file to be downloaded.
     *
     * @param string $name The download filename
     * @return FileResponse
     */
    public function setSaveAs($name = null)
    {
        $this->save_as = $name;

        return $this;
    }
}