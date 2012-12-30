<?php

namespace VGMdb\Component\WebProfiler\Controllers;

use VGMdb\AbstractController;
use Symfony\Component\Routing\Matcher\TraceableUrlMatcher;

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

        if (!isset($this->app['data_collector.templates'][$panel])) {
            throw new \ErrorException(sprintf('Template not available for panel "%s".', $panel));
        }

        $collector = $profile->getCollector($panel);

        if ($panel === 'exception' && !$collector->hasException()) {
            throw new \ErrorException(sprintf('Panel "%s" is not available for token "%s".', $panel, $token));
        }

        $profileData = $this->app['serializer']->serialize($profile, 'array');

        $templates = array();
        $toolbar = $this->app['view']('@WebProfiler/profiler/toolbar');

        foreach ($this->app['data_collector.templates'] as $name => $template) {
            if (null === $template) {
                continue;
            }
            if (!$this->app['profiler']->has($name) || !$profile->hasCollector($name)) {
                continue;
            }
            if ($name === 'exception' && !$profile->getCollector('exception')->hasException()) {
                continue;
            }

            $toolbar->nest($this->app['view']('@WebProfiler/profiler/menu', array(
                'name'     => $name,
                'selected' => ($name === $panel) ? true : false,
                'url'      => $this->app['url']('profiler', array('token' => $token, 'panel' => $name))
            ))->nest($this->app['view']('@WebProfiler/' . $template, array(
                'token'     => $token,
                'toolbar'   => true
            ))));

            $templates[$name] = $template;
        }

        $layoutData = array(
            'token'     => $token,
            'profile'   => $profileData,
            'panel'     => $panel,
            'page'      => $page,
            //'request'   => $this->app['serializer']->serialize($request, 'array'),
            'templates' => $templates,
            'is_ajax'   => $request->isXmlHttpRequest()
        );

        $panelData = array(
            'token'     => $token,
            'collector' => $profileData['collectors'][$panel],
            'panel'     => true
        );

        if ($panel === 'time') {
            $events = $panelData['collector']['data']['events'];
            unset($events['__section__']);
            $panelData['events_json'] = json_encode(array_values($events));
        } elseif ($panel === 'router') {
            if (isset($profileData['collectors']['request'])) {
                $request = $profile->getCollector('request');
                $context = $this->app['url_matcher']->getContext();
                $context->setMethod($profile->getMethod());
                $matcher = new TraceableUrlMatcher($this->app['routes'], $context);
                $panelData['request'] = $profileData['collectors']['request'];
                $panelData['request']['data']['route_params'] = $request->getRouteParams();
                $panelData['traces'] = $matcher->getTraces($request->getPathInfo());
            }
        }

        $layoutData['profile']['time_pretty'] = date('r', $layoutData['profile']['time']);
        unset($layoutData['profile']['collectors']);

        return $this->app['view']('@WebProfiler/profiler/layout', $layoutData)
                    ->nest($toolbar, 'toolbar')
                    ->nest($this->app['view']('@WebProfiler/' . $templates[$panel], $panelData));
    }
}
