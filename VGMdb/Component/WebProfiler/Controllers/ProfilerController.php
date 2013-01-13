<?php

namespace VGMdb\Component\WebProfiler\Controllers;

use VGMdb\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Matcher\TraceableUrlMatcher;
use SqlFormatter;

/**
 * Controller for showing application profiling information.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ProfilerController extends AbstractController
{
    /**
     * Renders a profiler panel for the given token.
     *
     * @param string $token The profiler token
     *
     * @return ViewInterface A view instance
     *
     * @throws NotFoundHttpException
     */
    public function panelAction($token)
    {
        $this->app['profiler']->disable();
        $request = $this->app['request'];

        $panel = $request->query->get('panel', 'request');
        $page = $request->query->get('page', 'home');

        if (!$profile = $this->app['profiler']->loadProfile($token)) {
            throw new NotFoundHttpException(sprintf('Token "%s" is invalid.', $token));
        }

        if (!$profile->hasCollector($panel)) {
            throw new NotFoundHttpException(sprintf('Panel "%s" is not available for token "%s".', $panel, $token));
        }

        if (!isset($this->app['data_collector.templates'][$panel])) {
            throw new NotFoundHttpException(sprintf('Template not available for panel "%s".', $panel));
        }

        $collector = $profile->getCollector($panel);

        if ($panel === 'exception' && !$collector->hasException()) {
            throw new NotFoundHttpException(sprintf('Panel "%s" is not available for token "%s".', $panel, $token));
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
            'is_ajax'   => $request->isXmlHttpRequest(),
            'urls'      => array(
                'search' => $this->app['url_generator']->generate('profiler_search', array('limit' => 10))
            )
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
        } elseif ($panel === 'db' || $panel === 'propel') {
            foreach ($panelData['collector']['data']['queries'] as $index => $query) {
                $panelData['collector']['data']['queries'][$index]['sql_pretty'] = SqlFormatter::format($query['sql']);
            }
        }

        $layoutData['profile']['time_pretty'] = date('r', $layoutData['profile']['time']);
        unset($layoutData['profile']['collectors']);

        $searchData = array(
            'urls' => array(
                'search' => $this->app['url_generator']->generate('profiler_search_results', array('token' => $token))
            )
        );

        return $this->app['view']('@WebProfiler/profiler/layout', $layoutData)
                    ->nest($toolbar, 'toolbar')
                    ->nest($this->app['view']('@WebProfiler/profiler/search', $searchData), 'searchbar')
                    ->nest($this->app['view']('@WebProfiler/' . $templates[$panel], $panelData));
    }

    /**
     * Renders the profiler search bar.
     *
     * @return ViewInterface A view instance
     */
    public function searchBarAction()
    {
        $this->app['profiler']->disable();

        $request = $this->app['request'];

        if (null === $session = $request->getSession()) {
            $ip     = null;
            $method = null;
            $url    = null;
            $start  = null;
            $end    = null;
            $limit  = null;
            $token  = null;
        } else {
            $ip     = $session->get('_profiler_search_ip');
            $method = $session->get('_profiler_search_method');
            $url    = $session->get('_profiler_search_url');
            $start  = $session->get('_profiler_search_start');
            $end    = $session->get('_profiler_search_end');
            $limit  = $session->get('_profiler_search_limit');
            $token  = $session->get('_profiler_search_token');
        }

        return $this->app['view']('@WebProfiler/profiler/layout')->nest($this->app['view']('@WebProfiler/profiler/search', array(
            'token'  => $token,
            'ip'     => $ip,
            'method' => $method,
            'url'    => $url,
            'start'  => $start,
            'end'    => $end,
            'limit'  => $limit,
        )));
    }

    /**
     * Search for profiles.
     *
     * @return Response A Response instance
     */
    public function searchAction()
    {
        $this->app['profiler']->disable();

        $request = $this->app['request'];

        $ip     = preg_replace('/[^:\d\.]/', '', $request->query->get('ip'));
        $method = $request->query->get('method');
        $url    = $request->query->get('url');
        $start  = $request->query->get('start', null);
        $end    = $request->query->get('end', null);
        $limit  = $request->query->get('limit');
        $token  = $request->query->get('token');

        if (null !== $session = $request->getSession()) {
            $session->set('_profiler_search_ip', $ip);
            $session->set('_profiler_search_method', $method);
            $session->set('_profiler_search_url', $url);
            $session->set('_profiler_search_start', $start);
            $session->set('_profiler_search_end', $end);
            $session->set('_profiler_search_limit', $limit);
            $session->set('_profiler_search_token', $token);
        }

        if (!empty($token)) {
            return new RedirectResponse($this->app['url_generator']->generate('profiler', array('token' => $token)));
        }

        $tokens = $this->app['profiler']->find($ip, $url, $limit, $method, $start, $end);

        return new RedirectResponse($this->app['url_generator']->generate('profiler_search_results', array(
            'token'  => $tokens ? $tokens[0]['token'] : 'empty',
            'ip'     => $ip,
            'method' => $method,
            'url'    => $url,
            'start'  => $start,
            'end'    => $end,
            'limit'  => $limit,
        )));
    }

    /**
     * Search results.
     *
     * @param Request $request The current HTTP Request
     * @param string  $token   The token
     *
     * @return Response A Response instance
     */
    public function searchResultsAction($token)
    {
        $this->app['profiler']->disable();

        $request = $this->app['request'];
        $profile = $this->app['profiler']->loadProfile($token);

        $ip     = $request->query->get('ip');
        $method = $request->query->get('method');
        $url    = $request->query->get('url');
        $start  = $request->query->get('start', null);
        $end    = $request->query->get('end', null);
        $limit  = $request->query->get('limit');
        $tokens = $this->app['profiler']->find($ip, $url, $limit, $method, $start, $end);

        foreach ($tokens as $index => $result) {
            $tokens[$index]['link'] = $this->app['url_generator']->generate('profiler', array('token' => $result['token']));
            $tokens[$index]['time'] = date('r', $result['time']);
        }

        return $this->app['view']('@WebProfiler/profiler/layout')->nest($this->app['view']('@WebProfiler/profiler/results', array(
            'token'   => $token,
            'profile' => $profile,
            'tokens'  => $tokens,
            'ip'      => $ip,
            'method'  => $method,
            'url'     => $url,
            'start'   => $start,
            'end'     => $end,
            'limit'   => $limit,
            'panel'   => null,
        )));
    }

    /**
     * Exports data for a given token.
     *
     * @param string $token The profiler token
     *
     * @return Response A Response instance
     *
     * @throws NotFoundHttpException
     */
    public function exportAction($token)
    {
        $this->app['profiler']->disable();

        if (!$profile = $this->app['profiler']->loadProfile($token)) {
            throw new NotFoundHttpException(sprintf('Token "%s" does not exist.', $token));
        }

        return new Response($this->app['profiler']->export($profile), 200, array(
            'Content-Type'        => 'text/plain',
            'Content-Disposition' => 'attachment; filename= '.$token.'.txt',
        ));
    }

    /**
     * Purges all tokens.
     *
     * @return ViewInterface A view instance
     */
    public function purgeAction()
    {
        $this->app['profiler']->disable();
        $this->app['profiler']->purge();

        return $this->app['view']('@WebProfiler/profiler/layout', array(
            'content' => '<div style="padding: 40px"><h2>The profiler database was purged successfully</h2></div>'
        ));
    }
}
