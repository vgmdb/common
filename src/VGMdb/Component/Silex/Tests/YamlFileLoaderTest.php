<?php

namespace VGMdb\Component\Silex\Tests;

use VGMdb\Component\Silex\Loader\YamlFileLoader;
use VGMdb\Component\Silex\Loader\Pass\DatabasePass;
use VGMdb\Component\Silex\Loader\Pass\QueuePass;
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
    private $databasePass;
    private $queuePass;

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
        $this->databasePass = new DatabasePass();
        $this->queuePass = new QueuePass();
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

    public function testDatabasePass()
    {
        $this->loader->addConfigPass($this->databasePass);
        $this->loader->load('database.yml');

        $propel1Options = $this->app['propel1.options'];
        $propel1DataSource = array(
            'default' => 'foo',
            'foo' => array(
                'adapter' => 'mysql',
                'connection' => array(
                    'dsn' => 'mysql:host=foo.example.org;charset=UTF8;',
                    'classname' => null,
                    'user' => 'user',
                    'password' => 'password'
                )
            ),
            'bar' => array(
                'adapter' => 'mysql',
                'connection' => array(
                    'dsn' => 'mysql:host=bar.example.org;port=3333;charset=UTF8;',
                    'classname' => null,
                    'user' => null,
                    'password' => null
                )
            ),
            'profiler' => array(
                'adapter' => 'sqlite',
                'connection' => array(
                    'dsn' => 'sqlite:host=127.0.0.1;charset=UTF8;',
                    'classname' => null,
                    'user' => null,
                    'password' => null
                )
            )
        );
        $this->assertEquals($propel1DataSource, $propel1Options['propel']['datasources']);

        $propelOptions = $this->app['propel.options'];
        $propelDataSource = array(
            'foo' => array(
                'driver' => 'mysql',
                'dsn' => 'mysql:host=foo.example.org;charset=UTF8;',
                'classname' => null,
                'user' => 'user',
                'password' => 'password'
            ),
            'bar' => array(
                'driver' => 'mysql',
                'dsn' => 'mysql:host=bar.example.org;port=3333;charset=UTF8;',
                'classname' => null,
                'user' => null,
                'password' => null
            ),
            'profiler' => array(
                'driver' => 'sqlite',
                'dsn' => 'sqlite:host=127.0.0.1;charset=UTF8;',
                'classname' => null,
                'user' => null,
                'password' => null
            )
        );
        $this->assertEquals($propelDataSource, $propelOptions['dbal']['connections']);

        $doctrineOptions = $this->app['doctrine.dbs.options'];
        $doctrineDataSource = array(
            'foo' => array(
                'driver' => 'pdo_mysql',
                'user' => 'user',
                'password' => 'password',
                'host' => 'foo.example.org',
                'port' => 3306,
                'dbname' => null,
                'charset' => 'UTF8',
                'path' => null,
                'memory' => null
            ),
            'bar' => array(
                'driver' => 'pdo_mysql',
                'user' => null,
                'password' => null,
                'host' => 'bar.example.org',
                'port' => 3333,
                'dbname' => null,
                'charset' => 'UTF8',
                'path' => null,
                'memory' => null
            ),
            'profiler' => array(
                'driver' => 'pdo_sqlite',
                'user' => null,
                'password' => null,
                'host' => '127.0.0.1',
                'port' => 3306,
                'dbname' => null,
                'charset' => 'UTF8',
                'path' => '/tmp',
                'memory' => null
            )
        );
        $this->assertEquals($doctrineDataSource, $doctrineOptions);
    }

    public function testQueuePass()
    {
        $this->loader->addConfigPass($this->queuePass);
        $this->loader->load('queue.yml');

        $queueConfigs = $this->app['queue.configs'];
        $queueSources = array(
            'foo' => array(
                'provider' => 'AmazonSQS',
                'options' => array(
                    'queue' => 'https://foo.example.org',
                    'sqs_options' => array(
                        'key' => 'foo',
                        'secret' => 'bar'
                    )
                )
            ),
            'bar' => array(
                'provider' => 'AmazonSQS',
                'options' => array(
                    'queue' => 'https://bar.example.org',
                    'sqs_options' => array(
                        'key' => 'bar',
                        'secret' => 'baz'
                    )
                )
            )
        );
        $this->assertEquals($queueSources, $queueConfigs);
    }
}
