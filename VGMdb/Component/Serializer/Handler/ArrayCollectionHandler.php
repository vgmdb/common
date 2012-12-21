<?php

namespace VGMdb\Component\Serializer\Handler;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\ArrayCollectionHandler as BaseArrayCollectionHandler;

class ArrayCollectionHandler extends BaseArrayCollectionHandler
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        $formats = array('array', 'json', 'xml', 'yml');
        $collectionTypes = array('ArrayCollection', 'Doctrine\Common\Collections\ArrayCollection', 'Doctrine\ORM\PersistentCollection', 'Doctrine\ODM\MongoDB\PersistentCollection');

        foreach ($collectionTypes as $type) {
            foreach ($formats as $format) {
                $methods[] = array(
                    'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'serializeCollection',
                );

                $methods[] = array(
                    'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'deserializeCollection',
                );
            }
        }

        return $methods;
    }
}
