<?php

namespace VGMdb\Component\Serializer\Handler;

use Thrift\Base\TBase;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\VisitorInterface;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Handler\SubscribingHandlerInterface;

class ThriftHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        $formats = array('array', 'json', 'xml', 'yml');

        foreach ($formats as $format) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => 'TBase',
                'format' => $format,
                'method' => 'serializeThrift',
            );
        }

        return $methods;
    }

    public function serializeThrift(VisitorInterface $visitor, TBase $struct, array $type)
    {
        $data = array();

        $fields = $struct::$_TSPEC;
        foreach ($fields as $field) {
            $data[$field['var']] = $struct->{$field['var']};
        }

        $type['name'] = 'array';

        return $visitor->visitArray($data, $type);
    }
}
