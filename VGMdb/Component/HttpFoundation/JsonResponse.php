<?php

namespace VGMdb\Component\HttpFoundation;

use VGMdb\Component\View\View;
use Symfony\Component\HttpFoundation\Request as BaseRequest;

/**
 * @brief       Representation of a HTTP response in JSON format.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class JsonResponse extends Response
{
    protected $data;
    protected $callback;

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
     * Sets the JSONP callback.
     *
     * @param string $callback
     *
     * @return JsonResponse
     */
    public function setCallback($callback = null)
    {
        if (null !== $callback) {
            // taken from http://www.geekality.net/2011/08/03/valid-javascript-identifier/
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';
            $parts = explode('.', $callback);
            foreach ($parts as $part) {
                if (!preg_match($pattern, $part)) {
                    throw new \InvalidArgumentException('The callback name is not valid.');
                }
            }
        }

        $this->callback = $callback;

        return $this->update();
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(BaseRequest $request)
    {
        $this->headers->set('Vary', 'Accept');

        // Ideally only the Vary header would be necessary
        // However Chrome has aggressive caching which ignores the Vary header
        $this->headers->set('Cache-Control', 'no-cache, no-store');

        return parent::prepare($request);
    }

    /**
     * Sets the data to be sent as json.
     *
     * @param mixed $data
     *
     * @return JsonResponse
     */
    public function setData($data = array())
    {
        if (is_array($data)) {
            $data = View::create(null, $data);
        }

        $this->data = $data;

        return $this->update();
    }

    /**
     * Updates the content and headers according to the json data and callback.
     *
     * @return JsonResponse
     */
    protected function update()
    {
        if (null !== $this->callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript');
        }
        elseif (!$this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            // Only set the header when there is none or when it equals 'text/javascript'
            // (from a previous update with callback) in order to not overwrite a custom definition.
            $this->headers->set('Content-Type', 'application/json');
        }

        return $this->setContent($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function sendContent()
    {
        $data = $this->content;

        if ($data instanceof \ArrayObject) {
            $data = $data->getArrayCopy(true);
        }

        // Encode <, >, ', &, and " for RFC4627-compliant JSON, which may also be embedded into HTML.
        $data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        if (null !== $this->callback) {
            $data = sprintf('%s(%s);', $this->callback, $data);
        }

        echo $data;

        return $this;
    }
}
