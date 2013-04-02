<?php

namespace VGMdb\Component\Layout\EventListener;

use VGMdb\Application;
use VGMdb\Component\View\ViewInterface;
use VGMdb\Component\Routing\Translation\TranslationRouteLoader;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Applies a layout wrapper to HTML responses.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class LayoutListener implements EventSubscriberInterface
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        if (false !== $pos = strpos($route, TranslationRouteLoader::ROUTING_PREFIX)) {
            $route = substr($route, $pos + strlen(TranslationRouteLoader::ROUTING_PREFIX));
        }

        $defaultLayout = $request->attributes->get('_layout');
        if (null === $defaultLayout && isset($this->app['layout.default_template'])) {
            $defaultLayout = $this->app['layout.default_template'];
        }
        $layouts = $this->app['layout.config'];

        $config = isset($layouts[$route]) ? $layouts[$route] : array();
        if (!isset($config['layout'])) {
            $config['layout'] = $defaultLayout;
        }

        if ($request->getRequestFormat() === 'html') {
            $response = $event->getResponse();
            $content = $response->getContent();
            if ($content instanceof ViewInterface) {
                $response->setContent($this->buildLayout($content, $config));
            }
        }
    }

    protected function buildLayout(ViewInterface $content, $config = null)
    {
        $layout = $this->app['view']($config['layout'])
            ->with($this->app['layout.default_data'])
            ->with(array(
                'html' => array(
                    'lang' => $this->app['locale']
                ),
                'base_url' => $this->app['app.options']['domain']
            ));

        foreach ($this->app['layout.filters'] as $filter) {
            if (class_exists($filter)) {
                $filter = array($filter, 'onLayout');
            } elseif (!is_callable($filter)) {
                throw new \RuntimeException('Layout filter must be a callable.');
            }
            call_user_func($filter, $this->app, $layout);
        }

        return $content->wrap($layout);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse', -32)),
        );
    }
}
