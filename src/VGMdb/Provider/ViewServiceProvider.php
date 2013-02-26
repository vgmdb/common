<?php

namespace VGMdb\Provider;

use VGMdb\Component\View\ViewFactory;
use VGMdb\Component\View\AbstractView;
use VGMdb\Component\View\Widget;
use VGMdb\Component\View\EventListener\LayoutListener;
use VGMdb\Component\View\EventListener\RenderListener;
use VGMdb\Component\View\Logging\ViewLogger;
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

            AbstractView::share(array(
                $locale => true,
                'DEBUG' => $app['debug'],
                'YEAR'  => $app['locale.formatter.year']->format(new \DateTime())
            ));

            return $view_factory;
        });

        $app['view.logger'] = $app->share(function ($app) {
            return new ViewLogger($app['logger']);
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
            if (!isset($widgets[$template])) {
                $widgets[$template] = $widget = new Widget($view, $callback);
            }

            return $widgets[$template]->with($data);
        });
    }

    public function boot(Application $app)
    {
        foreach ($app['view.prefix_dirs'] as $prefix => $prefixDir) {
            ViewFactory::addPrefix($prefix, $prefixDir);
        }

        $app['dispatcher']->addSubscriber(new LayoutListener($app)); // -32
        $app['dispatcher']->addSubscriber(new RenderListener($app)); // -64
    }
}
