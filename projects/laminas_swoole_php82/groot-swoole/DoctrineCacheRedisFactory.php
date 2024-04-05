<?php
namespace GrootSwoole;
use Interop\Container\ContainerInterface;
use Redis;

class DoctrineCacheRedisFactory {
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        return new DoctrineRedisCache();
    }
}
?>