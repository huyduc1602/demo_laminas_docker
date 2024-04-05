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
 * Create a "Checkbox" on the gridview, use check all checkbox has attribute name="id[]"
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapManage
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class ManageCheckboxAll extends AbstractHelper {
	// String html for render checkbox
	private $_formatIcon = '<div class="checkbox"><label><input type="checkbox" id="checkall" __attributes__ /><i class="input-helper"></i></label></div>';
	
	/**
	 * Tạo nút checkbox, dùng để check all toàn bộ checkbox có thuộc tính name="id[]".
	 * 
	 * @param array $attribs
	 *        	Thuộc tính của checkbox
	 *        	
	 * @return string The element XHTML
	 */
	public function __invoke($attribs = []) {
		// Tạo thuộc tính của checkbox
		$attributes = "";
		// Duyệt mảng attribs
		foreach ( $attribs as $key => $val ) {
			$attributes .= htmlspecialchars ( $key ) . "=\"" . htmlspecialchars ( $val ) . "\"";
		}
		
		// Render checkbox
		return str_replace ( "__attributes__", $attributes, $this->_formatIcon );
	}
}
?>