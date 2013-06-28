<?php

namespace VGMdb\Component\Layout\Tests\Fixtures\Widget;

use VGMdb\Application;
use VGMdb\Component\View\Widget;

class FooWidget extends Widget
{
    public function __construct(Application $app)
    {
        $view = $app['view']('test/foo');

        parent::__construct($view);
    }
}
