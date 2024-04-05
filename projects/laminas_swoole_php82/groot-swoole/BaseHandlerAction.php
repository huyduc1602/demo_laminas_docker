<?php
namespace GrootSwoole;

use Doctrine\ORM\EntityManager;
use GrootSwoole\HandlerMiddleware\KafkaMiddleware;
use Laminas\Http\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use GrootSwoole\HandlerMiddleware\RedisMiddleware;

class BaseHandlerAction implements RequestHandlerInterface
{
    /**
     * Current request info
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $_request = null;

    /**
     * Redis cache adapter
     * @var null
     */

    protected static $_rCaches = null;

    /**
     * Get current request
     */
    public function getRequest(){
        return $this->_request;
    }
    
    /**
     * set current request
     */
    public function setRequest(ServerRequestInterface $request){
        return $this->_request = $request;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager($connName = 'orm_default') {
        $key = \GrootSwoole\HandlerMiddleware\DoctrineMiddleware::ENTITY_MANAGER_KEY;
        // -- Multi connection
        return $this->getRequest()->getAttribute($key)[$connName]
        
        // -- Only 1 connection
        ?? $this->getRequest()->getAttribute($key);
    }

    /**
     * @param $contextName
     * @param $topicName
     * @param $action
     * @param array $formPost
     * @throws \Interop\Queue\Exception
     */
    public function sendKafkaMessage($contextName, $topicName, $action, $formPost = []) {
        $this->getKafkaManager($contextName)->sendMessage($topicName, [
            'action'    => $action,
            'form_post' => $formPost
        ]);
    }

    /**
     * @param string $contextName
     * @return bool|KafkaManager
     * @throws \Exception
     */
    public function getKafkaManager(string $contextName) {
        return (new KafkaManager(
            $this->getRequest()->getAttribute(KafkaMiddleware::CONFIG_KEY)
        ))($contextName);
    }

    /**
     * @param string $cacheKey
     * @param int $lifetime | default 24h
     * @return mixed
     */
    public function getRedisManager($cacheKey = null, $lifetime = 86400)
    {
        if ( empty($cacheKey) ) return null;

        if ( null == self::$_rCaches || empty(self::$_rCaches[$cacheKey]) ) {
            if ( class_exists('Laminas\Cache\StorageFactory') ){
                self::$_rCaches[$cacheKey] = \Laminas\Cache\StorageFactory::factory([
                    'adapter' => [
                        'name' => BaseRedisAdapter::class,
                        'options' => array_merge([
                            'namespace' => DOMAIN_NAME,
                            'ttl'       => $lifetime,
                        ], $this->getRequest()->getAttribute(RedisMiddleware::REDIS_CONFIG_KEY))
                    ],
                    'plugins' => [
                        'exception_handler' => ['throw_exceptions' => true],
                        'Serializer'
                    ]
                ]);
            }else {
                unset($opts['lifetime']);

                if (isset(REDIS_CONFIG['seeds'])){
                    $configs = REDIS_CONFIG ?? [];

                    unset($configs['database'], $configs['read_timeout']);

                    if (isset($opts['ttl'])) $configs['ttl'] = $opts['ttl'];

                    $cache = new \Laminas\Cache\Storage\Adapter\RedisCluster();
                    $cache->setOptions($configs);
                }else{
                    $cache =new \Laminas\Cache\Storage\Adapter\Redis (
                        array_merge([
                            'namespace' => DOMAIN_NAME,
                            'ttl'       => $lifetime,
                        ], $opts, REDIS_CONFIG)
                    );
                }

                // Laminas cache Version 3
                return new \GrootSwoole\LaminasRedisAdapter (
                    $cache
                    
                    ->addPlugin(
                        (new \Laminas\Cache\Storage\Plugin\ExceptionHandler())
                        ->setOptions(new \Laminas\Cache\Storage\Plugin\PluginOptions([
                            'throw_exceptions' => true
                        ]))
                    )
                    ->addPlugin(
                        new \Laminas\Cache\Storage\Plugin\Serializer()
                    )
                );
            }
        }
        return self::$_rCaches[$cacheKey];
    }


    /**
     * Parse error and save to database
     * @param \Throwable $error
     * @return string || null
     */
    public function saveParseError( \ThrowAble $error ){
        try {
            $params = array_merge(
                (array)$this->getRequest()->getParsedBody(), 
                (array)$this->getRequest()->getQueryParams()
            );
           // $pathApp = realpath( APPLICATION_PATH . '/../../');
           // $pathLib = realpath( LIBRARY_PATH. '/../');
            
            $this->getEntityManager()
            ->getConnection()->insert('tbl_error',  [
                'error_user_id' => null,
                'error_uri'     => $url = $this->getRequest()->getUri()->getPath(),
                'error_params'  => json_encode($params),
                'error_method'  => $this->getRequest()->getMethod(),
                'error_msg'     => $error->getMessage()
                . ".\nOn line: ". $error->getLine()
                . ".\nOf file: " . $error->getFile(),
                'error_trace'   => $error->getTraceAsString(),
                'error_code'    => $error->getCode(),
                'error_time'    => time()
            ]);
        }catch (\ThrowAble $e){}
        
        $putCmd= realpath(DATA_PATH . '/shell_scripts/push_notify_error.sh');
        if ( !empty($putCmd) ){
            $now = date('Y/m/d H:i:s');
            $env = APP_ENV_VERSION;
            $domain = strtoupper(DOMAIN_NAME);
            $url = $url ?? ($_SERVER['REQUEST_URI'] ?? '');
            $file = str_replace(realpath(APPLICATION_PATH . '/../../'), '', $error->getFile());
            @shell_exec(vsprintf('sh %s %s', [
                $putCmd,
                escapeshellarg(str_replace("'", '', "[{$now} - {$env}] {$domain} Problem: {$url}\n{$error->getMessage()}\nAt line: {$error->getLine()} of file: {$file}"))
            ]));
        }
        
        if ( preg_match('/(Duplicate\sentry)/m', $error->getMessage()) >= 1 ){
            return 'DUPLICATE_DATA';
        }
        return '';
    }
    /**
     * destroy db connection
     */
    public function destroyEntityManager(){
        $manager = $this->getEntityManager(null, false);
        if ( $manager && $manager->isOpen() ){
            $confs = $manager->getConfiguration();
            foreach ([
                'HydrationCacheImpl',
                'MetadataCacheImpl',
                'QueryCacheImpl',
                'ResultCacheImpl'
            ] as $fnc
            ){
                $cache = $confs->{"get{$fnc}"}();
                if( (
                        $cache instanceof \Doctrine\Common\Cache\RedisCache ||
                        $cache instanceof \GrootSwoole\DoctrineRedisCache 
                    )
                    && $cache->getRedis()->isConnected()
                ){
                    $cache->getRedis()->close();
                    $cache->getRedis()->__destruct();
                }
            }
            $manager->getConnection()->close();
            $manager->close();
        }
        unset($manager);
    }
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $this->setRequest($request);
        $respone = $this->process();
        // $this->destroyEntityManager();
        
        return $respone;
    }

