<?php

namespace VGMdb\Component\Security\Http;

use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\FirewallMapInterface;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;

class TraceableFirewallMap implements FirewallMapInterface
{
    private $app;
    private $map;
    private $matchers;
    private $traces;

    public function __construct(Application $app, FirewallMapInterface $map)
    {
        $this->app = $app;
        $this->map = $map;
        $this->matchers = array();
        $this->traces = array();
    }

    public function add(RequestMatcherInterface $requestMatcher = null, array $listeners = array(), ExceptionListener $exceptionListener = null)
    {
        /**
         * @todo: wrap request matchers in traceable proxies
         */
        $this->matchers[] = $requestMatcher;

        $this->map->add($requestMatcher, $listeners, $exceptionListener);
    }

    public function getListeners(Request $request)
    {
        $trace = array();

        $listeners = $this->map->getListeners($request);

        foreach ($this->matchers as $requestMatcher) {
            $matches = null === $requestMatcher || $requestMatcher->matches($request);
            $trace[] = array(
                'matches' => $matches,
                'reason' => !$matches ? $requestMatcher->getLastMatch() : null,
            );
        }

        $this->traces[] = $trace;

        return $listeners;
    }

    public function getTraces()
    {
        return $this->traces;
    }
}
