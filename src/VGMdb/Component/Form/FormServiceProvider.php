<?php

namespace VGMdb\Component\Form;

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

        $app['form.flow.class'] = 'VGMdb\\Component\\Form\\FormFlow';
        $app['form.flow.storage.class'] = 'VGMdb\\Component\\Form\\Storage\\SessionStorage';

        $app['form.flow.storage'] = $app->share(function ($app) {
            return new $app['form.flow.storage.class']($app['session']);
        });

        $app['form.flow'] = $app->share(function ($app) {
            $flow = new $app['form.flow.class']();
            $flow->setFormFactory($app['form.factory']);
            $flow->setRequest($app['request']);
            $flow->setStorage($app['form.flow.storage']);
            $flow->setEventDispatcher($app['dispatcher']);

            return $flow;
        });
    }

    public function boot(Application $app)
    {
    }
}
