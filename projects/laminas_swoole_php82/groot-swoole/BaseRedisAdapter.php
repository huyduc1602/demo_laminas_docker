<?php
namespace GrootSwoole;

use Laminas\Cache\Storage\Adapter\Redis as RedisAdapter;

class BaseRedisAdapter extends RedisAdapter {

    /**
     * @param $prefix
     * @return array
     */
    public function getItemsByPrefix($prefix) {
        if (empty($prefix)) return [];

        $redis = $this->getRedisResource();
        $namepace = $this->namespacePrefix;
        $arrayItems = [];
        foreach ( $redis->keys($namepace.$prefix.'*' ) as $key) {
            $arrayItems[$key] = $this->getItem(str_replace($namepace, '', $key));
        }
        return $arrayItems;
    }
    /**
     * Explicitly disconnect
     */
    public function closeConnection(){
        $this->getRedisResource()->close();
        $this->getRedisResource()->__destruct();
    }
    
    /**
     * Is connected
     * @return bool
     */
    public function isConnected(){
        return $this->getRedisResource()->isConnected();
    }
    
    public function getRedisResourceId(){
        return $this->resourceId;
    }
    
    public function getConnId(){
        return $this->getRedisResource()->client('id');
    }
}