<?php
namespace GrootSwoole\MiddlewareFactory;

use Psr\Container\ContainerInterface;

class TaskEventListenerFactory
{
    public function __invoke(ContainerInterface $container, $className)
    {
        $config = $container->get('config');
        
        $entitymanager = null;
        if ($container->has($key = 'doctrine.entitymanager.orm_default')){
            $entitymanager = $container->get($key);
        }
        
        return new $className(
            $config['doctrine'] ?? [],
            $config['redis-caching'] ?? [],
            $config['kafka_config'] ?? [],
            $entitymanager
        );
    }
}
?>