<?php
/**
* @category   ZF
* @package    ZF_View_Helper
* @subpackage BootstrapToolbar
* @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
* @version    $Id: BootstrapToolbar.php 2014-20-01
* @license    http://framework.zend.com/license/new-bsd     New BSD License
*/

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper;
use Laminas\View\Helper\AbstractHelper;

/**
 * Get current route
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapToolbar
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class GetRouteMatch extends AbstractHelper {
	// Match route
	protected $_route = null;
	public function __construct( $container ){
	   $route = $container->get('router');
	   $this->_route = $route->match($container->get('request'));
	}
	
	/**
	 * @return this
	 */
	public function __invoke() {
		// Return.
		return $this->_route;
	}
}

?>