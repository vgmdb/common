<?php

/*
 * This code was originally part of the Symfony2 FrameworkBundle.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */

namespace VGMdb\Component\Routing;

use Silex\Application;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RequestContext as BaseRequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Psr\Log\LoggerInterface;

/**
 * This Router creates the Loader only when the cache is empty.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class LazyRouter extends Router implements WarmableInterface
{
    protected $parameters;
    protected $matcherProxyClass = 'VGMdb\\Component\\Routing\\Matcher\\RedirectableProxyUrlMatcher';

    /**
     * Constructor.
     *
     * @param LoaderInterface $loader     A LoaderInterface instance
     * @param mixed           $resource   The main resource to load
     * @param array           $options    An array of options
     * @param parameters      $parameters An array of routing parameters
     * @param RequestContext  $context    The context
     * @param LoggerInterface $logger     A logger instance
     */
    public function __construct(LoaderInterface $loader, $resource, array $options = array(), array $parameters = array(), RequestContext $context = null, LoggerInterface $logger = null)
    {
        $this->parameters = $parameters;

        /**
         * This is a workaround, since the parent class filters out unknown keys
         */
        if (isset($options['matcher_proxy_class'])) {
            $this->matcherProxyClass = $options['matcher_proxy_class'];
            unset($options['matcher_proxy_class']);
        }

        parent::__construct($loader, $resource, $options, $context, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        if (null === $this->collection) {
            parent::getRouteCollection();
            $this->resolveParameters($this->collection);
        }

        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatcher()
    {
        if (null !== $this->matcher) {
            return $this->matcher;
        }

        $class = $this->matcherProxyClass;
        $matcher = parent::getMatcher();

        return $this->matcher = new $class($matcher);
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $currentDir = $this->getOption('cache_dir');

        // force cache generation
        $this->matcher = null;
        $this->generator = null;
        $this->setOption('cache_dir', $cacheDir);
        $this->getMatcher();
        $this->getGenerator();

        $this->setOption('cache_dir', $currentDir);
    }

    /**
     * Replaces placeholders with routing parameter values in:
     * - the route defaults,
     * - the route requirements,
     * - the route pattern.
     * - the route host.
     *
     * @param RouteCollection $collection
     */
    private function resolveParameters(RouteCollection $collection)
    {
        foreach ($collection as $route) {
            foreach ($route->getDefaults() as $name => $value) {
                $route->setDefault($name, $this->resolve($value));
            }

            foreach ($route->getRequirements() as $name => $value) {
                $route->setRequirement($name, $this->resolve($value));
            }

            $route->setPath($this->resolve($route->getPath()));
            $route->setHost($this->resolve($route->getHost()));
        }
    }

    /**
     * Recursively replaces placeholders with the supplied routing parameters.
     *
     * @param mixed $value The source which might contain "%placeholders%"
     *
     * @return mixed The source with the placeholders replaced by the routing
     *               parameters. Array are resolved recursively.
     *
     * @throws \RuntimeException When a parameter is not a string or a numeric value
     */
    private function resolve($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->resolve($val);
            }

            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        $parameters = $this->parameters;

        $escapedValue = preg_replace_callback('/%%|%([^%\s]+)%/', function ($match) use ($parameters, $value) {
            // skip %%
            if (!isset($match[1])) {
                return '%%';
            }

            $key = strtolower($match[1]);

            if (!isset($parameters[$key])) {
                return '%' . $match[1] . '%';
            }

            $resolved = $parameters[$key];

            if (is_string($resolved) || is_numeric($resolved)) {
                return (string) $resolved;
            }

            throw new \RuntimeException(sprintf(
                'A string value must be composed of strings and/or numbers,' .
                'but found parameter "%s" of type %s inside string value "%s".',
                $key,
                gettype($resolved),
                $value)
            );

        }, $value);

        return str_replace('%%', '%', $escapedValue);
    }
}
