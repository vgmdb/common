<?php

namespace VGMdb\Component\Thrift;

use VGMdb\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TPhpStream;
use Thrift\Transport\TBufferedTransport;

/**
 * Representation of a HTTP response using the Thrift binary transport.
 *
 * Heavily based on StreamedResponse.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ThriftResponse extends Response
{
    protected $processor;
    protected $transport;
    protected $sent;

    /**
     * Constructor.
     *
     * @param mixed   $processor A valid Thrift processor or callback
     * @param integer $status    The response status code
     * @param array   $headers   An array of response headers
     */
    public function __construct($processor = null, $status = 200, $headers = array())
    {
        parent::__construct(null, $status, $headers);

        if (null !== $processor) {
            $this->setProcessor($processor);
        }
        $this->sent = false;
    }

    /**
     * {@inheritDoc}
     */
    public static function create($processor = null, $status = 200, $headers = array())
    {
        return new static($processor, $status, $headers);
    }

    /**
     * Sets the Thrift processor associated with this Response.
     *
     * @param mixed $processor
     */
    public function setProcessor($processor)
    {
        if (!$processor instanceof \Closure && !method_exists($processor, 'process')) {
            throw new \LogicException('Invalid Thrift processor.');
        }
        $this->processor = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(BaseRequest $request)
    {
        $this->headers->set('Content-Type', 'application/x-thrift');
        $this->headers->set('Vary', 'Accept');

        // Ideally only the Vary header would be necessary
        // However Chrome has aggressive caching which ignores the Vary header
        $this->headers->set('Cache-Control', 'no-cache, no-store');

        return parent::prepare($request);
    }

    /**
     * {@inheritdoc}
     *
     * This method only sends the content once.
     */
    public function sendContent()
    {
        if ($this->sent) {
            return;
        }

        $this->sent = true;

        if (null === $this->processor) {
            throw new \LogicException('Missing Thrift processor.');
        }

        $transport = new TBufferedTransport(new TPhpStream(TPhpStream::MODE_R | TPhpStream::MODE_W));
        $protocol = new TBinaryProtocol($transport, true, true);

        $transport->open();

        if (method_exists($this->processor, 'process')) {
            $this->processor->process($protocol, $protocol);
        } else {
            call_user_func($this->processor, $protocol, $protocol);
        }

        $transport->close();
    }

   /**
     * {@inheritdoc}
     *
     * @throws \LogicException when the content is not null
     */
    public function setContent($content)
    {
        if (null !== $content) {
            throw new \LogicException('The content cannot be set on a ThriftResponse instance.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return false
     */
    public function getContent()
    {
        return false;
    }
}
