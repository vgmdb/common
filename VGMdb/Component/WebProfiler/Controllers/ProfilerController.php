<?php

namespace VGMdb\Component\WebProfiler\Controllers;

use VGMdb\AbstractController;

/**
 * Controller for showing application profiling information.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ProfilerController extends AbstractController
{
    public function panelAction($token)
    {
        $this->app['profiler']->disable();
        $request = $this->app['request'];

        $panel = $request->query->get('panel', 'request');
        $page = $request->query->get('page', 'home');

        if (!$profile = $this->app['profiler']->loadProfile($token)) {
            throw new \ErrorException(sprintf('Token "%s" is invalid.', $token));
        }

        if (!$profile->hasCollector($panel)) {
            throw new \ErrorException(sprintf('Panel "%s" is not available for token "%s".', $panel, $token));
        }

        $templates = array();
        foreach ($this->app['data_collector.templates'] as $name => $template) {
            if (!$this->app['profiler']->has($name) || !$profile->hasCollector($name)) {
                continue;
            }
            $templates[$name] = $template;
        }

        if (!isset($templates[$panel])) {
            throw new \ErrorException(sprintf('Template not available for panel "%s".', $panel));
        }

        return $this->app['view']('@WebProfiler/' . $templates[$panel], array(
            'token'     => $token,
            'profile'   => $this->app['serializer']->serialize($profile, 'array'),
            'collector' => $this->app['serializer']->serialize($profile->getCollector($panel), 'array'),
            'panel'     => $panel,
            'page'      => $page,
            'request'   => $this->app['serializer']->serialize($request, 'array'),
            'templates' => $templates,
            'is_ajax'   => $request->isXmlHttpRequest(),
        ));
    }
}
