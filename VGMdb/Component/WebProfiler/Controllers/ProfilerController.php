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

        $collector = $profile->getCollector($panel);

        $templates = array();
        $toolbar = $this->app['view']('@WebProfiler/profiler/toolbar');
        foreach ($this->app['data_collector.templates'] as $name => $template) {
            if (!$this->app['profiler']->has($name) || !$profile->hasCollector($name)) {
                continue;
            }
            $toolbar->nest($this->app['view']('@WebProfiler/profiler/menu', array(
                'name'     => $name,
                'selected' => ($name === $panel) ? true : false,
                'url'      => $this->app['url']('profiler', array('token' => $token, 'panel' => $name))
            ))->nest($this->app['view']('@WebProfiler/' . $template, array(
                'toolbar'  => true
            ))));
            $templates[$name] = $template;
        }

        if (!isset($templates[$panel])) {
            throw new \ErrorException(sprintf('Template not available for panel "%s".', $panel));
        }

        $layoutData = array(
            'token'     => $token,
            'profile'   => $this->app['serializer']->serialize($profile, 'array'),
            'panel'     => $panel,
            'page'      => $page,
            'request'   => $this->app['serializer']->serialize($request, 'array'),
            'templates' => $templates,
            'is_ajax'   => $request->isXmlHttpRequest()
        );

        $layoutData['profile']['time_pretty'] = date('r', $layoutData['profile']['time']);

        $panelData = array(
            'token'     => $token,
            'collector' => $this->app['serializer']->serialize($collector, 'array'),
            'panel'     => true
        );

        if ($panel === 'time') {
            $panelData['collector']['data']['duration'] = sprintf('%.0f', $collector->getDuration());
            $panelData['collector']['data']['inittime'] = sprintf('%.0f', $collector->getInitTime());
            $events = array();
            foreach ($collector->getEvents() as $name => $event) {
                $panelData['collector']['data']['events'][$name]['name'] = $name;
                $panelData['collector']['data']['events'][$name]['starttime'] = $event->getStartTime();
                $panelData['collector']['data']['events'][$name]['endtime'] = $event->getEndTime();
                $panelData['collector']['data']['events'][$name]['duration'] = $event->getDuration();
                $panelData['collector']['data']['events'][$name]['memory'] = sprintf('%.1F', $event->getMemory() / 1024 / 1024);
                if ($name !== '__section__') {
                    $events[] = $panelData['collector']['data']['events'][$name];
                }
            }
            $panelData['events_json'] = json_encode($events);
            $panelData['colors'] = array(
                'default'                => '#aacd4e',
                'section'                => '#666',
                'event_listener'         => '#3dd',
                'event_listener_loading' => '#add',
                'template'               => '#dd3',
                'doctrine'               => '#d3d',
                'propel'                 => '#f4d',
                'child_sections'         => '#eed',
            );
            $panelData['colors_json'] = json_encode($panelData['colors']);
        }

        return $this->app['view']('@WebProfiler/profiler/layout', $layoutData)
                    ->nest($toolbar, 'toolbar')
                    ->nest($this->app['view']('@WebProfiler/' . $templates[$panel], $panelData));
    }
}
