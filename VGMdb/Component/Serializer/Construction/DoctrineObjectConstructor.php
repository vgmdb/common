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

namespace VGMdb\Component\Serializer\Construction;

use JMS\SerializerBundle\Serializer\Construction\ObjectConstructorInterface;
use JMS\SerializerBundle\Serializer\VisitorInterface;
use JMS\SerializerBundle\Metadata\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;

class DoctrineObjectConstructor implements ObjectConstructorInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $objectManager;

    /**
     * @var \JMS\SerializerBundle\Serializer\Construction\ObjectConstructorInterface
     */
    private $fallbackConstructor;

    /**
     * Constructor.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager                               $objectManager
     * @param \JMS\SerializerBundle\Serializer\Construction\ObjectConstructorInterface $fallbackConstructor
     */
    public function __construct(ObjectManager $objectManager, ObjectConstructorInterface $fallbackConstructor)
    {
        $this->objectManager       = $objectManager;
        $this->fallbackConstructor = $fallbackConstructor;
    }

    /**
     * {@inheritdoc}
     */
    public function construct(VisitorInterface $visitor, ClassMetadata $metadata, $data, $type)
    {
        $objectManager = $this->objectManager;

        if (!$objectManager) {
            // No ObjectManager found, proceed with normal deserialization
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type);
        }

        // Locate possible ClassMetadata
        $classMetadataFactory = $objectManager->getMetadataFactory();

        if ($classMetadataFactory->isTransient($metadata->name)) {
            // No ClassMetadata found, proceed with normal deserialization
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type);
        }

        // Managed entity, check for proxy load
        if (!is_array($data)) {
            // Single identifier, load proxy
            return $objectManager->getReference($metadata->name, $data);
        }

        // Entity update, load it from database
        $classMetadata         = $objectManager->getClassMetadata($metadata->name);
        $identifierList        = $classMetadata->getIdentifierFieldNames();
        $missingIdentifierList = array_filter(
            $identifierList,
            function ($identifier) use ($data)
            {
                return !isset($data[$identifier]);
            }
        );

        return (!$missingIdentifierList)
            ? $objectManager->find($metadata->name, $data)
            : $this->fallbackConstructor->construct($visitor, $metadata, $data, $type);
    }
}
