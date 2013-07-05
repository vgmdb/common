<?php

/*
 * This file was originally part of the Symfony DoctrineBridge.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */

namespace VGMdb\Component\Doctrine\Form\ChoiceList;

/**
 * Custom loader for entities in the choice list.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
interface EntityLoaderInterface
{
    /**
     * Returns an array of entities that are valid choices in the corresponding choice list.
     *
     * @return array The entities.
     */
    public function getEntities();

    /**
     * Returns an array of entities matching the given identifiers.
     *
     * @param string $identifier The identifier field of the object. This method
     *                           is not applicable for fields with multiple
     *                           identifiers.
     * @param array $values The values of the identifiers.
     *
     * @return array The entities.
     */
    public function getEntitiesByIds($identifier, array $values);
}
