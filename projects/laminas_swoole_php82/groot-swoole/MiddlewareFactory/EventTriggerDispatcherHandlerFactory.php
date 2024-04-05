<?php 
namespace GrootSwoole\MiddlewareFactory;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface as EventTriggerDispatcherHandler;
use Mezzio\Swoole\Event\EventDispatcherInterface;

class EventTriggerDispatcherHandlerFactory
{
    public function __invoke(ContainerInterface $container, $className): EventTriggerDispatcherHandler
    {
        $entitymanager = null;
        if ($container->has($key = 'doctrine.entitymanager.orm_default')){
            $entitymanager = $container->get($key);
        }
        return new $className(
            $container->get(EventDispatcherInterface::class),
            $container->get(ResponseFactoryInterface::class),
            $container->get('config'),
            $entitymanager
        );
    }
}
?>