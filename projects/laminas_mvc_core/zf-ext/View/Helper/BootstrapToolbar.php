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
 * Generates a "button" element into the toolbar
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapToolbar
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class BootstrapToolbar extends AbstractHelper {
	// Mang chua noi dung XHTML.
	private $_toolbarIcons = array ();
	
	/**
	 * Tao thanh toolbar.
	 * 
	 * @param array $toolbarIcons
	 *        	Button cua thanh toolbar
	 * @return this
	 */
	public function __invoke($toolbarIcons = array()) {
		if (! is_array ( $toolbarIcons )) {
			$toolbarIcons = @( array ) $toolbarIcons;
		}
		// Gan gia tri.
		foreach ( $toolbarIcons as $toolbarIcon ) {
			$this->_toolbarIcons [] = $toolbarIcon;
			//echo (string)$toolbarIcon;
		}
		// Return.
		return $this;
	}
	
	public function __toString() {
		// Tao thanh toolbar
		$strContainerXHTML = '<div class="toolbar-navigation collapse navbar-collapse navbar-right">
			<ul class="nav navbar-nav">';
		foreach ( $this->_toolbarIcons as $toolbarIcon ) {
			$strContainerXHTML .= ( string ) $toolbarIcon;
		}
		
		$strContainerXHTML .= '</ul></div>';
		
		// Return
		return $strContainerXHTML;
	}
	public function toString() {
		return $this->__toString ();
	}
}

?>