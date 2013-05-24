<?php

namespace VGMdb\Component\Silex\Tests\Fixtures;

use Silex\Application;
use Silex\ServiceProviderInterface;

class TestService implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['test'] = $app->share(function ($app) {
            return $app['test.foo'];
        });
    }

    public function boot(Application $app)
    {
    }
}
