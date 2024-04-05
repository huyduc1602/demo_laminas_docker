<?php
namespace GrootSwoole;

use Doctrine\ORM\EntityManager;

/**
 * Class BaseTaskEventListener
 *
 * @author  Cuongnp - 3/10/2021
 * @package GrootSwoole
 */
abstract class BaseTaskEventListener
{
    use BaseUtil;
    
    /**
     * @var array
     */
    protected $_dtConfigs;
    /**
     * @var array
     */
    protected $_dtFullConfigs;
    /**
     * @var array
     */
    protected $_cacheCfg;
    /**
     * @var array
     */
    protected $_kafkaConfig;
    /**
     * @var array
     */
    protected $_esearchConfig;

    /**
     * @var EntityManager
     */
    protected $_entityMnCache = null;


    protected $_kafkaManager = [];
    /**
     * BaseMailService constructor.
     *
     * @param $entityManager
     * @param $redisConfigs
     * @param $kafkaConfig
     * @param $entitymanager
     */
    public function __construct($doctrineConfigs, $redisConfigs, $kafkaConfig, $entitymanager)
    {
        $this->_dtFullConfigs   = $doctrineConfigs;
        $this->_dtConfigs       = $doctrineConfigs['connection']['orm_default']['params'] ?? [];
        $this->_cacheCfg        = $redisConfigs;
        $this->_kafkaConfig     = $kafkaConfig;
        $this->_entityMnCache   = $entitymanager;
        
        $this->redisCacheForTask= $this->getCache(
            'KAFKA_MESSAGE', 
            defined('KAFKA_REDIS_LIFETIME') ? KAFKA_REDIS_LIFETIME : 3600
        );
        
        $this->childConstruct();
        
    }

    /**
     * For child class
     */
    protected function childConstruct(){}
    
    protected $redisCacheForTask = null;

    protected function pingEvt($task){
        fwrite(STDOUT, "call pingEvt \n");
        sleep(1);
    }
    /**
     * Proccessing an task 
     */
    public function __invoke ($task ){

        try{
            // $task->setRedisCache($this->redisCacheForTask);
            if ('YES' === $task->getArrayValues('is_ping')) {
                $task->setDeleteCacheProcessState(false);
                $this->pingEvt($task);
            } else{
                try{
                    $this->getEntityManager(false);
                } catch (\Throwable $ee) {
                    $date = gmdate('d/M/Y:H:i:s O', time());
                    $msg = urldecode( mb_substr($ee->getMessage(), 0, 100) );
                    fwrite(STDOUT, "127.0.0.1 - - [{$date}] \"POST /doctrine-error?msg={$msg} HTTP/1.1\" 500 1\n");
                }               
                $this->invoke($task);
            }
            
            $task->setWorkerState(true);
            
        } catch (\Throwable $e) {
            $this->saveParseError(
                $e, $task->getClassProperties('_reqInfo') ?? []
            );
            $task->setWorkerState(false);
            
            $this->logMsg("Error: {$e->getMessage()}, Line: {$e->getLine()}, File: {$e->getFile()}");
        }
        
        $this->destructTask($task);
        
        // $this->clearCacheEntityMn();
    }
    /**
     * Destruct task
     */
    protected function destructTask($task)
    {
        if( null !== $task->workerState ) {
            try {
                $processKey = $task->process_key ?? 'redis_process_key_' . time();
                
                if($task->workerState && $task->deleteCacheProcess) {
                    $this->redisCacheForTask->removeItem($processKey);
                }else{
                    $cacheProcessData = $this->redisCacheForTask->getItem($processKey) ?? [];
                    
                    $cacheProcessData['task-worker'] = [
                        'status' => $task->workerState, 'time' => time()
                    ];
                    $this->redisCacheForTask->setItem($processKey, $cacheProcessData);
                    
                    unset($cacheProcessData);
                }
            } catch (\Throwable $e) {
                $className = $task::class;
                $date = gmdate('d/F/Y:H:i:s O', time());
                $msg = escapeshellarg("Destruct task ({$className}) got an error: {$e->getMessage()}, line: {$e->getLine()}, file: {$e->getFile()}");
                $this->logMsg("127.0.0.1 - - [{$date}] \"DEBUG {$msg}\" HTTP/1.1 500 - -");
            }
        }
    }
    
    /**
     * set current data to cache
     * @param array $data
     * @param string $key
     * @return bool
     */
    public function appendProcessDatatoCache($data, $key){
        return $this->redisCacheForTask->setItem($key, array_replace(
            $this->redisCacheForTask->getItem($key) ?? [],
            $data
        ));
    }
    /**
     * Get current data in cache
     * @return array
     */
    public function getProcessDataFromCache($key){
        if ( $key )
            return $this->redisCacheForTask->getItem($key) ?? [];
        return [];
    }
    /**
     * Get current data in cache
     * @return bool
     */
    public function delProcessDataFromCache($key){
        if ( $key )
            return $this->redisCacheForTask->removeItem($key) ?? [];
        return false;
    }
    /**
     * @param $contextName
     * @param $topicName
     * @param $action
     * @param array $formPost
     * @throws \Interop\Queue\Exception
     */
    public function sendKafkaMessage($contextName, $topicName, $action, $formPost = []) {
        return $this->getKafkaManager($contextName)->sendMessage($topicName, [
            'action'    => $action,
            'form_post' => $formPost
        ]);
    }

