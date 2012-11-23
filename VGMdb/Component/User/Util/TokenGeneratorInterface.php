<?php

namespace VGMdb\Component\User\Util;

/**
 * Token generator interface definition.
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @copyright (c) 2010-2012 FriendsOfSymfony
 */
interface TokenGeneratorInterface
{
    /**
     * @return string
     */
    public function generateToken();
}
