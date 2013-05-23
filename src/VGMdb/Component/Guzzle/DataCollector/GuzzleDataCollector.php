<?php

namespace VGMdb\Component\Guzzle\DataCollector;

use Guzzle\Log\ArrayLogAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data from Guzzle requests.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class GuzzleDataCollector extends DataCollector
{
    protected $logAdapter;

    public function __construct(ArrayLogAdapter $logAdapter)
    {
        $this->logAdapter = $logAdapter;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        foreach ($this->logAdapter->getLogs() as $log) {
            $datum['message'] = $log['message'];
            $datum['request'] = array(
                'Request' => trim($log['extras']['request']->getMethod() . ' '
                             . $log['extras']['request']->getResource()) . ' '
                             . strtoupper(str_replace('https', 'http', $log['extras']['request']->getScheme()))
                             . '/' . $log['extras']['request']->getProtocolVersion()
            ) + $log['extras']['request']->getHeaders()->toArray();
            $datum['requestBody'] = (string) $log['extras']['request']->getBody();
            $datum['response'] = array(
                'Status' => 'HTTP/1.1 ' . $log['extras']['response']->getStatusCode() . ' '
                            . $log['extras']['response']->getReasonPhrase()
            ) + $log['extras']['response']->getHeaders()->toArray();
            $datum['body'] = ($log['extras']['response']->getBody()->getSize() < 2097152)
                ? $log['extras']['response']->getBody(true)
                : 'Responses over 2MB not logged.';
            $datum['is_error'] = $log['extras']['response']->isError();
            $this->data['requests'][] = $datum;
        }
    }

    /**
     * Checks if any requests were recorded.
     *
     * @return Boolean
     */
    public function hasRequests()
    {
        return isset($this->data['requests']);
    }

    public function getRequests()
    {
        return $this->data['requests'];
    }

    public function getName()
    {
        return 'guzzle';
    }
}
