<?php
namespace Zf\Ext;
use Zf\Ext\LaminasRedisAdapter;
/**
 * Customize Laminas redis cache 
 * @author Jilv006
 */
class LaminasRedisCache {
    protected $_myNamespace = null;
    protected $_myTTL = null;

    public $_throwErrOnCall = false;
    /**
     * @var LaminasRedisAdapter
     */
    protected $_instance = null;
    /**
     * @param LaminasRedisAdapter $adapter
     */
    public function __construct(LaminasRedisAdapter $adapter){
        $this->_instance = $adapter;
        $this->_throwErrOnCall = $adapter->_throwErrOnCall;
    }
    
    public function __call($method, $args) {
        if ( !method_exists($this, $method) ){
            $this->_instance->getOptions()
            // Change time to live
            ->setTtl($this->_myTTL)
            // Change name space
            ->setNamespace($this->_myNamespace);
        }
        try {
            return call_user_func_array([$this->_instance, $method], $args);
        } catch (\Throwable $e){
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

    public function setMyNamespace($namespace){
        $this->_myNamespace = $namespace;
        return $this;
    }

    public function setMyTTL($ttl){
        $this->_myTTL = $ttl;
        return $this;
    }

}