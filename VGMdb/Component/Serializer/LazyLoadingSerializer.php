<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
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

namespace VGMdb\Component\Serializer;

use Silex\Application;
use JMS\SerializerBundle\Serializer\Serializer;
use JMS\SerializerBundle\Serializer\VisitorInterface;

class LazyLoadingSerializer extends Serializer
{
    private $app;

    public function setContainer(Application $app)
    {
        $this->app = $app;
    }

    public function getDeserializationVisitor($format)
    {
        $visitor = parent::getDeserializationVisitor($format);

        if ($visitor instanceof VisitorInterface) {
            return $visitor;
        }

        return $this->app[$visitor];
    }

    public function getSerializationVisitor($format)
    {
        $visitor = parent::getSerializationVisitor($format);

        if ($visitor instanceof VisitorInterface) {
            return $visitor;
        }

        return $this->app[$visitor];
    }
}
