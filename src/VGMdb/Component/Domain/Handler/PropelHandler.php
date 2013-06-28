<?php

namespace VGMdb\Component\Domain\Handler;

use VGMdb\Component\Domain\ArrayAccessHandlerInterface;
use VGMdb\Component\Domain\DomainObjectInterface;
use VGMdb\Component\Propel\Util\PropelInflector;

/**
 * Handles a Propel or Propel2 object.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class PropelHandler implements ArrayAccessHandlerInterface
{
    protected $factory;

    public function __construct(\Closure $factory)
    {
        $this->factory = $factory;
    }

    public function save(DomainObjectInterface $object)
    {
        $object->getEntity()->save($this->getConnection());
    }

    public function delete(DomainObjectInterface $object)
    {
        $object->getEntity()->delete($this->getConnection());
    }

    protected function getConnection()
    {
        static $connection;

        if (null === $connection) {
            $connection = call_user_func($this->factory);
        }

        return $connection;
    }

    public function offsetExists(DomainObjectInterface $object, $offset)
    {
        $getter = 'get' . self::accessorify($offset);

        return method_exists($object->getEntity(), $getter);
    }

    public function offsetGet(DomainObjectInterface $object, $offset)
    {
        $getter = 'get' . self::accessorify($offset);

        if (method_exists($entity = $object->getEntity(), $getter)) {
            return $entity->$getter();
        }

        return null;
    }

    public function offsetUnset(DomainObjectInterface $object, $offset)
    {
        $setter = 'set' . self::accessorify($offset);

        if (method_exists($entity = $object->getEntity(), $setter)) {
            $entity->$setter(null);
        }
    }

    public function offsetSet(DomainObjectInterface $object, $offset, $value)
    {
        $setter = 'set' . self::accessorify($offset);

        if (method_exists($entity = $object->getEntity(), $setter)) {
            $entity->$setter($value);
        }
    }

    public function proxy(DomainObjectInterface $object, $method, $arguments)
    {
        if (method_exists($entity = $object->getEntity(), $method)) {
            return call_user_func_array(array($entity, $method), $arguments);
        }

        return null;
    }

    protected static function accessorify($offset)
    {
        static $classified = array();

        if (!isset($classified[$offset])) {
            $classified[$offset] = PropelInflector::classify($offset);
        }

        return $classified[$offset];
    }
}
