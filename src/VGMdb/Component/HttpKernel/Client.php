<?php

namespace VGMdb\Component\HttpKernel;

use VGMdb\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Client as BaseClient;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\BrowserKit\Request as DomRequest;

/**
 * Overrides the Request object with our own implementation.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Client extends BaseClient
{
    /**
     * Makes a request.
     *
     * @param Request $request A Request instance
     *
     * @return Response A Response instance
     */
    protected function doRequest($request)
    {
        if (!$request instanceof Request) {
            $request = Request::create($request->getUri(), $request->getMethod(), $request->getParameters(), $request->getCookies(), $request->getFiles(), $request->getServer(), $request->getContent());
        }

        return parent::doRequest($request);
    }

    /**
     * Converts the BrowserKit request to a HttpKernel request.
     *
     * @param DomRequest $request A Request instance
     *
     * @return Request A Request instance
     */
    protected function filterRequest(DomRequest $request)
    {
        $httpRequest = Request::create($request->getUri(), $request->getMethod(), $request->getParameters(), $request->getCookies(), $request->getFiles(), $request->getServer(), $request->getContent());

        $httpRequest->files->replace($this->filterFiles($httpRequest->files->all()));

        return $httpRequest;
    }
}
