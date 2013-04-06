<?php

namespace VGMdb\Component\Layout;

use VGMdb\Application;
use VGMdb\Component\View\ViewInterface;

/**
 * Represents a template layout.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Layout
{
    protected $app;
    protected $config;
    protected $layoutData;

    public function __construct(Application $app, array $config = array(), array $layoutData = array())
    {
        $this->app = $app;
        $this->config = $config;
        $this->layoutData = $layoutData;
    }

    public function wrap(ViewInterface $view)
    {
        $data = $view->getArrayCopy();

        return $this->filterLayout($this->onLayout($view, $this->config, $data, $this->layoutData));
    }

    protected function onLayout(ViewInterface $view, array $config, array $data, array $layoutData = array())
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
            $template = $this->getDefaultTemplate($config['layout']['template'], 'layouts');
            $view = $this->wrapLayout($view, $template, $layoutData);
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

    protected function getDefaultTemplate($name, $baseDir = 'sites')
    {
        if ('@' === $name[0] || false !== strpos('/', $name)) {
            return $name;
        }

        return sprintf(
            '%s/%s/%s/%s',
            $baseDir,
            $this->app['request_context']->getAppName(),
            'm' === $this->app['request_context']->getSubdomain()
                ? 'mobile'
                : ($this->app['request_context']->isMobile() ? 'mobile' : 'web'),
            $name
        );
    }
}
