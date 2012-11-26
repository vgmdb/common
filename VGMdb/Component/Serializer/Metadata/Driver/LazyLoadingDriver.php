<?php

namespace VGMdb\Component\Serializer\Metadata\Driver;

use Silex\Application;
use Metadata\Driver\DriverInterface;

class LazyLoadingDriver implements DriverInterface
{
    private $app;
    private $realDriverId;

    public function __construct(Application $app, $realDriverId)
    {
        $this->app = $app;
        $this->realDriverId = $realDriverId;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        return $this->app[$this->realDriverId]->loadMetadataForClass($class);
    }
}
