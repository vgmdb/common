<?php

namespace VGMdb\Component\Locale;

use VGMdb\Component\Locale\EventListener\LocaleMappingListener;
use VGMdb\Component\Locale\EventListener\TimezoneListener;
use VGMdb\Component\HttpFoundation\Request;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Loads locale specific configuration upon boot.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class LocaleServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['locale.mapping'] = array();
        $app['locale.formats'] = array();
        $app['locale.timezones'] = array();

        $app['locale.mapping_listener'] = $app->share(function ($app) {
            return new LocaleMappingListener($app['request_context'], $app['locale.mapping']);
        });

        $app['locale.timezone_listener'] = $app->share(function ($app) {
            return new TimezoneListener($app['request_context'], $app['locale.timezones']);
        });

        $app['locale.formatter.datetime._proto'] = $app->protect(function ($pattern = null) use ($app) {
            $localeWithKeywords = $app['request_context']->getLocaleWithKeywords();
            $isTraditional = (Boolean) strpos($localeWithKeywords, 'calendar');

            return new \IntlDateFormatter(
                $localeWithKeywords,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::NONE,
                date_default_timezone_get(),
                $isTraditional ? \IntlDateFormatter::TRADITIONAL : \IntlDateFormatter::GREGORIAN,
                $pattern
            );
        });

        $app['locale.formatter.date'] = $app->share(function ($app) {
            $dateFormat = null;
            if (isset($app['locale.formats']['date']) && isset($app['locale.formats']['date'][$app['locale']])) {
                $dateFormat = $app['locale.formats']['date'][$app['locale']];
            }

            return $app['locale.formatter.datetime._proto']($dateFormat);
        });

        $app['locale.formatter.year'] = $app->share(function ($app) {
            return $app['locale.formatter.datetime._proto']('yyyy');
        });

        $app['locale.formatter.number'] = $app->share(function ($app) {
            return new \NumberFormatter($app['request_context']->getLocale(), \NumberFormatter::DECIMAL);
        });

        $app['locale.formatter.currency'] = $app->share(function ($app) {
            $formatter = new \NumberFormatter($app['request_context']->getLocale(), \NumberFormatter::DECIMAL);
            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
            $formatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $app['locale.formats']['currency'][$app['locale']][1]);
            $formatter->setPattern($app['locale.formats']['currency'][$app['locale']]);

            return $formatter;
        });

        $app['locale.currency'] = $app->share(function ($app) {
            return $app['locale.formatter.currency']->getSymbol(\NumberFormatter::INTL_CURRENCY_SYMBOL);
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['locale.mapping_listener']);
        $app['dispatcher']->addSubscriber($app['locale.timezone_listener']);
    }
}
