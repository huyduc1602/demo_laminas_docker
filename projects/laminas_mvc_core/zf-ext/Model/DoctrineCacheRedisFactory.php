<?php

namespace Zf\Ext\Model;

use Psr\Container\ContainerInterface;
use Redis;
use RedisCluster;

class DoctrineCacheRedisFactory {
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {

        if (isset(REDIS_CONFIG['seeds']))
            $redis = $this->iniRedisCluster();
        else $redis = $this->iniRedis();

        return new DoctrineRedisCache($redis);
    }

    protected function iniRedis()
    {
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
        $redis = new RedisCluster(
            NULL,
            REDIS_CONFIG['seeds'] ?? [],
            REDIS_CONFIG['timeout'] ?? 4,
			REDIS_CONFIG['read_timeout'] ?? 10,
            true,
            REDIS_CONFIG['password'] ?? null
        );

        $redis->setOption(Redis::OPT_PREFIX, (REDIS_CONFIG['lib_options'][Redis::OPT_PREFIX] ?? '') . 'DOCTRINE_CACHE:');

        $redis->setOption(RedisCluster::OPT_SLAVE_FAILOVER, RedisCluster::FAILOVER_NONE);
		
		// Fix bug: Unable to enter MULTI mode on requested slot
        // $redis->multi();

        return $redis;
    }
}
?>