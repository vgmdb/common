<?php

namespace VGMdb\Component\Serializer\Handler;

use JMS\Serializer\Handler\DateHandler as BaseDateHandler;
use JMS\Serializer\GraphNavigator;

class DateHandler extends BaseDateHandler
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        $types = array('DateTime', 'DateInterval');

        foreach (array('array', 'json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'type' => 'DateTime',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => $format,
            );

            foreach ($types as $type) {
                $methods[] = array(
                    'type' => $type,
                    'format' => $format,
                    'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                    'method' => 'serialize'.$type,
                );
            }
        }

        return $methods;
    }
}
