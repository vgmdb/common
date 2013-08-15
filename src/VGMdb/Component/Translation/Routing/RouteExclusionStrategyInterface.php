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

/**
 * Interface for route exclusions.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface RouteExclusionStrategyInterface
{
    /**
     * Implementations determine whether the given route is eligible for translation.
     *
     * @param string $routeName
     * @param Route  $route
     *
     * @return Boolean
     */
    public function shouldExcludeRoute($routeName, Route $route);
}
