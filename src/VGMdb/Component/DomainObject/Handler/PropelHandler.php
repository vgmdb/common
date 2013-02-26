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
    public function offsetExists(&$object, $offset)
    {
        $getter = 'get' . static::classify($object, $offset);

        return method_exists($object, $getter);
    }

    public function offsetGet(&$object, $offset)
    {
        $getter = 'get' . static::classify($object, $offset);

        return $object->$getter;
    }

    public function offsetUnset(&$object, $offset)
    {
        $setter = 'set' . static::classify($object, $offset);
        $object->$setter(null);
    }

    public function offsetSet(&$object, $offset, $value)
    {
        if (is_null($offset)) {
            throw new \InvalidArgumentException('Offset must not be null.');
        } else {
            $setter = 'set' . static::classify($object, $offset);
            $object->$setter($value);
        }
    }

    protected static function classify($object, $offset)
    {
        return $object::translateFieldName($offset, \BasePeer::TYPE_FIELDNAME, \BasePeer::TYPE_PHPNAME);
    }
}
