<?php 
namespace GrootSwoole\MiddlewareFactory;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface as EventTriggerHandler;
use Swoole\Http\Server as HttpServer;

class EventTriggerHandlerFactory
{
    public function __invoke(ContainerInterface $container, $className): EventTriggerHandler
    {
        $entitymanager = null;
        if ($container->has($key = 'doctrine.entitymanager.orm_default')){
            $entitymanager = $container->get($key);
        }
        
        return new $className(
            $container->get(HttpServer::class),
            $container->get(ResponseFactoryInterface::class),
            $entitymanager
        );
    }
}
?>