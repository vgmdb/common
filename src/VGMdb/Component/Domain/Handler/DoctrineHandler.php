<?php

namespace VGMdb\Component\Domain\Handler;

use VGMdb\Component\Domain\ArrayAccessHandlerInterface;
use VGMdb\Component\Doctrine\RegistryInterface;
use Doctrine\Common\Util\Inflector;

/**
 * Handles a Doctrine entity.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DoctrineHandler implements ArrayAccessHandlerInterface
{
    protected $factory;

    public function __construct(\Closure $factory)
    {
        $this->factory = $factory;
    }

    public function save($object)
    {
        if ($manager = $this->getRegistry()->getEntityManagerForClass(get_class($object))) {
            $manager->persist($object);
            $manager->flush();
        }
    }

    public function delete($object)
    {
        if ($manager = $this->getRegistry()->getEntityManagerForClass(get_class($object))) {
            $manager->remove($object);
            $manager->flush();
        }
    }

    protected function getRegistry()
    {
        static $registry;

        if (null === $registry) {
            $registry = call_user_func($this->factory);
            if (!$registry instanceof RegistryInterface) {
                throw new \LogicException("Factory supplied to DoctrineHandler must return implementation of RegistryInterface.");
            }
        }

        return $registry;
    }

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
