<?php
namespace GrootSwoole\HandlerMiddleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class KafkaMiddleware implements MiddlewareInterface
{

    const CONFIG_KEY = 'ZF_KAFKA_CONFIG';

    protected static  $_config = [];

    public function __construct($conf = null){
       if ( empty(static::$_config) && !empty($conf))
           static::$_config = $conf;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return $handler->handle(
            $request->withAttribute(self::CONFIG_KEY, self::$_config)
        );
    }
}