<?php
/**
 * @see       http://github.com/mezzio/mezzio for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/mezzio/mezzio/blob/master/LICENSE.md New BSD License
 */

namespace GrootSwoole\HandlerMiddleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

class RedisMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : MiddlewareInterface
    {
        return new RedisMiddleware($container->get('config')['redis-caching'] ?? []);
    }
}
