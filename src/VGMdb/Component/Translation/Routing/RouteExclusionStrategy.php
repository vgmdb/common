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
 * The default route exclusion strategy.
 *
 * This strategy ignores all routes if at least one of the following is true:
 *
 *     - the route name starts with an underscore
 *     - the option "_translate" is set to false
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class RouteExclusionStrategy implements RouteExclusionStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function shouldExcludeRoute($routeName, Route $route)
    {
        if ('_' === $routeName[0]) {
            return true;
        }

        if (false === $route->getOption('_translate')) {
            return true;
        }

        return false;
    }
}
