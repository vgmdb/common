<?php

namespace VGMdb\Component\Serializer\Debug;

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;

/**
 * Serializer proxy class for debug use.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TraceableSerializer implements SerializerInterface
{
    private $serializer;
    private $stopwatch;
    private $logger;

    /**
     * Constructor.
     *
     * @param SerializerInterface $serializer
     * @param Stopwatch           $stopwatch
     * @param LoggerInterface     $logger
     */
    public function __construct(SerializerInterface $serializer, Stopwatch $stopwatch, LoggerInterface $logger = null)
    {
        $this->serializer = $serializer;
        $this->stopwatch = $stopwatch;
        $this->logger = $logger;
    }

    public function setSerializeNull($serializeNull)
    {
        $this->serializer->setSerializeNull($serializeNull);
    }

    public function setExclusionStrategy(ExclusionStrategyInterface $exclusionStrategy = null)
    {
        $this->serializer->setExclusionStrategy($exclusionStrategy);
    }

    public function setVersion($version)
    {
        $this->serializer->setVersion($version);
    }

    public function setGroups($groups)
    {
        $this->serializer->setGroups($groups);
    }

    public function serialize($data, $format)
    {
        $event = $this->stopwatch->start('serialize', 'serializer');

        $result = $this->serializer->serialize($data, $format);

        $event->stop('Serialize');

        if (null !== $this->logger) {
            $time = $event->getDuration();
            $this->logger->info(sprintf('Serialized data to %s format in %sms', $format, $time));
        }

        return $result;
    }

    public function deserialize($data, $type, $format)
    {
        $event = $this->stopwatch->start('deserialize', 'serializer');

        $result = $this->serializer->deserialize($data, $type, $format);

        $event->stop('Deserialize');

        if (null !== $this->logger) {
            $time = $event->getDuration();
            $this->logger->info(sprintf('Deserialized %s from %s format in %sms', $type, $format, $time));
        }

        return $result;
    }
}
