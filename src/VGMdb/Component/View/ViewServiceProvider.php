<?php

namespace VGMdb\Component\View;

use VGMdb\Component\View\EventListener\ViewListener;
use VGMdb\Component\View\Logger\ViewLogger;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * View service provides view and widget factories.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ViewServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['view_factory'] = $app->share(function ($app) {
            $view_factory = new ViewFactory();

            if ($app['debug']) {
                $view_factory->setLogger($app['view.logger']);
            }

            $locale = $app['locale'] ? strtoupper($app['locale']) : strtoupper($app['locale_fallback']);

            $globals = array(
                $locale  => true,
                'DEBUG'  => $app['debug'],
                'LOCALE' => $app['request_context']->getLocale(),
                'YEAR'   => date('Y')
            );

            if (isset($app['locale.formatter.year'])) {
                $globals['YEAR'] = $app['locale.formatter.year']->format(new \DateTime());
            }

            AbstractView::share($globals);

            return $view_factory;
        });

        $app['view.logger'] = $app->share(function ($app) {
            return new ViewLogger($app['logger']);
        });

        $app['view'] = $app->protect(function ($template, $data = array(), $type = null) use ($app) {
            if (!$type) {
                $type = $app['view.default_engine'];
            }
            if (!is_array($data)) {
                $data = array('content' => $data);
            }
            $view = $app['view_factory']->create($template, $data, $app[$type]);

            return $view;
        });

        $app['widget'] = $app->protect(function ($view, $callback = null) use ($app) {
            $widget = new Widget($view, $callback);

            return $widget->with($data);
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber(new ViewListener($app)); // -16, -64
    }
}
