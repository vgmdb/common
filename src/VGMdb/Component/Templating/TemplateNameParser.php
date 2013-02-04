<?php

namespace VGMdb\Component\Templating;

use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Templating\TemplateReference;

/**
 * TemplateNameParser which converts colon to slash.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TemplateNameParser implements TemplateNameParserInterface
{
    /**
     * Parses a template to an array of parameters.
     *
     * @param string $name A template name
     *
     * @return TemplateReferenceInterface A template
     */
    public function parse($name)
    {
        if ($name instanceof TemplateReferenceInterface) {
            return $name;
        }

        $engine = null;
        if (false !== $pos = strrpos($name, '.')) {
            $engine = substr($name, $pos + 1);
        }

        $name = str_replace(':', '/', $name);

        return new TemplateReference($name, $engine);
    }
}
