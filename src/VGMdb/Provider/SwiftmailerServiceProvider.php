<?php

namespace VGMdb\Provider;

use VGMdb\Component\Swiftmailer\EventListener\EmailSenderListener;
use Silex\Application;
use Silex\Provider\SwiftmailerServiceProvider as BaseSwiftmailerServiceProvider;

/**
 * Swiftmailer Provider.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class SwiftmailerServiceProvider extends BaseSwiftmailerServiceProvider
{
    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber(new EmailSenderListener($app));
    }
}
