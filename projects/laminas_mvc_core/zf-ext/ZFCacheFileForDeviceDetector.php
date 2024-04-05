<?php
namespace Zf\Ext;

use Laminas\Cache\Storage\Adapter\Filesystem;

class ZFCacheFileForDeviceDetector implements \DeviceDetector\Cache\CacheInterface {
    /**
     * @var Filesystem
     */
    private $cache;
    
    /**
     * @param Filesystem $cache
     */
    public function __construct(Filesystem $cache)
    {
        $this->cache = $cache;
    }
    
    /**
     * @inheritDoc
     */
    public function fetch(string $id)
    {
        $data = $this->cache->getItem($id);
        
        if ($data === null ) return null;
        
        $dataDecode = @unserialize($data);
        
        return ($dataDecode === false ? $data : $dataDecode);
    }
    
    /**
     * @inheritDoc
     */
    public function contains(string $id): bool
    {
        return $this->cache->hasItem($id);
    }
    
    /**
     * @inheritDoc
     */
    public function save(string $id, $data, int $lifeTime = 0): bool
    {
        return $this->cache->setItem($id, is_string($data) ? $data : serialize($data));
    }
    
    /**
     * @inheritDoc
     */
    public function delete(string $id): bool
    {
        return $this->cache->removeItem($id);
    }
    
    /**
     * @inheritDoc
     */
    public function flushAll(): bool
    {
        return $this->cache->flush();
    }
}
