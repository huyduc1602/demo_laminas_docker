<?php

namespace GrootSwoole;

use Redis;
use RedisCluster;

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
    /** @var Redis|RedisCluster|null */
    private $redis;
    protected $isCluster = false;

    /**
     * Sets the redis instance to use.
     *
     * @return void
     */
    public function setRedis( $redis)
    {
        $redis->setOption(Redis::OPT_SERIALIZER, $this->getSerializerValue());
        $this->redis = $redis;
    }

    protected function iniRedis()
    {
        $this->isCluster = false;
        $redis = new Redis();
        $redis->connect(REDIS_CONFIG['server']['host'], REDIS_CONFIG['server']['port']);

        if( !empty(REDIS_CONFIG['password']) ) {
            $redis->auth(REDIS_CONFIG['password']);
        }

        $redis->select(REDIS_CONFIG['database'] ?? 0);

        $redis->setOption(Redis::OPT_PREFIX, 'DOCTRINE_CACHE:');

        return $redis;
    }

    protected function iniRedisCluster()
    {
        $this->isCluster = true;

        $redis = new RedisCluster(
            null,
            REDIS_CONFIG['seeds'],
            0,
            0,
            true,
            REDIS_CONFIG['password']
        );

        $redis->setOption(Redis::OPT_PREFIX, (REDIS_CONFIG['lib_options'][Redis::OPT_PREFIX] ?? '') . 'DOCTRINE_CACHE:');

        $redis->setOption(RedisCluster::OPT_SLAVE_FAILOVER, RedisCluster::FAILOVER_ERROR);

        // $redis->multi();

        return $redis;
    }
    /**
     * Gets the redis instance used by the cache.
     *
     * @return Redis|null
     */
    public function getRedis()
    {
        if ($this->redis === null){
            if (isset(REDIS_CONFIG['seeds']))
                $redis = $this->iniRedisCluster();
            else $redis = $this->iniRedis();

            $redis->setOption(Redis::OPT_SERIALIZER, $this->getSerializerValue());
            $this->redis = $redis;
        }

        return $this->redis;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        $rc = $this->getRedis()->get($id);
        return $rc instanceof RedisCluster
        ? $rc->exec()
        : $rc ;
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
                $existItems = [];
                foreach ($keysToFilter as $key) {
                    $existItems[$key] = $this->redis->exists($key);
                }
                $existItems = array_filter($existItems);
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
            if ($lifetime){
                $total = 0;
                foreach ($keysAndValues as $key => $val){
                    $total += ($this->redis->setex($key, $lifetime, $val) ? 1 : 0);
                }
                return $total == count($keysAndValues);
            }
            else{
                $total = 0;
                foreach ($keysAndValues as $key => $val){
                    $total += ($this->redis->set($key, $val) ? 1 : 0);
                }
                return $total == count($keysAndValues);
            }
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
        $exists = $this->getRedis()->exists($id);

        if (is_bool($exists)) return $exists;

        return $exists instanceof RedisCluster
            ? ($exists->exec() > 0)
            : ($exists > 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 0) {
            $rc = $this->getRedis()->setex($id, $lifeTime, $data);
        }else $rc = $this->getRedis()->set($id, $data);

        return $rc instanceof RedisCluster
            ? $rc->exec()
            : $rc;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        $rc = $this->getRedis()->del($id);
        return $rc instanceof RedisCluster
            ? ($rc->exec() > 0)
            : ($rc >= 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDeleteMultiple(array $keys)
    {
        $rc = $this->getRedis()->del($keys);
        return $rc instanceof RedisCluster
            ? ($rc->exec() > 0)
            : ($rc >= 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return $this->getRedis()->flushDB();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        $info = $this->getRedis()->info();

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