    /**
     * This property must be overriding by child class
     * Only accept key in this array
     * @var array
     */
    protected $_paramsKey = [];
    protected $_systemParamsKey=[
        'process_key' => '',
        'is_ping'     => '',
		'retry'		  => 0
    ];
    /**
     * Get request  params base on Method
     * @return array
     */
    protected function getParams(){
        $method = strtolower($this->getRequest()->getMethod() ?? 'get');
        $params = [];
        switch ($method){
            case 'post':
            case 'put':
            case 'delete':
                $params = $this->getRequest()->getParsedBody();
                break;
            default:
                $params = $this->getRequest()->getQueryParams();
                break;
        }
        
        $this->_paramsKey = array_merge(
            $this->_paramsKey, $this->_systemParamsKey
        );
        
        return array_intersect_key(
            // Request params
            $params, 
            
            // Action params
            $this->_paramsKey
        );
    }
    /**
     * Get request informations
     * @return array
     */
    protected function getRequestInfo(){
        return [
            'uri'   => $this->getRequest()->getUri()->getPath(),
            'method'=> $this->getRequest()->getMethod()
        ];
    }
    
    /**
     * This function musk be overriding by child class
     * @return string
     */
    protected function getRequestTaskClass(){
        return str_replace(
            ['\\Handler\\', 'Handler'], 
            ['\\Task\\', 'Task'], 
            get_called_class()
        );
    }
    public function process()
    {
        $msg = 'Cannot assign new task. Please check it';
        try {
            if (!empty($taskClass = $this->getRequestTaskClass())) {
                if (!empty($paramKeys = $taskClass::_getParamkeys())) {
                    $this->_paramsKey = $paramKeys;
                }
                if (empty($this->_paramsKey ?? []) ||
                    !empty($params = $this->getParams())
                ) {                    
                    $this->getTaskDispatcher()->dispatch(
                        new $taskClass($params ?? [], $this->getRequestInfo())
                    );
                }
                return $this->responseSuccess([
                    'message' => 'Assign new task completed.',
                    'params' => array_keys($params ?? []),
                    'task_class' => $taskClass ?? ''
                ]);
            }
        } catch (\Throwable $e) {
            $this->saveParseError($e);
            $msg = $e->getMessage();
        }

        return $this->responseError([
            'message' => $msg,
        ], Response::STATUS_CODE_503);
    }

    /**
     * @param $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    protected function response( $data, int $status = Response::STATUS_CODE_200, array $headers = []){
        return new JsonResponse($data, $status, $headers);
    }
}
