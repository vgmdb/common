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
        if ($request->getRequestFormat() !== 'html') {
            return;
        }

        $route = $request->attributes->get('_route');
        if (false !== $pos = strpos($route, TranslationRouteLoader::ROUTING_PREFIX)) {
            $route = substr($route, $pos + strlen(TranslationRouteLoader::ROUTING_PREFIX));
        }

        $layout = $request->attributes->get('_layout');
        $layouts = $this->app['layout.config'];
        $replacements = array(
            '%locale%' => $this->app['request_context']->getLanguage(),
            '%app%' => $this->app['request_context']->getAppName(),
            '%client%' => $this->app['request_context']->isMobile() ? 'mobile' : 'web',
        );
        $config = $layout && isset($layouts[$layout])
            ? $this->doReplacements($layouts[$layout], $replacements)
            : array();

        if (!isset($config['layout'])) {
            $config['layout'] = array();
        }
        if (!$layout && isset($config['layout']['template'])) {
            $layout = $config['layout']['template'];
        }
        if ($layout) {
            $config['layout']['template'] = $this->getDefaultTemplate($layout, 'layouts');
            $layoutData = $this->doReplacements($this->app['layout.default_data'], $replacements);
        }

        $response = $event->getResponse();
        $content = $response->getContent();

        if ($content instanceof ViewInterface) {
            $data = $content->getArrayCopy();
            $content = $this->filterLayout($this->onLayout($content, $config, $data, $layoutData));
        } elseif ($layout) {
            $data = is_array($content) ? $content : array('content' => $content);
            $content = $this->filterLayout($this->app['view']($config['layout']['template'], array_merge($data, $layoutData)));
        }

        $response->setContent($content);
    }

    protected function onLayout(ViewInterface $view, array $config = array(), array $data = array(), array $layoutData = array())
    {
        if (isset($config['views']) && is_array($config['views'])) {
            foreach ($config['views'] as $key => $viewConfig) {
                $viewConfig['key'] = is_numeric($key) ? 'content' : $key;
                $view = $this->nestView($view, $viewConfig, isset($data[$key]) ? $data[$key] : array());
            }
        }

        if (isset($config['widgets']) && is_array($config['widgets'])) {
            foreach ($config['widgets'] as $key => $viewConfig) {
                $viewConfig['key'] = is_numeric($key) ? 'content' : $key;
                $view = $this->nestWidget($view, $viewConfig, isset($data[$key]) ? $data[$key] : array());
            }
        }

        if (isset($config['layout']['template'])) {
            $view = $this->wrapLayout($view, $config['layout']['template'], $layoutData);
        }

        return $view;
    }

    protected function wrapLayout(ViewInterface $view, $template, array $data = array())
    {
        $layout = $this->app['view']($template, $data);

        return $view->wrap($layout);
    }

    protected function nestView(ViewInterface $view, array $config = array(), array $data = array())
    {
        $template = isset($config['template'])
            ? $config['template']
            : $this->getDefaultTemplate($config['key']);

        $childView = $this->onLayout($this->app['view']($template, $data), $config, $data);

        return $view->nest($childView, $config['key']);
    }

    protected function nestWidget(ViewInterface $view, array $config = array(), array $data = array())
    {
        $widgetClass = $config['widget'];
        $widget = new $widgetClass($this->app);
        $widget->with($data);

        $childView = $this->onLayout($widget, $config, $data);

        return $view->nest($childView, $config['key']);
    }

    protected function filterLayout(ViewInterface $view)
    {
        foreach ($this->app['layout.filters'] as $filter) {
            if (class_exists($filter)) {
                $filter = array($filter, 'onLayout');
            } elseif (!is_callable($filter)) {
                throw new \RuntimeException('Layout filter must be a callable.');
            }
            call_user_func($filter, $this->app, $view);
        }

        return $view;
    }

    protected function doReplacements($value, array $replacements)
    {
        if (!$replacements) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->doReplacements($v, $replacements);
            }

            return $value;
        }

        if (is_string($value)) {
            return strtr($value, $replacements);
        }

        return $value;
    }

    protected function getDefaultTemplate($name, $baseDir = 'sites')
    {
        if ('@' === $name[0] || false !== strpos('/', $name)) {
            return $name;
        }

        return sprintf(
            '%s/%s/%s/%s',
            $baseDir,
            $this->app['request_context']->getAppName(),
            $this->app['request_context']->isMobile() ? 'mobile' : 'web',
            $name
        );
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse', -32)),
        );
    }
}
