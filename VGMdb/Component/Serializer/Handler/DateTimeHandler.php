<?php

namespace VGMdb\Component\Serializer\Handler;

use JMS\Serializer\Handler\DateTimeHandler as BaseDateTimeHandler;
use JMS\Serializer\GraphNavigator;

class DateTimeHandler extends BaseDateTimeHandler
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (array('array', 'json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'type' => 'DateTime',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => $format,
            );

            $methods[] = array(
                'type' => 'DateTime',
                'format' => $format,
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method' => 'serializeDateTime',
            );
        }

        return $methods;
    }
}
