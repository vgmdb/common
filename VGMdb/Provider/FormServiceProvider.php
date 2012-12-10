<?php

namespace VGMdb\Provider;

use VGMdb\Component\Templating\TemplateNameParser;
use VGMdb\Component\Templating\Helper\FormHelper;
use VGMdb\Component\Templating\Helper\TranslatorHelper;
use VGMdb\Component\Form\Extension\Csrf\CsrfProvider\ExpiringSessionCsrfProvider;
use Silex\Application;
use Silex\Provider\FormServiceProvider as BaseFormServiceProvider;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine;
use Symfony\Component\Form\FormRenderer;

/**
 * Form component Provider.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class FormServiceProvider extends BaseFormServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $app['form.helper'] = $app->share(function ($app) {
            $r = new \ReflectionClass('VGMdb\\Component\\Templating\\Helper\\FormHelper');
            $loader = new FilesystemLoader(dirname(dirname($r->getFilename())) . '/Resources/views/%name%');
            $engine = new PhpEngine(new TemplateNameParser(), $loader);
            $translator_helper = new TranslatorHelper($app['translator']);
            $form_helper = new FormHelper(
                new FormRenderer(new TemplatingRendererEngine($engine, array('Form')), $app['form.csrf_provider'])
            );
            $engine->addHelpers(array($form_helper, $translator_helper));

            return $form_helper;
        });

        /*$app['form.csrf_provider'] = $app->share(function ($app) {
            return new ExpiringSessionCsrfProvider($app['session'], $app['form.secret']);
        });*/
    }

    public function boot(Application $app)
    {
    }
}
