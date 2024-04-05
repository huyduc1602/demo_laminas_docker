<?php 
namespace GrootSwoole\HandlerMiddleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Swoole\Http\Server as HttpServer;

class EventTriggerHandler extends \GrootSwoole\BaseHandlerAction
{
    /** @var ResponseFactoryInterface */
    private $responseFactory;
    
    /** @var HttpServer */
    private $server;
    
    public function __construct(
        HttpServer $server,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->server          = $server;
        $this->responseFactory = $responseFactory;
    }
}
?>