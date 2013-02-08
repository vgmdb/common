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
     * The PRESAVE event occurs before an object is saved.
     *
     * @var string
     */
    const PRESAVE = 'domain.presave';

    /**
     * The POSTSAVE event occurs after an object is saved.
     *
     * @var string
     */
    const POSTSAVE = 'domain.postsave';

    /**
     * The PREDELETE event occurs before an object is deleted.
     *
     * @var string
     */
    const PREDELETE = 'domain.predelete';

    /**
     * The POSTDELETE event occurs after an object is deleted.
     *
     * @var string
     */
    const POSTDELETE = 'domain.postdelete';
}
