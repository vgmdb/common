<?php

namespace VGMdb\Component\User\Util;

/**
 * Canonicalizer interface definition.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @copyright (c) 2010-2012 FriendsOfSymfony
 */
interface CanonicalizerInterface
{
    public function canonicalize($string);
}
