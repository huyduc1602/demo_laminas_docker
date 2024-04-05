<?php

namespace Zf\Ext\Model;

use RedisCluster;
use Redis;

use function array_combine;
use function array_diff_key;
use function array_fill_keys;
use function array_filter;
use function array_keys;
use function count;
use function defined;
use function extension_loaded;
use function is_bool;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
/**
 * Redis cache provider.
 *
 * @link   www.doctrine-project.org
 */
class DoctrineRedisCache extends CacheProvider
{
    protected $isCluster = false;
    /** @var Redis| RedisCluster| null */
    private $redis;

    /**
     * @param Redis | RedisCluster $redis
     */
    public function __construct($redis){
        $this->redis = $redis;
        $this->isCluster = $redis instanceof RedisCluster;
    }

    /**
     * Sets the redis instance to use.
     *
     * @return void
     */
    public function setRedis($redis)
    {
        $redis->setOption(Redis::OPT_SERIALIZER, $this->getSerializerValue());
        $this->redis = $redis;
    }

    /**
     * Gets the redis instance used by the cache.
     *
     * @return Redis|RedisCluster|null
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        $data = $this->redis->get($id);
        return ($data instanceof RedisCluster) ? false : $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetchMultiple(array $keys)
    {
        if ($this->isCluster) {
            $fetchedItems = [];
            foreach ($keys as $key) {
                $fetchedItems[$key] = $this->redis->get($key);
            }
            $fetchedItems = array_combine($keys, $fetchedItems);
        }else $fetchedItems = array_combine($keys, $this->redis->mget($keys));

        // Redis mget returns false for keys that do not exist. So we need to filter those out unless it's the real data.
        $keysToFilter = array_keys(array_filter($fetchedItems, static function ($item): bool {
            return $item === false;
        }));

        if ($keysToFilter) {
            if ($this->isCluster) {
                try{
                    foreach ($keysToFilter as $key) {
                        $this->redis->exists($key);
                    }
                    $existItems = array_filter($this->redis->exec());
                }catch(\Throwable $e){

                    $existItems = [];
                    foreach ($keysToFilter as $key) {
                        $existItems[$key] = $this->redis->exists($key);
                    }
                    $existItems = array_filter($existItems);
                }
            }else {
                $multi = $this->redis->multi(Redis::PIPELINE);
                foreach ($keysToFilter as $key) {
                    $multi->exists($key);
                }
                $existItems = array_filter($multi->exec());
            }

            $missedItemKeys = array_diff_key($keysToFilter, $existItems);
            $fetchedItems   = array_diff_key($fetchedItems, array_fill_keys($missedItemKeys, true));
        }

        return $fetchedItems;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0)
    {
        if ($this->isCluster) {
            $total = 0;
            if ($lifetime){
                foreach ($keysAndValues as $key => $val){
                    $total += ($this->redis->setex($key, $lifetime, $val) ? 1 : 0);
                }
            }
            else{
                foreach ($keysAndValues as $key => $val){
                    $total += ($this->redis->set($key, $val) ? 1 : 0);
                }
            }

            return $total == count($keysAndValues);
        }
        else {
            if ($lifetime) {
                // Keys have lifetime, use SETEX for each of them
                $multi = $this->redis->multi(Redis::PIPELINE);
                foreach ($keysAndValues as $key => $value) {
                    $multi->setex($key, $lifetime, $value);
                }

                $succeeded = array_filter($multi->exec());

                return count($succeeded) == count($keysAndValues);
            }

            // No lifetime, use MSET
            return (bool)$this->redis->mset($keysAndValues);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        $exists = $this->redis->exists($id);

        if (is_bool($exists)) {
            return $exists;
        }

        return is_numeric($exists) ? ($exists > 0) : false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ( $this->isCluster ) {

            if ($lifeTime > 0) {
                $rc = $this->redis->setex($id, $lifeTime, $data);
                return is_bool($rc) ? $rc : false;
            }

            $rc = $this->redis->set($id, $data);
            return is_bool($rc) ? $rc : false;
        }

        if ($lifeTime > 0)
            return $this->redis->setex($id, $lifeTime, $data);
        return $this->redis->set($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        $isDel = $this->redis->del($id);
        if( is_bool($isDel) ) return $isDel;

        return is_numeric($isDel) ? ($isDel >= 0) : false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDeleteMultiple(array $keys)
    {
        $isDel = $this->redis->del($keys);
        if( is_bool($isDel) ) return $isDel;

        return is_numeric($isDel) ? ($isDel >= 0) : false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return $this->redis->flushDB();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        $info = $this->redis->info();

        return [
            Cache::STATS_HITS   => $info['keyspace_hits'],
            Cache::STATS_MISSES => $info['keyspace_misses'],
            Cache::STATS_UPTIME => $info['uptime_in_seconds'],
            Cache::STATS_MEMORY_USAGE      => $info['used_memory'],
            Cache::STATS_MEMORY_AVAILABLE  => false,
        ];
    }

    /**
     * Returns the serializer constant to use. If Redis is compiled with
     * igbinary support, that is used. Otherwise the default PHP serializer is
     * used.
     *
     * @return int One of the Redis::SERIALIZER_* constants
     */
    protected function getSerializerValue()
    {
        if (defined('Redis::SERIALIZER_IGBINARY') && extension_loaded('igbinary')) {
            return Redis::SERIALIZER_IGBINARY;
        }

        return Redis::SERIALIZER_PHP;
    }
}
