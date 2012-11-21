<?php

namespace VGMdb\Provider;

use VGMdb\Component\View\ViewInterface;
use VGMdb\Component\View\View;
use VGMdb\Component\View\MustacheView;
use VGMdb\Component\View\Widget;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @brief       View service provides view and widget factories.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class ViewServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // View factory
        $app['view'] = $app->protect(function ($template, array $data = array()) use ($app) {
            $callback = null;

            if (isset($app['view.template.engine']) && $app['view.template.engine'] === 'mustache') {
                $view = new MustacheView($template, array(), $app['mustache']);
            } else {
                $callback = function ($view) {
                    return $view->getArrayCopy();
                };
                $view = new View($template, array(), $callback);
            }

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
            $locale = strtoupper($attributes->get('_locale'));
            if (!$locale) {
                $locale = strtoupper($app['locale_fallback']);
            }

            if (isset($app['mustache'])) {
                $app['mustache']->addHelper($locale, true);
                $app['mustache']->addHelper('DEBUG', $app['debug']);
            } else {
                View::share($locale, true);
                View::share('DEBUG', $app['debug']);
            }

            if ($event->getRequest()->getRequestFormat() === 'html') {
                $layout_provider = $attributes->get('_layouts');
                $layout_name = $attributes->get('_layout');

                if (is_callable($layout_provider)) {
                    $layout = $layout_provider($layout_name);
                    $content = $event->getResponse()->getContent();
                    if ($content instanceof ViewInterface && $layout instanceof ViewInterface) {
                        $event->getResponse()->setContent($content->wrap($layout));
                    }
                }
            }
        }, -255);
    }
}