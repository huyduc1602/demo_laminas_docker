<?php


namespace GrootSwoole;

// use Laminas\Cache\StorageFactory;

abstract class BaseTaskEvent
{
    /**
     * @var bool
     */
    public $deleteCacheProcess = true;
    /**
     * @var bool
     */
    public $workerState = null;

    /**
     * @var string
     */
    public $process_key;
    /**
     * Contructor parameters
     * @var array
     */
    protected $_params = [];

    public static function _getParamkeys() {
        return [];
    }
    /**
     * Request parameters
     * @var array
     */
    protected $_reqInfo = [];
    
    public function __construct($params = [], $rqInfo = []){
        $this->_params = $params;

        $this->_reqInfo= $rqInfo;
        $this->_reqInfo['params'] = $params;
        if ( !empty($params['process_key']) ){
            $this->setProcessKey($params['process_key']);
        }
    }
    
    /**
     * Get input parameters
     * @return array
     */
    public function getArrayValues($key = null){
        return $key ? ($this->_params[$key] ?? null) : $this->_params;
    }
    
    /**
     * @param string $key
     * @return mixed|null
     */
    public function getClassProperties(string $key = '')
    {
        if (empty($key))
            return null;

        return $this->{$key} ?? null;
    }

    /**
     * @param string $key
     *
     * @author Cuongnp - 3/22/2021
     */
    public function setProcessKey(string $key) {
        $this->process_key = $key;
    }

    /**
     * @return string
     * @author Cuongnp - 3/22/2021
     */
    public function getProcessKey() {
        return $this->process_key ?? '';
    }
    /**
     * @param bool $state
     * @return $this
     */
    public function setWorkerState(bool $state)
    {
        $this->workerState = $state;
        return $this;
    }

    /**
     * @param bool $state
     * @return $this
     */
    public function setDeleteCacheProcessState(bool $state)
    {
        $this->deleteCacheProcess = $state;
        return $this;
    }
    
    protected $_redisCache = null;
    
    public function setRedisCache ($redis){
        $this->_redisCache = $redis;
    }

    protected function getRedisCache(){
        return $this->_redisCache;
    }
    /**
     * Get current data in cache
     * @return array
     */
    public function getProcessDataFromCache($key = null){
        $key = $key ?? $this->process_key;
        if ( $key )
            return $this->getRedisCache()->getItem($key) ?? [];
        return [];
    }
    /**
     * Get current data in cache
     * @return bool
     */
    public function delProcessDataFromCache($key = null){
        $key = $key ?? $this->process_key;
        if ( $key )
            return $this->getRedisCache()->removeItem($key) ?? [];
        return false;
    }
    /**
     * set current data to cache
     * @param array $data
     * @param string $key
     * @return bool
     */
    public function appendProcessDatatoCache($data, $key = null){
        $key = $key ?? $this->process_key;
        return $this->getRedisCache()->setItem($key, array_replace(
            $this->getRedisCache()->getItem($key) ?? [],
            $data
        ));
    }
    
    /**
     * Destruct task
     */
    /* public function __destruct()
    {
        if( null !== $this->workerState ) {
            try {
                $processKey = $this->process_key ?? 'redis_process_key_' . time();
                
                if($this->workerState && $this->deleteCacheProcess) {
                    $this->getRedisCache()->removeItem($processKey);
                }else{
                    $cacheProcessData = $this->getRedisCache()->getItem($processKey) ?? [];
            
                    $cacheProcessData['task-worker'] = [
                        'status' => $this->workerState, 'time' => time()
                    ];
                    $this->getRedisCache()->setItem($processKey, $cacheProcessData);
                    
                    unset($cacheProcessData);
                }
            } catch (\Throwable $e) {
                $className = self::class;
                $date = gmdate('d/F/Y:H:i:s O', time());
                $msg = escapeshellarg("Destruct task ({$className}) got an error: {$e->getMessage()}, line: {$e->getLine()}, file: {$e->getFile()}");
                fwrite(STDOUT, "127.0.0.1 - - [{$date}] \"DEBUG {$msg}\" HTTP/1.1 500 - -" . PHP_EOL);
            }
        }
    } */
}