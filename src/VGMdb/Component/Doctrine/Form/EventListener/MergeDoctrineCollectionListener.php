<?php

/*
 * This file was originally part of the Symfony DoctrineBridge.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */

namespace VGMdb\Component\Doctrine\Form\EventListener;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Merge changes from the request to a Doctrine\Common\Collections\Collection instance.
 *
 * This works with ORM, MongoDB and CouchDB instances of the collection interface.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @see    Doctrine\Common\Collections\Collection
 */
class MergeDoctrineCollectionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // Higher priority than core MergeCollectionListener so that this one
        // is called before
        return array(FormEvents::SUBMIT => array('onBind', 10));
    }

    public function onBind(FormEvent $event)
    {
        $collection = $event->getForm()->getData();
        $data = $event->getData();

        // If all items were removed, call clear which has a higher
        // performance on persistent collections
        if ($collection && count($data) === 0) {
            $collection->clear();
        }
    }
}
