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

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * The default strategy supports 3 different scenarios, and makes use of the
 * Symfony2 Translator Component.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PathGenerationStrategy implements PathGenerationStrategyInterface
{
    const STRATEGY_PREFIX = 'prefix';
    const STRATEGY_PREFIX_EXCEPT_DEFAULT = 'prefix_except_default';
    const STRATEGY_CUSTOM = 'custom';

    private $strategy;
    private $translator;
    private $translationDomain;
    private $locales;
    private $cacheDir;
    private $defaultLocale;

    /**
     * Constructor.
     *
     * @param string              $strategy
     * @param TranslatorInterface $translator
     * @param array               $locales
     * @param string              $cacheDir
     * @param string              $translationDomain
     * @param string              $defaultLocale
     */
    public function __construct($strategy, TranslatorInterface $translator, array $locales, $cacheDir, $translationDomain = 'routes', $defaultLocale = 'en')
    {
        $this->strategy = $strategy;
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
        $this->locales = $locales;
        $this->cacheDir = $cacheDir;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * {@inheritDoc}
     */
    public function generateTranslatedPaths($routeName, Route $route)
    {
        $paths = array();
        foreach ($route->getOption('_locales') ?: $this->locales as $locale) {
            // if no translation exists, we use the current pattern
            if ($routeName === $translatedPath = (string) $this->translator->trans($routeName, array(), $this->translationDomain, $locale)) {
                $translatedPath = $route->getPath();
            }

            // prefix with locale if requested
            if (static::STRATEGY_PREFIX === $this->strategy || (static::STRATEGY_PREFIX_EXCEPT_DEFAULT === $this->strategy && $this->defaultLocale !== $locale)) {
                $translatedPath = '/'.$locale.$translatedPath;
            }

            $paths[$translatedPath][] = $locale;
        }

        return $paths;
    }

    /**
     * {@inheritDoc}
     */
    public function addResources(RouteCollection $routeCollection)
    {
        foreach ($this->locales as $locale) {
            if (file_exists($metadata = $this->cacheDir.'/translations/catalogue.'.$locale.'.php.meta')) {
                foreach (unserialize(file_get_contents($metadata)) as $resource) {
                    $routeCollection->addResource($resource);
                }
            }
        }
    }
}
