<?php

namespace VGMdb\Component\Domain\Handler;

use VGMdb\Component\Domain\ArrayAccessHandlerInterface;
use VGMdb\Component\Domain\DomainObjectInterface;
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

    public function save(DomainObjectInterface $object)
    {
        if ($manager = $this->getRegistry()->getEntityManagerForClass(get_class($entity = $object->getEntity()))) {
            $manager->persist($entity);
            $manager->flush();
        }
    }

    public function delete(DomainObjectInterface $object)
    {
        if ($manager = $this->getRegistry()->getEntityManagerForClass(get_class($entity = $object->getEntity()))) {
            $manager->remove($entity);
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

    protected static function accessorify($offset)
    {
        static $classified = array();

        if (!isset($classified[$offset])) {
            $classified[$offset] = Inflector::classify($offset);
        }

        return $classified[$offset];
    }
}
