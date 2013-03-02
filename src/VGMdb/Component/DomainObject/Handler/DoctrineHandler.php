<?php

namespace VGMdb\Component\DomainObject\Handler;

use VGMdb\Component\DomainObject\ArrayAccessHandlerInterface;
use Doctrine\Common\Util\Inflector;

/**
 * Handles a Doctrine entity.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DoctrineHandler implements ArrayAccessHandlerInterface
{
    public function offsetExists($object, $offset)
    {
        $getter = 'get' . self::accessorify($offset);

        return method_exists($object, $getter);
    }

    public function offsetGet($object, $offset)
    {
        $getter = 'get' . self::accessorify($offset);

        if (method_exists($object, $getter)) {
            return $object->$getter();
        }

        return null;
    }

    public function offsetUnset($object, $offset)
    {
        $setter = 'set' . self::accessorify($offset);

        if (method_exists($object, $setter)) {
            $object->$setter(null);
        }
    }

    public function offsetSet($object, $offset, $value)
    {
        $setter = 'set' . self::accessorify($offset);

        if (method_exists($object, $setter)) {
            $object->$setter($value);
        }
    }

    protected static function accessorify($offset)
    {
        static $classified = array();

        if (!isset($classified[$offset])) {
            $classified[$offset] = Inflector::classify($offset);
        }

        return $classified[$offset];
    }
}
