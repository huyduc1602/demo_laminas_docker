<?php
namespace Zf\Ext;

use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
class ZFTemplate
{
    /**
     * Key to get Template
     * @var string
     */
    const TEMPLATE_KEY = 'ZF_Template';
    
    /**
     * Key to get Template
     * @var string
     */
    const ROUTER_KEY = 'ZF_Router';
    
    /**
     * @var Mezzio\Template\TemplateRendererInterface
     */
    public static $_zfTemplate = null;
    
    /**
     * @var Mezzio\Router\RouterInterface
     */
    public static $_zfRouter = null;
    /**
     * @var Laminas\Router\RouteStackInterface
     */
    public static $_httpRouter = null;
    
    public function __construct(RouterInterface $router, TemplateRendererInterface $template){
        self::$_zfTemplate = $template;
        self::$_zfRouter = $router;
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
        if ($next) {
            return $next(
                $request
                    ->withAttribute(self::TEMPLATE_KEY, self::$_zfTemplate)
                    ->withAttribute(self::ROUTER_KEY, self::$_zfRouter),
                $response
            );
        }
        return $response;
    }
}
?>