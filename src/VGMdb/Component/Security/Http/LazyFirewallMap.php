<?php

namespace VGMdb\Component\Security\Http;

use Silex\Application;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\FirewallMapInterface;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;

/**
 * LazyFirewallMap loads listeners only upon demand.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class LazyFirewallMap implements FirewallMapInterface
{
    private $app;
    private $map = array();

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function add(RequestMatcherInterface $requestMatcher = null, array $listeners = array(), ExceptionListener $exceptionListener = null)
    {
        $this->map[] = array($requestMatcher, $listeners, $exceptionListener);
    }

    public function getListeners(Request $request)
    {
        foreach ($this->map as $elements) {
            if (null === $elements[0] || $elements[0]->matches($request)) {
                $listeners = $elements[1];
                foreach ($listeners as $index => $listener) {
                    if ($listener instanceof \Closure) {
                        $listeners[$index] = $listener();
                    }
                }

                return array($listeners, $elements[2]);
            }
        }

        return array(array(), null);
    }
}
