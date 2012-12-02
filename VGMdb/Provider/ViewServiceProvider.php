<?php

namespace VGMdb\Provider;

use VGMdb\Component\View\ViewInterface;
use VGMdb\Component\View\ViewFactory;
use VGMdb\Component\View\View;
use VGMdb\Component\View\Widget;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * View service provides view and widget factories.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ViewServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // View factory
        $app['view.proto'] = $app->protect(function ($template, $engine = null) use ($app) {
            $view = ViewFactory::create($template, array(), $engine, ($app['debug'] ? $app['logger'] : null));

            $locale = $app['locale'] ?: $app['locale_fallback'];
            $view::share(strtoupper($locale), true);
            $view::share('DEBUG', $app['debug']);

            return $view;
        });

        $app['view'] = $app->protect(function ($template, array $data = array(), $type = null) use ($app) {
            if (!$type) {
                $type = $app['view.template.engine'];
            }
            $view = $app['view.proto']($template, $app[$type]);

            return $view->with($data);
        });

        // Widget factory
        $app['widget'] = $app->protect(function ($view, $callback = null) use ($app) {
            static $widgets = array();
            if (!array_key_exists($template, $widgets)) {
                $widgets[$template] = $widget = new Widget($view, $callback);
            }

            return $widgets[$template]->with($data);
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addListener(KernelEvents::RESPONSE, function (FilterResponseEvent $event) use ($app) {
            $attributes = $event->getRequest()->attributes;

            if ($event->getRequest()->getRequestFormat() === 'html') {
                $layout_name = $attributes->get('_layout');
                if (!$layout_name && isset($app['view.default_layout'])) {
                    $layout_name = $app['view.default_layout'];
                }

                if (isset($app['layouts'])) {
                    $layout = $app['layouts']($layout_name);
                    $content = $event->getResponse()->getContent();
                    if ($content instanceof ViewInterface && $layout instanceof ViewInterface) {
                        $event->getResponse()->setContent($content->wrap($layout));
                    }
                }
            }
        }, -255);
    }
}