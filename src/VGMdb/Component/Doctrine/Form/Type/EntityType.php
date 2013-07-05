<?php

/*
 * This file was originally part of the Symfony DoctrineBridge.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */

namespace VGMdb\Component\Doctrine\Form\Type;

use VGMdb\Component\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Doctrine\Common\Persistence\ObjectManager;

class EntityType extends DoctrineType
{
    /**
     * Return the default loader object.
     *
     * @param ObjectManager $manager
     * @param mixed         $queryBuilder
     * @param string        $class
     * @return ORMQueryBuilderLoader
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        return new ORMQueryBuilderLoader(
            $queryBuilder,
            $manager,
            $class
        );
    }

    public function getName()
    {
        return 'entity';
    }
}
