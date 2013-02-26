<?php

namespace VGMdb\Component\DomainObject\Handler;

use VGMdb\Component\DomainObject\ArrayAccessHandlerInterface;

/**
 * Handles a plain array.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ArrayHandler implements ArrayAccessHandlerInterface
{
    public function offsetExists(&$object, $offset)
    {
        return isset($object[$offset]) || array_key_exists($offset, $object);
    }

    public function offsetGet(&$object, $offset)
    {
        return isset($offset, $object) ? $object[$offset] : null;
    }

    public function offsetUnset(&$object, $offset)
    {
        unset($object[$offset]);
    }

    public function offsetSet(&$object, $offset, $value)
    {
        if (is_null($offset)) {
            throw new \InvalidArgumentException('Offset must not be null.');
        } else {
            $object[$offset] = $value;
        }
    }
}
