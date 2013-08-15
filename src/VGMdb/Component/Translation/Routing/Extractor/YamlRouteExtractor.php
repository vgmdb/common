<?php

namespace VGMdb\Component\Translation\Routing\Extractor;

use VGMdb\Component\Translation\Routing\RouteExclusionStrategyInterface;
use VGMdb\Component\Translation\Routing\TranslationRouter;
use VGMdb\Component\Translation\Extractor\AbstractExtractor;
use VGMdb\Component\Translation\Extractor\Model\FileSource;
use VGMdb\Component\Translation\Extractor\Model\Message;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * YamlRouteExtractor extracts routes from YAML files.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class YamlRouteExtractor extends AbstractExtractor
{
    protected $router;
    protected $routeExclusionStrategy;
    protected $domain;

    public function __construct(RouterInterface $router, RouteExclusionStrategyInterface $routeExclusionStrategy)
    {
        $this->router = $router;
        $this->routeExclusionStrategy = $routeExclusionStrategy;
        $this->domain = 'routes';
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($directory, MessageCatalogue $catalogue)
    {
        // note: $directory is ignored
        $routes = $this->router instanceof TranslationRouter
            ? $this->router->getOriginalRouteCollection()
            : $this->router->getRouteCollection();

        foreach ($routes->all() as $name => $route) {
            if ($this->routeExclusionStrategy->shouldExcludeRoute($name, $route)) {
                continue;
            }

            $message = new Message($name, $this->domain);
            $message->setDesc($route->getPattern());

            $catalogue->set($name, $message, $this->domain);
        }
    }
}
