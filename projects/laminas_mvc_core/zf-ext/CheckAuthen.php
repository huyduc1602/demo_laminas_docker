<?php
namespace Zf\Ext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\JsonModel;
class CheckAuthen
{
    /**
     * Key to get authentication
     * @var string
     */
    const AUTHEN_KEY = 'ZF_Authen';
    
    /**
     * Key to get authentication
     * @var string
     */
    const AUTHEN_SERVICE = 'ZF_Authen_Service';
    
    /**
     * Key to get current key
     * @var string
     */
    const SKIP_CHECK_KEY = 'ZF_AuthenSkipCheck';
    
    /**
     * @var null|mixed
     */
    public $_authen = null;
    /**
     * @var null|mixed
     */
    public $_auConfigs = null;
    protected $_store = null;
    
    public function __construct(array $configs, \Laminas\Session\SessionManager $ssManager ){
        $this->_auConfigs = $configs;
        $this->_store = new \Laminas\Authentication\Storage\Session(null, null, $ssManager);
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
        // instantiate the authentication service
        $auth = new AuthenticationService($this->_store); $this->_store = null;
        $this->_authen = $auth->getIdentity();
        
        $uri = $request->getUri(); $routeParams = [];
        $routeResult = $request->getAttribute('Mezzio\Router\RouteResult');
        if ($routeResult){
            $routeParams = $routeResult->getMatchedParams();
            unset($routeParams['middleware']);
        }
        
        // -- Path: uri replace all route param. Example: /abc/xy[/:param1[/:param2[/...]]]
        $paths = explode('/', ltrim($uri->getPath(), '/'));
        $path = '/' . implode('/', array_splice(
            $paths, 0, max([1, count($paths) - count($routeParams) ])
        ));
        
        $isSkipCheck = in_array($path, $this->_auConfigs['skipChecks']);
        
        // Not skip check
        if( false === $isSkipCheck ){
            $hasAut = false;
            // - Has Identity
            if ( $auth->hasIdentity() ){
                
                // Custom evt
                if ( isset($this->_auConfigs['middleware'])
                    && is_callable($this->_auConfigs['middleware']) )
                    $hasAut = call_user_func($this->_auConfigs['middleware'], $this->_authen);
                
            }
            
            // -- Not authentication
            if ( false === $hasAut ){
                unset($auth);
                return new JsonModel(['code' => '401', 'msg' => 'Unauthorized!']);
            }
        }
        
        if ($next) {
            return $next(
                $request
                ->withAttribute(self::AUTHEN_SERVICE, $auth)
                ->withAttribute(self::AUTHEN_KEY, $this->_authen)
                ->withAttribute(self::SKIP_CHECK_KEY, $isSkipCheck), 
                $response
            );
        }
        
        return $response;
    }
}
?>