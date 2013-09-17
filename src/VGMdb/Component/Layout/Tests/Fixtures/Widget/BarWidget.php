<?php

namespace VGMdb\Component\Layout\Tests\Fixtures\Widget;

use VGMdb\Component\View\Widget;
use Silex\Application;

class BarWidget extends Widget
{
    public function __construct(Application $app)
    {
        $view = $app['view']('test/bar');

        parent::__construct($view);
    }
}
