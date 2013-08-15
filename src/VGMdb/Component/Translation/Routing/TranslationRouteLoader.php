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

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\Loader\LoaderResolver;

/**
 * This loader expands all routes which are eligible for translation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TranslationRouteLoader
{
    const ROUTING_PREFIX = '.';

    private $routeExclusionStrategy;
    private $pathGenerationStrategy;

    public function __construct(RouteExclusionStrategyInterface $routeExclusionStrategy, PathGenerationStrategyInterface $pathGenerationStrategy)
    {
        $this->routeExclusionStrategy = $routeExclusionStrategy;
        $this->pathGenerationStrategy = $pathGenerationStrategy;
    }

    public function load(RouteCollection $collection)
    {
        $translatedCollection = new RouteCollection();
        foreach ($collection->getResources() as $resource) {
            $translatedCollection->addResource($resource);
        }
        $this->pathGenerationStrategy->addResources($translatedCollection);

        foreach ($collection->all() as $name => $route) {
            if ($this->routeExclusionStrategy->shouldExcludeRoute($name, $route)) {
                $translatedCollection->add($name, $route);
                continue;
            }

            foreach ($this->pathGenerationStrategy->generateTranslatedPaths($name, $route) as $pattern => $locales) {
                // If this pattern is used for more than one locale, we need to keep the original route.
                // We still add individual routes for each locale afterwards for faster generation.
                if (count($locales) > 1) {
                    $catchMultipleRoute = clone $route;
                    $catchMultipleRoute->setPattern($pattern);
                    $catchMultipleRoute->setDefault('_locales', $locales);
                    $translatedCollection->add(implode('_', $locales).static::ROUTING_PREFIX.$name, $catchMultipleRoute);
                }

                foreach ($locales as $locale) {
                    $localeRoute = clone $route;
                    $localeRoute->setPattern($pattern);
                    $localeRoute->setDefault('_locale', $locale);
                    $translatedCollection->add($locale.static::ROUTING_PREFIX.$name, $localeRoute);
                }
            }
        }

        return $translatedCollection;
    }
}
