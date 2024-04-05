<?php
namespace Zf\Ext;
use Laminas\Db\Adapter\Adapter as DbAdapter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
class ZFAdapter
{
    /**
     * Key to get Db Adapter
     * @var string
     */
    const DB_ADAPTER_KEY = 'ZF_DbAdapter';
    
    /**
     * @var Laminas\Db\Adapter\Adapter
     */
    public static $_zfAdapter = null;
    
    /**
     * @var null|mixed
     */
    public $_dbConfig = array();
    
    public function __construct(array $configs){
        $this->_dbConfig = $configs;
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
            if ( !self::$_zfAdapter ){
                // Set up zend db adapter
                self::$_zfAdapter = new DbAdapter($this->_dbConfig);
            }
            return $next(
                $request->withAttribute(self::DB_ADAPTER_KEY, self::$_zfAdapter), 
                $response
            );
        }
        return $response;
    }
}
?>