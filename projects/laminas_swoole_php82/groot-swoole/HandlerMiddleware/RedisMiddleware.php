<?php
namespace GrootSwoole\HandlerMiddleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RedisMiddleware implements MiddlewareInterface
{

    const REDIS_CONFIG_KEY = 'ZF_RedisConfig';

    protected static  $_config = [];

    public function __construct($conf = null){
       static::$_config = $conf;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return $handler->handle(
            $request->withAttribute(self::REDIS_CONFIG_KEY, self::$_config)
        );
    }
}