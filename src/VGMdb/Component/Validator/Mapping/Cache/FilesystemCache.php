<?php

namespace VGMdb\Component\Validator\Mapping\Cache;

use Symfony\Component\Validator\Mapping\Cache\CacheInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\Common\Cache\FilesystemCache as BaseFilesystemCache;

/**
 * Writes validator metadata to the filesystem cache.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class FilesystemCache implements CacheInterface
{
    private $cache;

    public function __construct(BaseFilesystemCache $cache)
    {
        $this->cache = $cache;
    }

    public function has($class)
    {
        return $this->cache->contains($class);
    }

    public function read($class)
    {
        return $this->cache->fetch($class);
    }

    public function write(ClassMetadata $metadata)
    {
        $this->cache->save($metadata->getClassName(), $metadata);
    }
}
