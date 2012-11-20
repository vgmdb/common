<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\Provider\TranslationServiceProvider as BaseTranslationServiceProvider;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;

/**
 * Translation component Provider.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TranslationServiceProvider extends BaseTranslationServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $app['translator'] = $app->share($app->extend('translator', function ($translator, $app) {
            $translator->addLoader('pofile', new PoFileLoader());

            foreach (glob($app['translator.po_dir'] . '/*.po') as $pofile) {
                $name = basename($pofile, '.po');
                list($domain, $locale) = explode('.', $name);
                $translator->addResource('pofile', $pofile, $locale, $domain);
            }

            return $translator;
        }));

        $app['translator.po_dir'] = __DIR__;

        $app['translate'] = $app->protect(function ($p0, $p1 = null, $p2 = null, $p3 = null, $p4 = null) use ($app) {
            if (!is_null($p1) && !is_array($p1)) {
                list($id, $number, $parameters, $domain, $locale) = array($p0, $p1, $p2, $p3, $p4);
            } else {
                list($id, $number, $parameters, $domain, $locale) = array($p0, null, $p1, $p2, $p3);
            }
            if (!is_array($parameters)) {
                $parameters = array();
            }
            if (is_null($domain)) {
                $domain = 'messages';
            }
            if (!is_null($number)) {
                return $app['translator']->transChoice($id, $number, $parameters, $domain, $locale);
            }

            return $app['translator']->trans($id, $parameters, $domain, $locale);
        });
    }
}
