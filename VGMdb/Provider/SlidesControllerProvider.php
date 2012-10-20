<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * @brief       Provides presentation slides!
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class SlidesControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $slides = $app['controllers_factory'];

        $slides->get('/slides', function () use ($app) {
            return $app['view']('slides', array(
                'slides' => array(
                    array(
                        'title' => 'Silex',
                        'subtitle' => 'A framework that works',
                        'content' => array(
                            'I totally made this on my return flight'
                        )
                    ),
                    array(
                        'caption' => 'What is it?',
                        'content' => array(
                            'It\'s not a big framework like Zend, CakePHP, Symfony2, Yii or Laravel',
                            'It\'s a microframework built mostly upon Symfony components',
                            'In fact it\'s more of a collection of libraries lightly coupled together'
                        )
                    ),
                    array(
                        'caption' => 'What\'s in it?',
                        'content' => array(
                            '<b>Application</b>: facade that proxies config and execution to other components',
                            '<b>Request</b>: header, param, cookie, session handling',
                            '<b>Response</b>: header, mimetype, charset, exceptions',
                            '<b>Routing</b>: Route matching and route handlers',
                            '<b>Events</b>: dispatcher and listeners, the core mechanism for the HTTP kernel',
                            '<b>Kernel</b>: insert Request, receive Response'
                        )
                    ),
                    array(
                        'caption' => 'Versatile',
                        'content' => array(
                            'Can be used just like any 3rd party vendor library',
                            'Manage packages using Composer',
                            'Doesn\'t prescribe directory structure, or even controller and view handling mechanisms',
                            'Event hooks are provided in the request and response flow'
                        )
                    ),
                    array(
                        'caption' => 'Extensible',
                        'content' => array(
                            'The Silex core is based off Pimple, a general-purpose Dependency Injection container',
                            'Basically an array-like object that provides storage for configuration parameters and service factories',
                            'Trivial to inject functionality or modify existing behaviour, due to clever usage of lazy evaluation',
                            'Provider classes used to integrate other libraries for database access, logging, etc'
                        )
                    ),
                    array(
                        'caption' => 'Robust',
                        'content' => array(
                            'Continuous integration with proper development guidelines and community oversight',
                            'Conforms strictly to standards (PSR-0/1/2)',
                            'Documented API, backwards compatibility is enforced',
                            'Widespread adoption (of Symfony components) ensures the code sees production usage in all sorts of environments'
                        )
                    ),
                    array(
                        'caption' => 'Hello World',
                        'content' => array('
<pre>

    $app = new Silex\Application();
    $app->get(\'/\', function() {
        return \'Hello World!\';
    });
    $app->run(Silex\Request::createFromGlobals());

</pre>
                            ',
                            'Notice that run() accepts a Request object',
                            'This means I can pass in my own implementation, such as a mock or stub object',
                            '<b>TESTABILITY!</b>'
                        )
                    ),
                    array(
                        'sections' => array(
                            array(
                                'caption' => 'Where are my views?',
                                'content' => array(
                                    'Silex doesn\'t come with a default view layer',
                                    'Plug in a template rendering engine of your choice (such as Mustache.php)',
                                    'However we still need a data structure to compose, organize and manipulate templates and bind data',
                                    'Created a custom View / Widget system, inspired by Laravel\'s templating system as well as Drupal\'s render arrays'
                                )
                            ),
                            array(
                                'caption' => 'Views',
                                'content' => array(
                                    'A generic class for storing and binding data to templates',
                                    'Extends ArrayObject, behaves like a regular array',
                                    'Rendering engine can be supplied as a callback in the view factory',
                                    'Alternatively, extend the class with a specific engine, e.g. MustacheView',
                                    '
<pre>

    // Binding data
    $view[\'username\'] = $username;
    $view->with($data = array(\'foo\' => \'bar\'));

</pre>
                                    '
                                )
                            ),
                            array(
                                'caption' => 'Layouts',
                                'content' => array(
                                    'Simply a view with a placeholder for $content',
                                    'You <b>wrap</b> your views with a layout in the route',
                                    '
<pre>

    /**
     * layouts/default.ms
     * &lt;html&gt;&lt;body&gt;{{{content}}}&lt;/body&gt;&lt;/html&gt;
     */

    $layout = new MustacheView(\'layouts/default\');

    $app->get(\'/\', function () {
        return \'Hello World!\';
    })->layout($layout);

</pre>
                                    '
                                )
                            ),
                            array(
                                'caption' => 'Widgets',
                                'content' => array(
                                    'A view that relies on a callback or predefined function for its own data',
                                    'Data is only retrieved upon rendering the template',
                                    'Kind of a mini-controller with its own logic and rendered output',
                                    'Can be lazy loaded using pull (AJAX) or push (websockets)',
                                    'Example usage: sidebar blocks such as related searches, top ten lists, latest articles'
                                )
                            )
                        )
                    ),
                )
            ));
        })->layout($app['view']('layouts/presentation'));

        return $slides;
    }
}