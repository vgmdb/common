<?php

/*
 * This code was originally part of JMSTranslationBundle.
 *
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 */

namespace VGMdb\Component\Translation\Annotation;

/**
 * @Annotation
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
final class Id
{
    /** @var string @Required */
    public $text;

    public function __construct()
    {
        if (0 === func_num_args()) {
            return;
        }
        $values = func_get_arg(0);

        if (isset($values['value'])) {
            $values['text'] = $values['value'];
        }

        if (!isset($values['text'])) {
            throw new \RuntimeException(sprintf('The "text" attribute for annotation "@Id" must be set.'));
        }

        $this->text = $values['text'];
    }
}
