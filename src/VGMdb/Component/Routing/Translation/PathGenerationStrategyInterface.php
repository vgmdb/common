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

namespace VGMdb\Component\Routing\Translation;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Implementations are responsible for generating the translated paths for a given route.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface PathGenerationStrategyInterface
{
    /**
     * Returns the translated paths for a given route.
     *
     * @param string $routeName
     * @param Route  $route
     *
     * @return array<string, array<string>> an array mapping the path to an array of locales
     */
    function generateTranslatedPaths($routeName, Route $route);

    /**
     * You may add possible resources to the translated collection.
     *
     * This may for example be translation resources.
     *
     * @param RouteCollection $routeCollection
     */
    function addResources(RouteCollection $routeCollection);
}
