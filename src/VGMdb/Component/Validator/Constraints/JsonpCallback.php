<?php

namespace VGMdb\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class JsonpCallback extends Constraint
{
    public $message = 'This value is not a valid JSONP callback.';
}
