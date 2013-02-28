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
        $getter = 'get' . static::classify($offset);

        return method_exists($object, $getter);
    }

    public function offsetGet($object, $offset)
    {
        $getter = 'get' . static::classify($offset);

        if (!method_exists($object, $getter)) {
            throw new \InvalidArgumentException('Offset does not exist.');
        }

        return $object->$getter();
    }

    public function offsetUnset($object, $offset)
    {
        $setter = 'set' . static::classify($offset);

        if (!method_exists($object, $setter)) {
            throw new \InvalidArgumentException('Offset does not exist.');
        }

        $object->$setter(null);
    }

    public function offsetSet($object, $offset, $value)
    {
        $setter = 'set' . static::classify($offset);

        if (!method_exists($object, $setter)) {
            throw new \InvalidArgumentException('Offset does not exist.');
        }

        $object->$setter($value);
    }

    protected static function classify($offset)
    {
        static $classified = array();

        if (!isset($classified[$offset])) {
            $classified[$offset] = Inflector::classify($offset);
        }

        return $classified[$offset];
    }
}
