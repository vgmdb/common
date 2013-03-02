<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\Provider\ValidatorServiceProvider as BaseValidatorServiceProvider;
use Symfony\Component\Validator\Validator;

/**
 * Symfony Validator component Provider.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ValidatorServiceProvider extends BaseValidatorServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $app['validator'] = $app->share(function ($app) {
            if (isset($app['translator'])) {
                $r = new \ReflectionClass('Symfony\\Component\\Validator\\Validator');
                $app['translator']->addResource('xliff', dirname($r->getFilename()).'/Resources/translations/validators.'.$app['locale'].'.xlf', $app['locale'], 'validators');
            }

            // Compatible with Symfony 2.2.x
            return new Validator(
                $app['validator.mapping.class_metadata_factory'],
                $app['validator.validator_factory'],
                $app['translator'],
                'validators'
            );
        });
    }
}
