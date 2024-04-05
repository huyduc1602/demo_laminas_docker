<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Controller;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Interop\Container\ContainerInterface;
//use Laminas\Permissions\Acl\Acl;
//use Laminas\Permissions\Acl\Role\GenericRole as Role;
//use Laminas\Permissions\Acl\Resource\GenericResource as Resource;

/**
 * @todo       allow specifying status code as a default, or as an option to methods
 */
class ZfPermission extends AbstractPlugin
{
    const SERVICE_ALIAS = 'getPermission';
    const ZF_DEFAULT_ROLE = 'all';
    const ZF_DEFAULT_CODE = 'STAFF';
    const ZF_ACL_FOLDER = 'zf_acl';
    /**
     * Zend authentication
     * @var Acl
     */
    protected $_pACL = null;
    /**
     * 
     * @var array
     */
    protected $_pConfigs = [];
    /**
     * customer folder
     * @var string
     */
    protected $_subFolder = '';
    /**
     * 
     * @var string
     */
    protected $_userKey = 'admin_groupcode';
    
    public function __construct(ContainerInterface $container){
        $configs = $container->get('config');
        $pConfigs = $configs['zf_permission'][APPLICATION_SITE] ?? [];
        
        // Neu co cau hinh phan quyen
        if ( false == empty($pConfigs) 
            && false == empty($configs['zf_permission'][APPLICATION_SITE]) ){
            $this->_pACL = true;
            
            $this->_userKey = $configs['zf_permission'][APPLICATION_SITE]['user_key'] 
            ?? $this->_userKey;
            
            if ( ($configs['zf_permission'][APPLICATION_SITE]['use_subfolder'] ?? false) ){
                $this->_subFolder = DIRECTORY_SEPARATOR . APPLICATION_SITE;
            }
            $routes = $configs['router'] ?? [];
            $prevents = $configs['zf_permission'][APPLICATION_SITE]['prevent_routes'] ?? [];
            $this->_pConfigs = [
                'routes'    => $routes['routes'] ?? [],
                'prevents'  => $prevents
            ];
            
            unset($prevents, $routes);
        }
    }
    
    /**
     * Co dang ky service hay khong
     * @return bool
     */
    public function isUnRegisted(){
        return empty($this->_pACL);
    }
    /**
     * Get route configs
     * @return array
     */
    public function getConfigs(){
        return $this->_pConfigs;
    }
    /**
     * Transform an "action" token into a method name
     *
     * @param  string $action
     * @return string
     */
    protected static function getMethodFromAction($action)
    {
        $method  = str_replace(['.', '-', '_'], ' ', $action);
        $method  = ucwords($method);
        $method  = str_replace(' ', '', $method);
        $method  = lcfirst($method);
        $method .= 'Action';
        
        return $method;
    }
    /**
     * 
     * @param string $str
     * @return string
     */
    protected function makeActName( $str = '' ){
        $names = explode('-', $str);
        
        return array_shift($names) . implode('', array_map(function($item){
            return ucfirst($item);
        }, $names)) . 'Action';
    }
    /**
     * Get url by route
     * @param string $name
     * @param array $params
     * @return string
     */
    protected function getZfUrlByRoute( $name = null, $params = [], $debug = false ){
        if ( null !== $name && false == empty($this->_pConfigs['routes'][$name]) ){
            $route = $this->_pConfigs['routes'][$name];
            if ( \Zf\Ext\Router\RouterLiteral::class == $route['type'] ){
                $controller = $route['options']['defaults']['controller'] ?? '';
                $actName = $this->getMethodFromAction($route['options']['defaults']['action'] ?? 'index');
            }else{
                $controller = $route['options']['defaults']['controller']?? '';
                $actName = $this->getMethodFromAction(
					$params['action'] ?? (
						$route['options']['defaults']['action']??'index'
					)
				);
            }
            return implode('\\', [$controller, $actName]);
        }
        return '';
    }
    
    /**
     * Check permission
     * @param string $routeName
     * @param array $params
     * @return boolean
     */
    public function checkPermission( $routeName = '', $params = [], $debug = false ){
        //return true;
        if ( $this->isUnRegisted() ){
            return true;
        }
        $controller = $this->getController();
        
        if (empty($controller)){
            $authen = new \stdClass();
        }else{
            $authen = $controller->getAuthen();
            if( $routeName === null ){
                $routeName = $controller->getEvent()->getRouteMatch()->getMatchedRouteName();
            }
        }
        
        // -- is prevent routes
        if ( !empty($this->_pConfigs['prevents'][$routeName]) ) {
            $rolePrevent = $this->_pConfigs['prevents'][$routeName];
            if (is_bool($rolePrevent) ) return $rolePrevent;
            if (is_callable($rolePrevent)) return call_user_func(
                $rolePrevent, $authen
            );
            return boolval($rolePrevent);
        }
        
        $role = ($authen ? ($authen->{$this->_userKey} ?? self::ZF_DEFAULT_CODE)
        : self::ZF_DEFAULT_CODE);
        
        if ( $role == 'SUPPORT' ){ return true;}
        $uri = $this->getZfUrlByRoute($routeName, $params, $debug);
        
        // Check permission
        $role = crc32($role);
        $opts = explode('\\', $uri);
        $filePath = implode(DIRECTORY_SEPARATOR, [
            DATA_PATH, self::ZF_ACL_FOLDER . $this->_subFolder, crc32(array_shift($opts)),
            crc32($routeName . '\\' . end($opts)) . '.php'
        ]);
        
        // -- File not exists, file empty or isset => Granted
        if ( realpath($filePath) 
            && false == empty($perms = @include $filePath)
            && isset($perms[$role]) ) return true;
        
        return false;
    }
    
    public function __invoke( ) {
        return $this;
    }
}
