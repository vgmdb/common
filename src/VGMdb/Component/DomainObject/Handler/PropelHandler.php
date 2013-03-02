<?php

namespace VGMdb\Component\DomainObject\Handler;

use VGMdb\Component\DomainObject\ArrayAccessHandlerInterface;

/**
 * Handles a Propel object.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class PropelHandler implements ArrayAccessHandlerInterface
{
    public function offsetExists($object, $offset)
    {
        $getter = 'get' . self::accessorify($object, $offset);

        return method_exists($object, $getter);
    }

    public function offsetGet($object, $offset)
    {
        $getter = 'get' . self::accessorify($object, $offset);

        if (method_exists($object, $getter)) {
            return $object->$getter();
        }

        return null;
    }

    public function offsetUnset($object, $offset)
    {
        $setter = 'set' . self::accessorify($object, $offset);

        if (method_exists($object, $setter)) {
            $object->$setter(null);
        }
    }

    public function offsetSet($object, $offset, $value)
    {
        $setter = 'set' . self::accessorify($object, $offset);

        if (method_exists($object, $setter)) {
            $object->$setter($value);
        }
    }

    protected static function accessorify($object, $offset)
    {
        $peer = $object::PEER;

        try {
            return $peer::translateFieldName($offset, \BasePeer::TYPE_FIELDNAME, \BasePeer::TYPE_PHPNAME);
        } catch (\PropelException $e) {
            return null;
        }
    }
}
