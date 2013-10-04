<?php

namespace VGMdb\Component\Swiftmailer;

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
    public function register(Application $app)
    {
        parent::register($app);

        $app['mailer'] = $app->share($app->extend('mailer', function ($mailer) use ($app) {
            if (isset($app['swiftmailer.redirect'])) {
                $redirectPlugin = new \Swift_Plugins_RedirectingPlugin($app['swiftmailer.redirect']);
                $mailer->registerPlugin($redirectPlugin);
            }

            return $mailer;
        }));
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber(new EmailSenderListener($app));
    }
}
