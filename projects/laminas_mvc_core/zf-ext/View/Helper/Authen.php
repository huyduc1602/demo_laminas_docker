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
use Laminas\Authentication\AuthenticationService;

/**
 * Generates a "button" element into the toolbar
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapToolbar
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class Authen extends AbstractHelper {
	// Mang chua noi dung XHTML.
	protected $_authen = null;
	public function __construct( $container ){
	    if ( $container->has(AuthenticationService::class) )
	       $this->_authen = $container->get(AuthenticationService::class)
	       ->getIdentity();
	}
	
	/**
	 * @return this
	 */
	public function __invoke() {
		// Return.
		return $this->_authen;
	}
}

?>