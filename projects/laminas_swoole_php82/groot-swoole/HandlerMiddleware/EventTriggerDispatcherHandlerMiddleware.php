<?php

namespace GrootSwoole\HandlerMiddleware;

use Doctrine\ORM\EntityManager;
use Laminas\Http\Response;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use GrootSwoole\BaseHandlerAction;
use Psr\Http\Message\ServerRequestInterface;

class EventTriggerDispatcherHandlerMiddleware extends BaseHandlerAction
{
    use \GrootSwoole\BaseUtil;
    
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var ResponseFactoryInterface */
    private $responseFactory;

    private $globalConfig;

    private $taskClass = null;
    
    protected $_entityManager = null;
    
    public function setTaskEventClass(string $task) {
        $this->taskClass = $task;
    }
    
    public function getTaskEventClass() {
        return $this->taskClass;
    }
    
    protected function getRequestTaskClass() {
        if ($this->taskClass && class_exists($this->taskClass))
            return $this->taskClass;
        return parent::getRequestTaskClass();
    }
    
    public function __construct(
        EventDispatcherInterface $dispatcher,
        ResponseFactoryInterface $responseFactory,
        array $globalConfig,
        $entitymanager
    )
    {
        $this->dispatcher = $dispatcher;
        $this->responseFactory = $responseFactory;
        $this->globalConfig = $globalConfig;
        $this->_entityManager = $entitymanager;
    }
	protected $_dbConnAt = 0;
    /**
     * @return EntityManager
     */
    public function getEntityManager($connName = 'orm_default', $autoInit = true ) {
        $connName = $connName ?? 'orm_default';
        if ( empty($this->_entityManager) && $autoInit ){
			$this->_dbConnAt = time();
            return $this->_entityManager = $this->createEntityManager(
                ($this->getConfiguration())['doctrine']
            );
        }elseif( $this->_entityManager &&
			(time() - $this->_dbConnAt) > 29 
		){
			try{
				// Try ping to DB
				$this->_entityManager->getConnection()->fetchAllAssociative('SELECT 1;');
			}catch(\Throwable $e){
				$date = gmdate('d/M/Y:H:i:s O', time());
				$msg = urldecode( mb_substr($e->getMessage(), 0, 100) );
				fwrite(STDOUT, "127.0.0.1 - - [{$date}] \"POST /doctrine-reconnecting?error={$msg} HTTP/1.1\" 500 1\n");
			}
		}
		
        $this->_dbConnAt = time();
        return $this->_entityManager ?? null;
    }
    
    /**
     * Get Task dispatcher
     * @return \Psr\EventDispatcher\EventDispatcherInterface
     */
    public function getTaskDispatcher()
    {
        return $this->dispatcher;
    }

    public function getConfiguration()
    {
        return $this->globalConfig;
    }
    
    public function setRequest(ServerRequestInterface $request){
        parent::setRequest($request);
        
        $path = str_replace('/', '.', ltrim($request->getUri()->getPath() ?? '', '/'));
        if (!isset($this->globalConfig['routes'][$path])) return;
        
        // Override Task class name
        if ( !empty($taskClass = $this->globalConfig['routes'][$path]['task'] ?? false) ) {
            $this->setTaskEventClass($taskClass);
        }
        
        // Override parameter
        if ( !empty($this->globalConfig['routes'][$path]['params']) ){
            $this->_paramsKey = $this->globalConfig['routes'][$path]['params'];
        }
    }
    /**
     * @return array|mixed
     */
    public function getDatabaseConfig()
    {
        return ($this->getConfiguration())['doctrine']
        ['connection']['orm_default']['params'] ?? [];
    }

    /**
     * @param $data
     * @param int $status
     * @return \Laminas\Diactoros\Response\JsonResponse
     */
    public function responseSuccess($data, $status = Response::STATUS_CODE_200)
    {
        return $this->response($data, $status);
    }

    /**
     * @param $data
     * @param int $status 400: bad request
     * @return \Laminas\Diactoros\Response\JsonResponse
     */
    public function responseError($data, $status = Response::STATUS_CODE_400, $level = 0)
    {
        return $this->response($data, $status);
        //send mail error belong to level
    }
}

?>