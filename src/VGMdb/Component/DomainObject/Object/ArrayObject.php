<?php

namespace VGMdb\Component\DomainObject\Object;

use VGMdb\Component\DomainObject\AbstractDomainObject;

/**
 * Plain array.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ArrayObject extends AbstractDomainObject
{
    public function doSave()
    {
    }

    public function doDelete()
    {
    }

    public static function accepts($object)
    {
        return is_array($object);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new \InvalidArgumentException('Offset must not be null.');
        } else {
            $this->object[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->object);
    }

    public function offsetUnset($offset)
    {
        unset($this->object[$offset]);
    }

    public function offsetGet($offset)
    {
        return array_key_exists($offset, $this->object) ? $this->object[$offset] : null;
    }
}
