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
        $getter = 'get' . static::classify($object, $offset);

        return method_exists($object, $getter);
    }

    public function offsetGet($object, $offset)
    {
        $getter = 'get' . static::classify($object, $offset);

        if (!method_exists($object, $getter)) {
            throw new \InvalidArgumentException('Offset does not exist.');
        }

        return $object->$getter();
    }

    public function offsetUnset($object, $offset)
    {
        $setter = 'set' . static::classify($object, $offset);

        if (!method_exists($object, $setter)) {
            throw new \InvalidArgumentException('Offset does not exist.');
        }

        $object->$setter(null);
    }

    public function offsetSet($object, $offset, $value)
    {
        $setter = 'set' . static::classify($object, $offset);

        if (!method_exists($object, $setter)) {
            throw new \InvalidArgumentException('Offset does not exist.');
        }

        $object->$setter($value);
    }

    protected static function classify($object, $offset)
    {
        $peer = $object::PEER;

        try {
            return $peer::translateFieldName($offset, \BasePeer::TYPE_FIELDNAME, \BasePeer::TYPE_PHPNAME);
        } catch (\PropelException $e) {
            return null;
        }
    }
}
