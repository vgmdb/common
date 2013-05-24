<?php

namespace VGMdb\Component\Silex\Tests;

use VGMdb\Component\Silex\Loader\YamlFileLoader;
use Silex\Application;
use Symfony\Component\Config\FileLocator;

/**
 * YamlFileLoader test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class YamlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $loader;
    private $loaderOverride;

    protected function setUp()
    {
        $options = array('parameters' => array());
        $locator = new FileLocator(array(
            __DIR__ . '/Fixtures/Resources/config'
        ));
        $locatorOverride = new FileLocator(array(
            __DIR__ . '/Fixtures/Resources/config/override',
            __DIR__ . '/Fixtures/Resources/config'
        ));
        $this->app = new Application();
        $this->loader = new YamlFileLoader($this->app, $locator, $options);
        $this->loaderOverride = new YamlFileLoader($this->app, $locatorOverride, $options);
    }

    public function testLoadConfig()
    {
        $this->loader->load('config.yml');

        $this->assertEquals('foo', $this->app['baz']);
    }

    public function testConfigImportsFoo()
    {
        $this->loader->load('config.yml');

        $this->assertEquals('baz', $this->app['foo']);
    }

    public function testFooImportsBar()
    {
        $this->loader->load('config.yml');

        $this->assertEquals('foo', $this->app['bar']);
    }

    public function testFooImportsBarWithOverride()
    {
        $this->loaderOverride->load('config.yml');

        $this->assertEquals('baz', $this->app['bar']);
    }

    public function testRegisterServiceWithIdentifier()
    {
        $this->loader->load('config.yml');

        $this->assertEquals('bar', $this->app['test.foo']);
        $this->assertEquals('bar', $this->app['test']);
    }

    public function testLoadFoo()
    {
        $this->loader->load('foo.yml');

        $this->assertEquals('baz', $this->app['test.foo']);
        $this->assertEquals('baz', $this->app['test']);
    }
}
