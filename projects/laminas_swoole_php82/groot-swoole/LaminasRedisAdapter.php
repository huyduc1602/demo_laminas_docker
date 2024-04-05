<?php
namespace GrootSwoole;

use \Laminas\Cache\Storage\Adapter\Redis as RedisCache;
use \Laminas\Cache\Storage\Adapter\RedisCluster as RedisClusterCache;
use \Laminas\Cache\Storage\Adapter\RedisClusterResourceManager;

/**
 * Customize Laminas redis cache
 * @author Jilv006
 */
class LaminasRedisAdapter{
    public $_throwErrOnCall = false;
    /**
     * @var RedisCache
     */
    protected $_instance = null;

    protected $_isCluster = false;
    /**
     * @param RedisCache | RedisClusterCache $cache
     */
    public function __construct($cache, $throwErrOnCall = false){
        $this->_instance = $cache;
        $this->_throwErrOnCall = $throwErrOnCall;

        $this->_isCluster = $this->_instance instanceof RedisClusterCache;

        if( $this->_isCluster ){
            $this->setClusterResourceManager(
                new RedisClusterResourceManager($this->_instance->getOptions())
            );
        }
    }

    protected $rdResource = null;

    public function setClusterResourceManager($resourceMn){
        $this->rdResource = $resourceMn;
        $this->_instance->setResourceManager($resourceMn);
    }

    public function __call($method, $args) {
        try {
            return call_user_func_array([$this->_instance, $method], $args);
        } catch (\Throwable $e) {
            if ($this->_throwErrOnCall) throw $e;
        }
        return null;
    }

    public function __get($key) {
        return $this->_instance->$key;
    }

    public function __set($key, $val) {
        return $this->_instance->$key = $val;
    }
    /**
     * Get name space prefix
     * @return string
     */
    public function getNamespacePrefix(){
        if( $this->_isCluster )
            return REDIS_CONFIG['lib_options'][\Redis::OPT_PREFIX];

        return $this->_instance->getOptions()->getNamespace()
            . $this->_instance->getOptions()->getNamespaceSeparator();
    }
    /**
     * @return \Redis
     */
    protected function getRedisResource(){
        if ( $this->_isCluster ) return $this->rdResource->getResource();

        return $this->_instance->getOptions()->getResourceManager()->getResource(
            $this->_instance->getOptions()->getResourceId()
        );
    }

    /**
     * Remove namespace from full key
     * @param string $key
     * @return string
     */
    protected function normalZfRedisKey($key = ''){
        return str_replace($this->getNamespacePrefix(), '', $key);
    }
    /**
     * Get redis key by pattern
     * @param string $prefix
     * @return []
     */
    protected function getKeyByPrefix( $prefix = '' ){
        if ( $this->_isCluster ) return $this->getRedisResource()
            ->keys("{$prefix}*");

        return $this->getRedisResource()
            ->keys($this->getNamespacePrefix() . $prefix . '*' );
    }
    public function clearByNamespace($namespace = null)
    {
        if ( $this->_isCluster ) return $this->removeItemsByPrefix($namespace);

        return $this->_instance->clearByNamespace($namespace);
    }

    /**
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
        if ( $this->_isCluster ) return $this->clearByPrefix($prefix);

        if ( empty($keys = $this->getKeyByPrefix( $prefix )) )
            return false;

        if ( $this->_isCluster ){
            foreach ( $keys as $idx => $key){
                $keys[$idx] = $this->normalZfRedisKey($key);
            }
            return $this->getRedisResource()->del($keys) == ($idx ?? 0);
        }

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
        $this->getRedisResource()->expire($this->getNamespacePrefix() . $key, $ttl);
    }

    /**
     * Touch item
     * @param string $key
     * @return boolean
     */
    public function touchItemIfExists($key){
        if ($this->_instance->hasItem($key)){
            $this->_instance->touchItem($key);
            return true;
        }
        return false;
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
