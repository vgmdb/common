<?php

namespace VGMdb\Component\Accept;

use VGMdb\Component\Accept\AcceptNegotiator;
use VGMdb\Component\Accept\EventListener\SubdomainListener;
use VGMdb\Component\Accept\EventListener\ExtensionListener;
use VGMdb\Component\Accept\EventListener\FormatListener;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Handles subdomain, extension, accept header format and version negotiation.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class AcceptServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['accept.format.extensions'] = array('json', 'xml', 'pdf', 'qrcode');
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

        $app['accept.format_listener'] = $app->share(function ($app) {
            return new FormatListener($app, $app['request_context']);
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['accept.subdomain_listener']); // 512
        $app['dispatcher']->addSubscriber($app['accept.extension_listener']); // 256
        $app['dispatcher']->addSubscriber($app['accept.format_listener']); // 128, -512, -16
    }
}
