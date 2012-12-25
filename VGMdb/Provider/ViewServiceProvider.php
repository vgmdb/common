<?php

namespace VGMdb\Provider;

use VGMdb\Component\View\ViewFactory;
use VGMdb\Component\View\AbstractView;
use VGMdb\Component\View\Widget;
use VGMdb\Component\View\EventListener\LayoutListener;
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
        $app['view_factory'] = $app->share(function () use ($app) {
            $view_factory = new ViewFactory();

            if ($app['debug']) {
                $view_factory->setLogger($app['logger']);
            }

            $locale = $app['locale'] ? strtoupper($app['locale']) : strtoupper($app['locale_fallback']);
            AbstractView::share($locale, true);
            AbstractView::share('DEBUG', $app['debug']);

            return $view_factory;
        });

        $app['view'] = $app->protect(function ($template, array $data = array(), $type = null) use ($app) {
            if (!$type) {
                $type = $app['view.default_engine'];
            }
            $view = $app['view_factory']->create($template, $data, $app[$type]);

            return $view;
        });

        $app['widget'] = $app->protect(function ($view, $callback = null) use ($app) {
            static $widgets = array();
            if (!array_key_exists($template, $widgets)) {
                $widgets[$template] = $widget = new Widget($view, $callback);
            }

            return $widgets[$template]->with($data);
        });

        $app['view.layout_listener'] = $app->share(function () use ($app) {
            return new LayoutListener($app);
        });
    }

    public function boot(Application $app)
    {
        foreach ($app['view.prefix_dirs'] as $prefix => $prefixDir) {
            ViewFactory::addPrefix($prefix, $prefixDir);
        }

        $app['dispatcher']->addSubscriber($app['view.layout_listener']); // -64
    }
}
