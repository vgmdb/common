<?php

namespace VGMdb\Component\Layout\Tests;

use VGMdb\Application;
use VGMdb\Component\Config\ConfigLoader;
use VGMdb\Component\View\View;
use VGMdb\Component\View\Engine\MustacheView;
use VGMdb\Component\Layout\Layout;

/**
 * Layout test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class LayoutTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $loader;

    protected function setUp()
    {
        $this->app = new Application();

        $this->app['layout.replacements'] = array();
        $this->app['layout.data'] = array();
        $this->app['layout.default_data'] = array();
        $this->app['layout.filters'] = array();

        $templateLoader = new \Mustache_Loader_FilesystemLoader(
            __DIR__ . '/Fixtures/Resources/views',
            array('extension' => 'ms')
        );

        $this->app['mustache'] = $mustache = new \Mustache_Engine(array(
            'loader' => $templateLoader,
            'partials_loader' => $templateLoader
        ));

        $this->app['view'] = $this->app->protect(function ($template, $data = array(), $type = null) use ($mustache) {
            if (!is_array($data)) {
                $data = array('content' => $data);
            }

            return new MustacheView($template, $data, $mustache);
        });

        $this->loader = new ConfigLoader(array(
            'base_dirs' => __DIR__ . '/Fixtures',
            'files' => 'Resources/config/layout/*.yml',
            'parameters' => array()
        ));

        $this->app->boot();
    }

    public function testLoadLayoutConfig()
    {
        $config = array();
        $config = $this->loader->load($config);

        $this->assertEquals(array(
            'layouts' => array(
                'test' => array(
                    'layout' => array(
                        'template' => 'test/home',
                        'data' => array(
                            'header' => array(
                                'title' => 'Test Title'
                            )
                        ),
                        'widgets' => array(
                            'foo' => array(
                                'widget' => 'VGMdb\Component\Layout\Tests\Fixtures\Widget\FooWidget',
                                'data' => array(
                                    'foo' => 'bar'
                                )
                            )
                        ),
                        'views' => array(
                            'bar' => array(
                                'template' => 'test/bar',
                                'data' => array(
                                    'bar' => 'baz'
                                )
                            )
                        )
                    ),
                    'views' => array(
                        'foo' => array(
                            'template' => 'test/foo',
                            'data' => array(
                                'foo' => 'baz'
                            )
                        )
                    ),
                    'widgets' => array(
                        'bar' => array(
                            'widget' => 'VGMdb\Component\Layout\Tests\Fixtures\Widget\BarWidget',
                            'data' => array(
                                'bar' => 'foo',
                                'no_footer' => true
                            )
                        )
                    )
                )
            )
        ), $config);
    }

    public function testWrapLayout()
    {
        $config = array();
        $config = $this->loader->load($config);
        $view = $this->app['view']('test/content');
        $layout = new Layout($this->app, $config['layouts']['test']);
        $view = $layout->wrap($view);

        $this->assertEquals(<<<EOF
<html>
<head>
<title>Test Title</title>
</head>
<body>
<span>bar</span>
<div>
<span>baz</span></div>
<div>foo</div>
<footer>baz</footer>
</body>
</html>
EOF
        , $view->render());
    }

    protected function tearDown()
    {
    }
}