    protected $dtRedisCache = null;
    protected $lastTimeCalledDB = 0;
    /**
     * @param bool $getInstance
     *
     * @return \Doctrine\ORM\EntityManager|null
     * @throws \Doctrine\ORM\ORMException
     * @author Cuongnp - 3/19/2021
     */
    public function getEntityManager($getInstance = true) {
        if ( !empty($this->_entityMnCache ?? null) ){

            // -- Auto make reconnection
            if ( (time() - $this->lastTimeCalledDB) > 29 ) {
                try{
                    // Try ping to DB
                    $this->_entityMnCache->getConnection()->fetchAllAssociative('SELECT 1;');
                    
                    // $date = gmdate('d/M/Y:H:i:s O', time());
                    // fwrite(STDOUT, "127.0.0.1 - - [{$date}] \"POST /ping-doctrine-finish HTTP/1.1\" 200 {$respone}\n");
                }catch(\Throwable $e){
                    $date = gmdate('d/M/Y:H:i:s O', time());
                    $msg = urldecode( mb_substr($e->getMessage(), 0, 100) );
                    fwrite(STDOUT, "127.0.0.1 - - [{$date}] \"POST /doctrine-reconnecting?error={$msg} HTTP/1.1\" 500 1\n");
                }
				$this->lastTimeCalledDB = time();
            }
            
            return $this->_entityMnCache;
        }
        $this->lastTimeCalledDB = time();
        return $this->_entityMnCache = $this->createEntityManager($this->_dtFullConfigs);
    }

    /**
     * @param string $entityName
     * @param bool $getInstanceEntityMn
     *
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @author Cuongnp - 3/31/2021
     */
    public function getEntityRepo(string $entityName, $getInstanceEntityMn = true) {
        return $this->getEntityManager($getInstanceEntityMn)->getRepository($entityName);
    }

    /**
     * @param bool $getInstanceEntityMn
     *
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\ORM\ORMException
     * @author Cuongnp - 3/31/2021
     */
    public function getEntityConn($getInstanceEntityMn = true) {
        return $this->getEntityManager($getInstanceEntityMn)->getConnection();
    }
    /**
     * @param string $contextName
     * @return bool|KafkaManager
     * @throws \Exception
     */
    public function getKafkaManager(string $contextName) {
        $cacheKey = crc32(serialize($this->_kafkaConfig). "_{$contextName}");
        if (empty($this->_kafkaManager[$cacheKey])) {
            $this->_kafkaManager[$cacheKey] = (new KafkaManager($this->_kafkaConfig, $this->redisCacheForTask))(
                $contextName
            );
        }
        return $this->_kafkaManager[$cacheKey];
    }
    /**
     * @param string $indexName
     * @return bool|ESearchManager
     * @throws \Exception
     */
    public function getESearchManager(string $indexName) {
        return (new ESearchManager($this->_esearchConfig))(
            $indexName 
        );
    }
    /**
     * @param string $cacheKey
     * @param bool $ttl
     * @return \Laminas\Cache\Storage\StorageInterface
     */
    protected function getCache(string $cacheKey, $ttl = false) {
        return self::createRedisManager(array_merge([
                'namespace' => $cacheKey,
                'ttl'       => $ttl,
            ], $this->_cacheCfg)
        );
    }
    
