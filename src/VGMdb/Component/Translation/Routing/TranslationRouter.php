<?php

/*
 * Copyright 2012 Johannes M. Schmitt <schmittjoh@gmail.com>
 * Modified by Gigablah <gigablah@vgmdb.net>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace VGMdb\Component\Translation\Routing;

use VGMdb\Component\Routing\LazyRouter;
use VGMdb\Component\Routing\Exception\NotAcceptableLanguageHttpException;
use Silex\Application;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Router that supports route translations.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TranslationRouter extends LazyRouter
{
    private $localeResolver;
    private $translationLoader;
    private $defaultLocale;
    private $redirectToHost = true;
    private $hostMap = array();

    /**
     * Set the locale resolver.
     *
     * @param LocaleResolverInterface $resolver
     */
    public function setLocaleResolver(LocaleResolverInterface $resolver)
    {
        $this->localeResolver = $resolver;
    }

    /**
     * Set the translation loader.
     *
     * @param TranslationRouteLoader $translationLoader
     */
    public function setTranslationLoader(TranslationRouteLoader $translationLoader)
    {
        $this->translationLoader = $translationLoader;
    }

    /**
     * Whether the user should be redirected to a different host if the
     * matching route is not belonging to the current domain.
     *
     * @param Boolean $bool
     */
    public function setRedirectToHost($bool)
    {
        $this->redirectToHost = (Boolean) $bool;
    }

    /**
     * Sets the host map to use.
     *
     * @param array $hostMap a map of locales to hosts
     */
    public function setHostMap(array $hostMap)
    {
        $this->hostMap = $hostMap;
    }

    /**
     * Sets the default locale.
     *
     * @param string $locale The default locale.
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string  $name       The name of the route
     * @param array   $parameters An array of parameters
     * @param Boolean $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        // determine the most suitable locale to use for route generation
        $currentLocale = $this->getContext()->getParameter('_locale');
        if (isset($parameters['_locale'])) {
            $locale = $parameters['_locale'];
        } elseif ($currentLocale) {
            $locale = $currentLocale;
        } else {
            $locale = $this->defaultLocale;
        }

        // if the locale is changed, and we have a host map, then we need to
        // generate an absolute URL
        if ($currentLocale && $currentLocale !== $locale && $this->hostMap) {
            $absolute = true;
        }

        // if an absolute URL is requested, we set the correct host
        if ($absolute && $this->hostMap) {
            $currentHost = $this->getContext()->getHost();
            $this->getContext()->setHost($this->hostMap[$locale]);
        }

        try {
            $url = $this->getGenerator()->generate($locale.TranslationRouteLoader::ROUTING_PREFIX.$name, $parameters, $absolute);

            if ($absolute && $this->hostMap) {
                $this->getContext()->setHost($currentHost);
            }

            return $url;
        } catch (RouteNotFoundException $ex) {
            if ($absolute && $this->hostMap) {
                $this->getContext()->setHost($currentHost);
            }

            // fallback to default behavior
        }

        // use the default behavior if no localized route exists
        return $this->getGenerator()->generate($name, $parameters, $absolute);
    }

    /**
     * Tries to match a URL with a set of routes.
     *
     * Returns false if no route matches the URL.
     *
     * @param string $url URL to be parsed
     *
     * @return array|false An array of parameters or false if no route matches
     */
    public function match($url)
    {
        $params = $this->getMatcher()->match($url);

        if (false === $params) {
            return false;
        }

        if (isset($params['_locales'])) {
            if (false !== $pos = strpos($params['_route'], TranslationRouteLoader::ROUTING_PREFIX)) {
                $params['_route'] = substr($params['_route'], $pos + strlen(TranslationRouteLoader::ROUTING_PREFIX));
            }

            if (!($currentLocale = $this->getContext()->getParameter('_locale'))) {
                $currentLocale = $this->localeResolver->resolveLocale($this->app['request'], $params['_locales']);

                // If the locale resolver was not able to determine a locale, then all efforts to
                // make an informed decision have failed. Just display something as a last resort.
                if (!$currentLocale) {
                    $currentLocale = reset($params['_locales']);
                }
            }

            if (!in_array($currentLocale, $params['_locales'], true)) {
                // TODO: We might want to allow the user to be redirected to the route for the given locale if
                //       it exists regardless of whether it would be on another domain, or the same domain.
                //       Below we assume that we do not want to redirect always.

                // if the available locales are on a different host, throw a ResourceNotFoundException
                if ($this->hostMap) {
                    // generate host maps
                    $hostMap = $this->hostMap;
                    $availableHosts = array_map(function ($locale) use ($hostMap) {
                        return $hostMap[$locale];
                    }, $params['_locales']);

                    $differentHost = true;
                    foreach ($availableHosts as $host) {
                        if ($this->hostMap[$currentLocale] === $host) {
                            $differentHost = false;
                            break;
                        }
                    }

                    if ($differentHost) {
                        throw new ResourceNotFoundException(
                            sprintf(
                                'The route "%s" is not available on the current host "%s", but only on these hosts "%s".',
                                $params['_route'],
                                $this->hostMap[$currentLocale],
                                implode(', ', $availableHosts)
                            )
                        );
                    }
                }

                // no host map, or same host means that the given locale is not supported for this route
                throw new NotAcceptableLanguageHttpException($currentLocale, $params['_locales']);
            }

            unset($params['_locales']);
            $params['_locale'] = $currentLocale;
        } elseif (isset($params['_locale']) && 0 < $pos = strpos($params['_route'], TranslationRouteLoader::ROUTING_PREFIX)) {
            $params['_route'] = substr($params['_route'], $pos + strlen(TranslationRouteLoader::ROUTING_PREFIX));
        }

        // check if the matched route belongs to a different locale on another host
        if (isset($params['_locale']) && isset($this->hostMap[$params['_locale']]) && $this->getContext()->getHost() !== $host = $this->hostMap[$params['_locale']]) {
            if (!$this->redirectToHost) {
                throw new ResourceNotFoundException(
                    sprintf(
                        'Resource corresponding to pattern "%s" not found for locale "%s".',
                        $url,
                        $this->getContext()->getParameter('_locale')
                    )
                );
            }

            return array(
                '_controller' => 'VGMdb\\Component\\HttpKernel\\Controller\\RedirectController::urlRedirectAction',
                'path'        => $url,
                'host'        => $host,
                'permanent'   => true,
                'scheme'      => $this->getContext()->getScheme(),
                'httpPort'    => $this->getContext()->getHttpPort(),
                'httpsPort'   => $this->getContext()->getHttpsPort(),
                '_route'      => $params['_route'],
            );
        }

        // if we have no locale set on the route, we try to set one according to the localeResolver
        // if we don't do this all _internal routes will have the default locale on first request
        if (!isset($params['_locale']) && $locale = $this->localeResolver->resolveLocale($this->app['request'], $this->app['routing.translation.locales'])) {
            $params['_locale'] = $locale;
        }

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        $collection = parent::getRouteCollection();

        return $this->translationLoader->load($collection);
    }

    public function getOriginalRouteCollection()
    {
        return parent::getRouteCollection();
    }
}
