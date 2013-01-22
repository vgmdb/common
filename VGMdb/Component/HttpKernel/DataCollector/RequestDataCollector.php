<?php

namespace VGMdb\Component\HttpKernel\DataCollector;

use VGMdb\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector as BaseRequestDataCollector;

/**
 * RequestDataCollector, with the ability to track request context.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RequestDataCollector extends BaseRequestDataCollector
{
    protected $context;

    public function __construct(RequestContext $context)
    {
        $this->context = $context;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        parent::collect($request, $response, $exception);

        $this->data['request_context'] = array(
            'base_url'   => $this->context->getBaseUrl(),
            'path_info'  => $this->context->getPathInfo(),
            'method'     => $this->context->getMethod(),
            'host'       => $this->context->getHost(),
            'scheme'     => $this->context->getScheme(),
            'http_port'  => $this->context->getHttpPort(),
            'https_port' => $this->context->getHttpsPort(),
            'parameters' => $this->varToString($this->context->getParameters()),
            'format'     => $this->context->getFormat(),
            'version'    => $this->context->getVersion(),
            'locale'     => $this->context->getLocale(),
            'language'   => $this->context->getLanguage(),
            'region'     => $this->context->getRegion(),
            'user_agent' => $this->context->getUserAgent(),
            'referer'    => $this->context->getReferer(),
            'is_mobile'  => $this->context->isMobile() ? 'true' : 'false',
            'is_table'   => $this->context->isTablet() ? 'true' : 'false',
            'is_web'     => $this->context->isWeb() ? 'true' : 'false',
        );
    }
}
