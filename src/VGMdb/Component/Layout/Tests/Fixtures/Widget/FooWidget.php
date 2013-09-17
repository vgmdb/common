<?php

namespace VGMdb\Component\Layout\Tests\Fixtures\Widget;

use VGMdb\Component\View\Widget;
use Silex\Application;

class FooWidget extends Widget
{
    public function __construct(Application $app)
    {
        $view = $app['view']('test/foo');

        parent::__construct($view);
    }
}
