<?php

namespace VGMdb\Component\Translation\Tests;

use VGMdb\Application;
use VGMdb\Component\Routing\Loader\YamlFileLoader;
use VGMdb\Component\Routing\RequestContext;
use VGMdb\Component\Translation\Routing\TranslationRouter;
use VGMdb\Component\Translation\Routing\TranslationRouteLoader;
use VGMdb\Component\Translation\Routing\RouteExclusionStrategy;
use VGMdb\Component\Translation\Routing\PathGenerationStrategy;
use VGMdb\Component\Translation\Routing\LocaleResolver;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;

/**
 * TranslationRouter test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TranslationRouterTest extends \PHPUnit_Framework_TestCase
{
    private $router;

    protected function setUp()
    {
        $app = new Application(array(
            'debug' => false,
            'cache' => false,
            'env' => 'test',
            'name' => 'test'
        ));

        $translator = new Translator('ms', new MessageSelector());
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', array(
            'home' => '/',
            'sale' => '/properti-jual/{page}',
            'rent' => '/properti-sewa/{page}',
            'listing' => '/listing-properti/{listing_id}'
        ), 'ms', 'routes');
        $translator->addResource('array', array(
            'home' => '/',
            'sale' => '/property-sale/{page}',
            'rent' => '/property-rent/{page}',
            'listing' => '/listing/{listing_id}'
        ), 'en', 'routes');

        $router = new TranslationRouter(
            new YamlFileLoader(new FileLocator(array(__DIR__ . '/Fixtures/routes.yml'))),
            array(__DIR__ . '/Fixtures/routes.yml'),
            array('cache_dir' => null),
            array(),
            new RequestContext(),
            null
        );
        $router->setDefaultLocale('ms');
        $router->setLocaleResolver(new LocaleResolver());
        $router->setTranslationLoader(new TranslationRouteLoader(
            new RouteExclusionStrategy(),
            new PathGenerationStrategy(
                'prefix_except_default',
                $translator,
                array('en', 'ms'),
                null,
                'routes',
                'ms'
            )
        ));

        $this->router = $router;
    }

    protected function tearDown() {}

    public function testMatchTranslatedRoute()
    {
        $params = $this->router->match('/properti-jual/1');

        $this->assertEquals($params['_controller'], 'TestController:sale');
        $this->assertEquals($params['_route'], 'sale');
        $this->assertEquals($params['_locale'], 'ms');
    }

    public function testMatchTranslatedRouteWithLocalePrefix()
    {
        $params = $this->router->match('/en/property-rent/1');

        $this->assertEquals($params['_controller'], 'TestController:rent');
        $this->assertEquals($params['_route'], 'rent');
        $this->assertEquals($params['_locale'], 'en');
    }
}