    /**
     * @param \Throwable $error
     * @param array $params
     *
     * @return string
     * @author Cuongnp - 3/10/2021
     */
    public function saveParseError( \Throwable $error, array $params = [] ){
        $dbStorage = 'false';
        try {
            if (function_exists('\Sentry\captureException')) 
                \Sentry\captureException($error);
            $this->getEntityManager(false)->getConnection()->insert('tbl_error',  $data = [
                'error_uri'     => $url = ($params['uri'] ?? '/swoole/api/'),
                'error_params'  => json_encode($params['params'] ?? []),
                'error_method'  => $params['method'] ?? 'GET',
                'error_msg'     => $error->getMessage()
                    . ".\nOn line: ". $error->getLine()
                    . ".\nOf file: " . $error->getFile(),
                'error_trace'   => $error->getTraceAsString(),
                'error_code'    => $error->getCode(),
                'error_time'    => time()
            ]);
            $dbStorage = 'true';
        }catch (\ThrowAble $e){
            @file_put_contents(implode('/', [
                DATA_PATH, '..', 'logs', 'dt_error_' . crc32(microtime()) . '.log'
            ]), implode(PHP_EOL, $data));
        }
        
        $putCmd= realpath(DATA_PATH . '/shell_scripts/push_notify_error.sh');
        if ( !empty($putCmd) ){
            $now = date('Y/m/d H:i:s');
            $env = APP_ENV_VERSION;
            $domain = strtoupper(DOMAIN_NAME);
            $url = $url ?? '';
            $file = str_replace(realpath(APPLICATION_PATH . '/../../'), '', $error->getFile());
            @shell_exec(vsprintf('sh %s %s', [
                $putCmd,
                escapeshellarg(str_replace("'", '', implode(PHP_EOL, [
                    "[{$now} - {$env}] {$domain} Problem: {$url}",
                    $error->getMessage(),
                    "At line: {$error->getLine()} of file: {$file}",
                    "Db storage: {$dbStorage}"
                ])))
            ]));
        }
        
        if ( preg_match('/(Duplicate\sentry)/m', $error->getMessage()) >= 1 ){
            return 'DUPLICATE_DATA';
        }
        return '';
    }

    /**
     * clearCacheEntityManager
     * @deprecated
     */
    public function clearCacheEntityMn() {
        
        foreach (($this->_entityMnCache ?? []) as $idx => $entity){
            /**
             * Configuration 
             * @var \Doctrine\ORM\Configuration  $confs
             */
            /* $confs = $entity->getConfiguration();
            foreach ([
                'HydrationCacheImpl', 
                'MetadataCacheImpl', 
                'QueryCacheImpl', 
                'ResultCacheImpl',
                'HydrationCache',
                'MetadataCache',
                'QueryCache',
                'ResultCache'
                ] as $fnc
            ){
                $cache = $confs->{"get{$fnc}"}();
                if ($cache instanceof \Psr\Cache\CacheItemPoolInterface ){
                    $cache = $cache->getCache();
                }
                if( ($cache instanceof \Doctrine\Common\Cache\RedisCache ||
                    $cache instanceof \GrootSwoole\DoctrineRedisCache)
                    && ($redis = $cache->getRedis())->isConnected()
                ){
                    $redis->close();
                    $redis->__destruct();
                    break;
                }
            }*/
            
            $entity->getConnection()->close();
            $entity->close();

            $this->_entityMnCache[$idx] = null;
        }
        
        $this->_entityMnCache = [];
    }
    /**
     * Log message
     * @param string $msg
     * @param callable $writer
     * @param array $params
     */
    public function logMsg($msg, callable $writer = null, $params = [] ){
        if ( empty($writer) ){
            $date = gmdate('d/M/Y:H:i:s O', time());
            $msg  = escapeshellarg($msg);
            fwrite(STDOUT, "127.0.0.1 - - [{$date}] \"DEBUG {$msg}\" HTTP/1.1 200 - -" . PHP_EOL);
        }
        else call_user_func($writer, $msg, $params);
    }
    /**
     * Retry current task
     * @param \Throwable $e
     * @param string $action
     * @param array $params
     * @param integer $max
     */
    public function retryCurrentTask(\Throwable $e, string $action, array $params, $max = null, $topic = null){
        $max = $max ?? (
			defined('TOTAL_SWOOLE_TASK_WORKER') 
			? (TOTAL_SWOOLE_TASK_WORKER + 1)
			: 5
		);
		
        $topic = $topic ?? KAFKA_TOPIC;
        
        $msg = $e->getMessage();
        foreach(['try restarting transaction', 'server has gone away', 'Cannot execute queries'] as $pattern){
            if ( strpos($msg, $pattern) > 0 ){
                $params['retry'] = 1 + ($params['retry'] ?? 0);
                if ( $params['retry'] < $max ){
                    sleep(2);
                    return $this->getKafkaManager($topic)
                    ->sendMessage($topic, [
                        'action'   => $action,
                        'form_post'=> $params
                    ], $params['key'] ?? null);
                }
            }
        }
        
        return false;
    }
	/**
	* Flush database transaction
	*/
	protected function finishDbTrans(){
		try{
			$adapter = $this->getEntityManager()->getConnection();
			if ($adapter->isTransactionActive()){
				if( $adapter->isRollbackOnly() ) $adapter->rollback();
				else $adapter->commit();
			}
			return true;
		}catch (\Throwable $e){
			$this->saveParseError($e);
			return false;
		}
	}
	
    /**
     * @return mixed
     * @deprecated
     */
    protected function getWorkerId(){}
    /**
     * @return mixed
     * @deprecated
     */
    protected function getWorkerStatus($id = null ){}
    /**
     * Active worker
     * @deprecated
     */
    protected function workerStart(){}
    /**
     * Released worker
     * @deprecated
     */
    protected function workerFinish(){}
}
?>