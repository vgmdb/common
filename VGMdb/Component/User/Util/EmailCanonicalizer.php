<?php

namespace VGMdb\Component\User\Util;

/**
 * Removes portions after the plus sign from email addresses.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class EmailCanonicalizer implements CanonicalizerInterface
{
    public function canonicalize($string)
    {
        $encoding = mb_detect_encoding($string);
        $string = mb_convert_case($string, MB_CASE_LOWER, $encoding);
        if (false !== $plus = mb_strpos($string, '+', 0, $encoding)) {
            $string = mb_substr($string, 0, $plus) . mb_substr($string, mb_strrpos($string, '@', 0, $encoding));
        }
        return $string;
    }
}
