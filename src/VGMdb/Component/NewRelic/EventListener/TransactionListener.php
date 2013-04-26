<?php

namespace VGMdb\Component\NewRelic\EventListener;

use VGMdb\Component\NewRelic\MonitorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sets the transaction name based on the controller and action.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TransactionListener implements EventSubscriberInterface
{
    protected $monitor;

    public function __construct(MonitorInterface $monitor)
    {
        $this->monitor = $monitor;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if ($event->getRequest()->getRequestFormat() !== 'html') {
            $this->monitor->disableAutoRum();
        }

        $this->monitor->setTransactionName($this->getTransactionName($event));
    }

    protected function getTransactionName(FilterControllerEvent $event)
    {
        $controller = $event->getRequest()->attributes->get('_controller');
        if (is_string($controller) && 2 === $count = substr_count($controller, ':')) {
            return $controller;
        }

        $controller = $event->getController();
        if ($controller instanceof \Closure) {
            try {
                $r = new \ReflectionFunction($controller);
                $file = basename($r->getFileName(), '.php');

                return $file.':{closure}';
            } catch (\ReflectionException $e) {
                return 'Unknown:{closure}';
            }
        } elseif (is_array($controller) || (is_object($controller) && is_callable($controller))) {
            if (!is_array($controller)) {
                $controller = array($controller, '__invoke');
            }
            $class = is_object($controller[0]) ? get_class($controller[0]) : $controller[0];
            $class = basename(str_replace('\\', '/', preg_replace('/Controller$/', '', $class)));
            $action = preg_replace('/Action$/', '', $controller[1]);

            return $class.':'.$action;
        }

        return 'Unknown';
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array(array('onKernelController', -1))
        );
    }
}
