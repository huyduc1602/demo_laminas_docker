<?php
/**
* @category   ZF
* @package    ZF_View
* @subpackage Helper
* @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
* @version    $Id: Url.php 23775 2011-03-01 17:25:24Z ralph $
* @license    http://framework.zend.com/license/new-bsd     New BSD License
*/

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\Session\SessionManager;
/**
 * Helper for making easy links and getting urls that depend on the routes and router
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View
 * @subpackage Helper
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class Url extends AbstractHelper {
    const QUERY_STR_NAMESPACE = 'queryStringMn';
    const QUERY_STR_PREFIX = 'queryString_';
    /**
     * Check permission plugin
     */
    protected $_checkPerAcl = null;
    protected $_ssManager = null;
    public function __construct( $container ){
        
        $plugins = $container->get('ControllerPluginManager');
        // Neu co cau hinh phan quyen
        if ( $plugins->has('getPermission') ){
            $this->_checkPerAcl = $plugins->get('getPermission');
            if ( $this->_checkPerAcl->isUnRegisted() ){
                $this->_checkPerAcl = null;
            }
        }
        if ( $container->has(SessionManager::class) ){
            $this->_ssManager = $container->get(SessionManager::class);
        }
        unset($plugins);
    }
    /**
     * Render url
     * @param string $name
     * @param array $params
     * @param array $options
     * @param string $reuseMatchedParams
     * @return string
     */    
	public function __invoke($name = null, $params = [], $options = [], $reuseMatchedParams = false) {
	    
	    // Need check permission of url
	    if ( $this->_checkPerAcl
	        && false == $this->_checkPerAcl->checkPermission($name, $params) ){
            return '#';
	    }
	    
	    if ( !empty($options['useOldQuery']) ){
	        // -- get query string from session
	        $oldQuery = $this->getOldQuery([
	            'router' => $name, 'action' => $params['action'] ?? 'index'
	        ]); unset($options['useOldQuery']);
	        
	        if ( $oldQuery ){
	            $options['query'] = array_replace(
	                $oldQuery, (array)($options['query'] ?? [])
                );
	        }
	    }
	    
	    $base = rtrim(BASE_URL, '/');
	    $baseNew = ltrim($this->view->url(
	        $name, $params, $options, $reuseMatchedParams
        ), '/');
		
	    if ( !empty($options['force_canonical']) ){
	        $url1 = $this->view->url($name, $params);
			
	        if ( $url1 != '/' ){
	            $url = str_replace($url1, $base . $url1, $baseNew);
	        }else{ 
				
				$url = implode('/', [
					rtrim($baseNew, '/'), ltrim($base, '/')
				]);
			}
	    }else
	        $url = implode('/', [ $base, $baseNew ]);
	    
	    return $url;
	}
	
	/**
	 * Get old query param of search form
	 * @param array $params
	 * @return array
	 */
	protected function getOldQuery( array $params = [] ){
	    
	    $action = empty($params['action']) ? 'index' : $params['action'];
	    
	    // -- get query string from session
	    $key = self::QUERY_STR_PREFIX . crc32(json_encode([$params['router'], $action]));
	    
	    $container = new \Laminas\Session\Container(self::QUERY_STR_NAMESPACE);
	    $container::setDefaultManager($this->_ssManager);
	    
	    if ( $container->offsetExists($key) ){
	        $oldQuery = $container->offsetGet($key);
	       //$paramsSession->offsetUnset('queryString');
	    }else $oldQuery = [];
	    
	    unset($container);
	    return $oldQuery;
	}
}
?>