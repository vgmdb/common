<?php

namespace VGMdb\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validate JSONP Callback
 *
 * https://github.com/tav/scripts/blob/master/validate_jsonp.py
 * https://github.com/talis/jsonp-validator/blob/master/src/main/java/com/talis/jsonp/JsonpCallbackValidator.java
 * http://tav.espians.com/sanitising-jsonp-callback-identifiers-for-security.html
 * http://news.ycombinator.com/item?id=809291
 * https://gist.github.com/1217080
 *
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class JsonpCallbackValidator extends ConstraintValidator
{
    private $reserved = array(
        'break',
        'do',
        'instanceof',
        'typeof',
        'case',
        'else',
        'new',
        'var',
        'catch',
        'finally',
        'return',
        'void',
        'continue',
        'for',
        'switch',
        'while',
        'debugger',
        'function',
        'this',
        'with',
        'default',
        'if',
        'throw',
        'delete',
        'in',
        'try',
        'class',
        'enum',
        'extends',
        'super',
        'const',
        'export',
        'import',
        'implements',
        'let',
        'private',
        'public',
        'yield',
        'interface',
        'package',
        'protected',
        'static',
        'null',
        'true',
        'false'
    );

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === trim($value)) {
            $this->context->addViolation($constraint->message);
            return;
        }

        foreach (explode('.', $value) as $identifier) {
            if (in_array($identifier, $this->reserved)) {
                $this->context->addViolation($constraint->message);
                return;
            }
        }
    }
}