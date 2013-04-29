<?php

namespace VGMdb\Component\HttpFoundation;

use VGMdb\Component\HttpFoundation\Util\AcceptNegotiator;
use VGMdb\Component\HttpFoundation\EventListener\SubdomainListener;
use VGMdb\Component\HttpFoundation\EventListener\ExtensionListener;
use VGMdb\Component\HttpFoundation\EventListener\RequestFormatListener;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Handles subdomain, extension, accept header format and version negotiation.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class FormatNegotiatorProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['accept.format.extensions'] = array('json', 'xml', 'gif', 'qrcode');
        $app['accept.format.default_version'] = '1.0';
        $app['accept.format.override'] = false;
        $app['accept.hosts'] = array();

        $app['accept.format.negotiator'] = $app->share(function ($app) {
            return new AcceptNegotiator();
        });

        $app['accept.subdomain_listener'] = $app->share(function ($app) {
            return new SubdomainListener($app, $app['accept.subdomains']);
        });

        $app['accept.extension_listener'] = $app->share(function ($app) {
            return new ExtensionListener($app);
        });

        $app['accept.request_format_listener'] = $app->share(function ($app) {
            return new RequestFormatListener($app, $app['request_context']);
        });

        Request::addFormat('gif', array('image/gif'));
        Request::addFormat('qrcode', array('image/png'));
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['accept.subdomain_listener']); // 512
        $app['dispatcher']->addSubscriber($app['accept.extension_listener']); // 256
        $app['dispatcher']->addSubscriber($app['accept.request_format_listener']); // 128, -512, -16
    }
}
