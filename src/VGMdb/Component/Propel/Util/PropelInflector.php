<?php

namespace VGMdb\Component\Propel\Util;

/**
 * The Propel inflector class provides methods for inflecting text.
 *
 * @author William Durand <william.durand1@gmail.com>
 */
class PropelInflector
{
    /**
     * Convert a word into the format for a class name. Converts 'table_name' to 'TableName'
     * Inspired by https://github.com/doctrine/common/blob/master/lib/Doctrine/Common/Util/Inflector.php
     *
     * @param string $word Word to classify
     *
     * @return string Classified word
     */
    public static function classify($word)
    {
        return str_replace(' ', '', ucwords(strtr($word, '_-', '  ')));
    }

    /**
     * Camelize a word.
     * Inspired by https://github.com/doctrine/common/blob/master/lib/Doctrine/Common/Util/Inflector.php
     *
     * @param string $word The word to camelize.
     *
     * @return string Camelized word
     */
    public static function camelize($word)
    {
        return lcfirst(self::classify($word));
    }
}
