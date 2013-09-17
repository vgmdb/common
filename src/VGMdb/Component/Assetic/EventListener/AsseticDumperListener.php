<?php

namespace VGMdb\Component\Assetic\EventListener;

use Silex\Application;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Dumps all lazy asset manager and asset managers assets.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class AsseticDumperListener implements EventSubscriberInterface
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $options = array_replace(
            array(
                'debug' => false,
                'formulae_cache_dir' => null,
                'auto_dump_assets' => true,
            ),
            $this->app['assetic.options']
        );

        if (!$options['auto_dump_assets']) {
            return;
        }

        $this->dumpAssets();
    }

    public function dumpAssets()
    {
        // Boot assetic
        $assetic = $this->app['assetic'];

        foreach ($this->app['assetic.asset_manager']->getNames() as $name) {
            $asset = $this->app['assetic.asset_manager']->get($name);
            $this->app['assetic.asset_writer']->writeAsset($asset);
        }

        foreach ($this->app['assetic.lazy_asset_manager']->getNames() as $name) {
            $asset = $this->app['assetic.lazy_asset_manager']->get($name);
            $formula = $this->app['assetic.lazy_asset_manager']->getFormula($name);
            $this->app['assetic.asset_writer']->writeAsset($asset);

            if (!isset($formula[2])) {
                continue;
            }

            $debug = isset($formula[2]['debug'])
                ? $formula[2]['debug']
                : $this->app['assetic.lazy_asset_manager']->isDebug();

            $combine = isset($formula[2]['combine'])
                ? $formula[2]['combine']
                : null;

            if (null !== $combine ? !$combine : $debug) {
                foreach ($asset as $leaf) {
                    $this->app['assetic.asset_writer']->writeAsset($leaf);
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse', -96)),
        );
    }
}
