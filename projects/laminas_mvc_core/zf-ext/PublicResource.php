<?php
namespace Zf\Ext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PublicResource
{
    /**
     * @var null|mixed
     */
    public $_rsConfigs = null;
    
    public function __construct(array $configs ){
        \Zf\Ext\Utilities\Resource::getInstance($configs);
    }
    
    /**
     * Inject the UrlHelper instance with a RouteResult, if present as a request attribute.
     *
     * Injects the helper, and then dispatches the next middleware.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if ($next) return $next($request, $response);
        return $response;
    }
}
?>