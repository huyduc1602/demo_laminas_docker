<?php
namespace Zf\Ext;

use \Laminas\Cache\Storage\Adapter\Redis as RedisAdapter;

class ZFRedisAdapter extends RedisAdapter {
    /**
     * Remove namespace from full key
     * @param string $key
     * @return string
     */
    protected function normalZfRedisKey($key = ''){
        return str_replace($this->namespacePrefix, '', $key);
    }
    /**
     * Get redis key by pattern
     * @param string $prefix
     * @return []
     */
    protected function getKeyByPrefix( $prefix = '' ){
        
        return $this->getRedisResource()
        ->keys($this->namespacePrefix . $prefix . '*' );
    }
        
    /**.
     * @param $prefix
     * @return array
     */
    public function getItemsByPrefix($prefix) {
        
        $keys  = $this->getKeyByPrefix( $prefix );
        
        $arrayItems = [];
        foreach ( $keys as $key) {
            $key = $this->normalZfRedisKey($key);
            $arrayItems[$key] = $this->getItem($key);
        }
        return $arrayItems;
    }

    /**
     * @param string $prefix
     * @return bool
     */
    public function removeItemsByPrefix(string $prefix) {
        
        if ( empty($keys = $this->getKeyByPrefix( $prefix )) ) 
            return false;
        
        foreach ( $keys as $key) {
            $this->removeItem( $this->normalZfRedisKey($key) );
        }
        
        return true;
    }
    
    /**
     * Change TTL of an item
     * @param string $key
     * @param number $ttl
     */
    public function changeTTLOfItemByKey($key, $ttl = 604800 ){
        $this->getRedisResource()->expire($this->namespacePrefix . $key, $ttl);
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
