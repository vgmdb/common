<?php

namespace VGMdb\Component\Serializer\Handler;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\PhpCollectionHandler as BasePhpCollectionHandler;

class PhpCollectionHandler extends BasePhpCollectionHandler
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        $formats = array('array', 'json', 'xml', 'yml');
        $collectionTypes = array('PhpCollection\Sequence' => 'Sequence');

        foreach ($collectionTypes as $type => $shortName) {
            foreach ($formats as $format) {
                $methods[] = array(
                    'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'serialize'.$shortName,
                );

                $methods[] = array(
                    'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'deserialize'.$shortName,
                );
            }
        }

        return $methods;
    }
}
