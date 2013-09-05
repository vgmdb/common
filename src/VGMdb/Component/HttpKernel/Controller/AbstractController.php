<?php

namespace VGMdb\Component\HttpKernel\Controller;

use VGMdb\Component\HttpKernel\Controller\Traits\ContainerAwareTrait;
use VGMdb\Component\HttpKernel\Controller\Traits\UrlGeneratorTrait;
use VGMdb\Component\HttpKernel\Controller\Traits\ResponseFactoryTrait;
use VGMdb\Component\HttpKernel\Controller\Traits\HostGeneratorTrait;

/**
 * Base class for application controllers.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractController
{
    use ContainerAwareTrait, UrlGeneratorTrait, ResponseFactoryTrait, HostGeneratorTrait;
}
