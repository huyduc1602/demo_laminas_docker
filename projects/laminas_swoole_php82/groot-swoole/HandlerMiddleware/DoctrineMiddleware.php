<?php
namespace GrootSwoole\HandlerMiddleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use GrootSwoole\SqlLogger;

class DoctrineMiddleware implements MiddlewareInterface
{
    /**
     * Key to get EntityManager
     * @var string
     */
    const ENTITY_MANAGER_KEY = 'ZF_EntityManager';
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    public static $_entityManager = null;

    /**
     * @var array
     */
    public $_config = [];
    
    public function __construct(\Interop\Container\ContainerInterface $container){
        if (
            //$container->has('doctrine.entitymanager.orm_default') &&
            empty(self::$_entityManager)
        ){
            $this->_config = $container->get('config')['doctrine'] ?? [];
            $managers = $this->_config['entitymanager'] ?? [];
            foreach ($managers as $key => $_){
                self::$_entityManager[$key] = $container->get("doctrine.entitymanager.{$key}");
                $this->initDT(self::$_entityManager[$key]);
            }
            
            unset($managers);
        }
    }
    
    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    protected function initDT($entityManager)
    {
        $this->customRedisCacheNameSpace(
            $entityManager->getConfiguration()
        );
        
        if ( !empty($this->_config['driver']['logs']) ){
            $entityManager->getConfiguration()
            ->setSQLLogger(
                new SqlLogger($this->_config['driver']['logs'])
            );
        }
    }
    
    /**
     * Set redis cache for entityManager
     * @param array $configs
     * @param \Doctrine\ORM\Configuration $confObj
     */
    protected function customRedisCacheNameSpace(\Doctrine\ORM\Configuration $confObj ){
        foreach (['HydrationCacheImpl', 'MetadataCacheImpl', 'QueryCacheImpl','ResultCacheImpl'] as $fnc){
            $driver = $confObj->{"get{$fnc}"}();
            if ($driver instanceof \Doctrine\Common\Cache\RedisCache){
                $driver->setNamespace($fnc);
            }
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return $handler->handle(
            $request->withAttribute(self::ENTITY_MANAGER_KEY, self::$_entityManager)
        );
    }
}