<?php
/**
 * @category   ZF
 * @package    ZF_View_Helper
 * @subpackage BootstrapManage
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: ManageIcon.php 2014-16-04
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper\BootstrapManage;
use Laminas\View\Helper\AbstractHelper;

/**
 * Create a "Checkbox" on the gridview
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapManage
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ManageCheckbox extends AbstractHelper {
	// String html for render checkbox
	private $_formatIcon = '<div class="checkbox"><label><input type="checkbox" name="id[]" value="__value__" __attributes__ /><i class="input-helper"></i></label></div>';
	
	/**
	 * Tạo nút checkbox.
	 * 
	 * @param int $value
	 *        	Giá trị của checkbox
	 * @param array $attribs
	 *        	Thuộc tính của checkbox
	 *        	
	 * @return string The element XHTML
	 */
	public function __invoke($value, $attribs = []) {
		// Tạo thuộc tính của checkbox
		$attributes = "";
		// Duyệt mảng attribs
		foreach ( $attribs as $key => $val ) {
			$attributes .= htmlspecialchars ( $key ) . "=\"" . htmlspecialchars ( $val ) . "\"";
		}
		
		// Render checkbox
		return str_replace ( array (
				"__value__",
				"__attributes__" 
		), array (
				$value,
				$attributes 
		), $this->_formatIcon );
	}
}
?>