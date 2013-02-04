<?php

namespace VGMdb\Component\User\Util;

/**
 * Simple canonicalizer, lowercases everything.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @copyright (c) 2010-2012 FriendsOfSymfony
 */
class Canonicalizer implements CanonicalizerInterface
{
    public function canonicalize($string)
    {
        return mb_convert_case($string, MB_CASE_LOWER, mb_detect_encoding($string));
    }
}
