<?php
namespace Zf\Ext;
use Laminas\View\Model\JsonModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Role\GenericRole as Role;
use Laminas\Permissions\Acl\Resource\GenericResource as Resource;

class CheckPermission
{
    const ACL_PER_KEY = 'ZF_ACL_PER';
    /**
     * Permissions config
     * @var null|mixed
     */
    public $_pConfigs = array();
    
    /**
     * Laminas\Permissions
     * @var Laminas\Permissions\Acl\Acl
     */
    public static $_pACL = null;
    
    public function __construct(array $configs){
        $this->_pConfigs = $configs;
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
        $authen = $request->getAttribute(\Zf\Ext\CheckAuthen::AUTHEN_KEY);
        
        if( is_array($this->_pConfigs) && isset($authen->group_code)
            && false === $request->getAttribute(\Zf\Ext\CheckAuthen::SKIP_CHECK_KEY, true) ){
            
            // If ACL not created
            if ( !self::$_pACL ){
                self::$_pACL = new Acl();
                
                // Add role
                foreach ($this->_pConfigs['group'] as $group){
                    self::$_pACL->addRole($group);
                }
                
                // Create permission
                foreach ($this->_pConfigs['routes'] as $route){
                    self::$_pACL
                        ->addResource(new Resource($route['path']))
                        ->allow($route['roles'], $route['path']);
                }
            }
            
            // Check permission
            $role = $authen->group_code; $uri = $request->getUri();
            if ( !self::$_pACL->hasRole($role) 
                || !self::$_pACL->isAllowed($role, $uri->getPath()) ){
                return new JsonModel(['code' => '403', 'msg' => 'Access denied!']);
            }
        }
        
        // Next middleware
        if ($next){
            return $next(
                $request->withAttribute(self::ACL_PER_KEY, self::$_pACL), 
                $response
            );
        }
        
        // Final middleware
        return $response;
    }
}
?>