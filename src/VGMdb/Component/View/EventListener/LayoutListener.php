<?php

namespace VGMdb\Component\View\EventListener;

use VGMdb\Application;
use VGMdb\Component\View\ViewInterface;
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

        $response = $event->getResponse();
        $request = $event->getRequest();
        $template = $request->attributes->get('_layout');

        if ($event->getRequest()->getRequestFormat() === 'html') {
            if (!$template && isset($this->app['view.default_layout'])) {
                $template = $this->app['view.default_layout'];
            }

            $layout = $this->app['view']($template)
                ->with($this->app['view.default_data'])
                ->with(array(
                    'html' => array(
                        'lang' => $this->app['locale']
                    ),
                    'base_url' => $this->app['app.options']['domain']
                ));

            foreach ($this->app['view.layout_filters'] as $filter) {
                if (class_exists($filter)) {
                    $filter = array($filter, 'onLayout');
                } elseif (!is_callable($filter)) {
                    throw new \RuntimeException('Layout filter must be a callable.');
                }
                call_user_func($filter, $this->app, $layout);
            }

            $content = $response->getContent();

            if ($content instanceof ViewInterface) {
                $response->setContent($content->wrap($layout));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse', -32)),
        );
    }
}
