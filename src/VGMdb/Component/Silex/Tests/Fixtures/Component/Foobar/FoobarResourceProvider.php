<?php

namespace VGMdb\Component\Silex\Tests\Fixtures\Component\Foobar;

use VGMdb\Component\Silex\AbstractResourceProvider;
use Silex\Application;
use Silex\ServiceProviderInterface;

class FoobarResourceProvider extends AbstractResourceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
    }

    public function boot(Application $app)
    {
    }
}
