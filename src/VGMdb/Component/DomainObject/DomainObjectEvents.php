<?php

namespace VGMdb\Component\DomainObject;

/**
 * Events thrown by domain objects.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
final class DomainObjectEvents
{
    /**
     * The SAVE event occurs when an object is saved.
     *
     * @var string
     */
    const SAVE = 'domain.save';

    /**
     * The DELETE event occurs when an object is deleted.
     *
     * @var string
     */
    const DELETE = 'domain.delete';
}
