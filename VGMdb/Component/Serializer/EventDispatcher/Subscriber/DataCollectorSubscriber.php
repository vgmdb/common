<?php

namespace VGMdb\Component\Serializer\EventDispatcher\Subscriber;

use VGMdb\Component\Doctrine\DataCollector\DoctrineDataCollector;
use VGMdb\Component\Propel\DataCollector\PropelDataCollector;
use Symfony\Component\HttpKernel\DataCollector\TimeDataCollector;
use Symfony\Component\Stopwatch\StopwatchEvent;
use JMS\Serializer\EventDispatcher\Event;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;

class DataCollectorSubscriber implements EventSubscriberInterface
{
    private $stopwatchEvents = array();

    public function onPreSerialize(PreSerializeEvent $event)
    {
        $object = $event->getObject();

        if ($object instanceof TimeDataCollector) {
            $this->stopwatchEvents = $object->getEvents();
        }
    }

    public function onPostSerialize(Event $event)
    {
        $object = $event->getObject();
        $visitor = $event->getVisitor();

        if ($object instanceof StopwatchEvent) {
            $visitor->addData('starttime', $object->getStartTime());
            $visitor->addData('endtime', $object->getEndTime());
            $visitor->addData('duration', $object->getDuration());
            $visitor->addData('memory', sprintf('%.1F', $object->getMemory() / 1024 / 1024));
            if ($name = array_search($object, $this->stopwatchEvents, true)) {
                $visitor->addData('name', $name);
                //unset($this->stopwatchEvents[$name]);
            }
        } elseif ($object instanceof TimeDataCollector) {
            $visitor->addData('duration', sprintf('%.0f', $object->getDuration()));
            $visitor->addData('inittime', sprintf('%.0f', $object->getInitTime()));
        } elseif ($object instanceof DoctrineDataCollector || $object instanceof PropelDataCollector) {
            $visitor->addData('time', sprintf('%.0f', ceil($object->getTime() * 1000)));
            $visitor->addData('querycount', sprintf('%d', $object->getQueryCount()));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.pre_serialize', 'method' => 'onPreSerialize'),
            array('event' => 'serializer.post_serialize', 'method' => 'onPostSerialize'),
        );
    }
}
